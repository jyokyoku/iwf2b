<?php

namespace Iwf2b\Model;

use Iwf2b\AbstractSingleton;

/**
 * Class AbstractModel
 *
 * @package Iwf2b\Model
 */
abstract class AbstractModel extends AbstractSingleton {
	/**
	 * Table name
	 *
	 * @var string
	 */
	protected static $table_name = '';

	/**
	 * Primary key
	 *
	 * @var string
	 */
	protected static $primary_key = '';

	/**
	 * SQL
	 *
	 * @var array|string
	 */
	protected static $sql;

	/**
	 * wpdb object
	 *
	 * @var \wpdb
	 */
	public static $db;

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		global $wpdb;

		static::$db = $wpdb;

		$this->migrate_table();
	}

	protected function migrate_table() {
		if ( ! static::$table_name || ! static::$sql ) {
			return;
		}

		if ( ! is_array( static::$sql ) ) {
			$sql_chunks = array_filter( array_map( function ( $value ) {
				return rtrim( trim( $value ), ',' );
			}, explode( "\n", static::$sql ) ) );

		} else {
			$sql_chunks = [];

			foreach ( static::$sql as $field => $config ) {
				if ( is_int( $field ) ) {
					$sql_chunks[] = trim( $config );

				} else {
					$sql_chunks[] = $field . ' ' . trim( $config );
				}
			}
		}

		$sql = 'CREATE TABLE ' . static::table_name() . " (\n" . implode( ",\n", $sql_chunks ) . "\n) " . static::$db->get_charset_collate() . ';';

		$db_hash    = md5( $sql );
		$config_key = static::$table_name . '_schema_hash';

		if ( $db_hash != get_option( $config_key ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );

			if ( ! static::$db->last_error ) {
				update_option( $config_key, $db_hash );
			}
		}
	}

	/**
	 * @param string $method
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public static function __callStatic( $method, $args ) {
		if ( method_exists( static::$db, $method ) ) {
			return call_user_func_array( [ static::$db, $method ], $args );
		}

		throw new \BadMethodCallException( sprintf( 'The method does not exist - %s::%s', __CLASS__, $method ) );
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		return static::$db->prefix . static::$table_name;
	}

	/**
	 * @return string
	 */
	public static function get_primary_key() {
		return static::$primary_key;
	}

	/**
	 * @param array        $data
	 * @param array|string $format
	 *
	 * @return false|int
	 */
	public static function insert( array $data, $format = null ) {
		return static::$db->insert( static::table_name(), $data, $format );
	}

	/**
	 * @param array        $data
	 * @param array|string $format
	 *
	 * @return false|int
	 */
	public static function replace( array $data, $format = null ) {
		return static::$db->replace( static::table_name(), $data, $format );
	}

	/**
	 * @param array        $data
	 * @param array        $where
	 * @param array|string $format
	 * @param array|string $where_format
	 *
	 * @return false|int
	 */
	public static function update( array $data, array $where, $format = null, $where_format = null ) {
		return static::$db->update( static::table_name(), $data, $where, $format, $where_format );
	}

	/**
	 * @param array        $where
	 * @param array|string $where_format
	 *
	 * @return false|int
	 */
	public static function delete( array $where, $where_format = null ) {
		return static::$db->delete( static::table_name(), $where, $where_format );
	}

	/**
	 * @param array $key_values
	 * @param array $args
	 *
	 * @return array|object|null
	 */
	public static function find_by( array $key_values = [], array $args = [] ) {
		$table_name = static::table_name();

		$args = wp_parse_args( $args, [
			'fields'  => [],
			'join'    => [],
			'groupby' => [],
			'orderby' => [],
			'limit'   => 0,
			'offset'  => 0,
		] );

		$query = [
			'select'  => "SELECT *",
			'from'    => "FROM {$table_name}",
			'join'    => '',
			'where'   => 'WHERE ' . static::make_conditions( $key_values ),
			'groupby' => '',
			'orderby' => '',
			'limit'   => '',
			'offset'  => '',
		];

		if ( $args['fields'] ) {
			if ( ! is_array( $args['fields'] ) ) {
				$args['fields'] = [ $args['fields'] ];
			}

			$query['select'] = "SELECT " . implode( ', ', array_unique( array_filter( $args['fields'] ) ) );
		}

		if ( $args['join'] ) {
			if ( ! is_array( $args['join'] ) ) {
				$args['join'] = [ $args['join'] ];
			}

			$query['join'] = implode( "\n", array_unique( array_filter( $args['join'] ) ) );
		}

		if ( $args['groupby'] ) {
			if ( ! is_array( $args['groupby'] ) ) {
				$args['groupby'] = [ $args['groupby'] ];
			}

			$query['groupby'] = 'GROUP BY ' . implode( ', ', array_unique( array_filter( $args['groupby'] ) ) );
		}

		if ( $args['orderby'] ) {
			if ( ! is_array( $args['orderby'] ) ) {
				$args['orderby'] = [ $args['orderby'] ];
			}

			$query['orderby'] = 'ORDER BY ' . implode( ', ', array_unique( array_filter( $args['orderby'] ) ) );
		}

		if ( $args['offset'] ) {
			$query['offset'] = "OFFSET {$args['offset']}";
		}

		if ( $args['limit'] ) {
			$query['limit'] = "LIMIT {$args['limit']}";
		}

		return static::$db->get_results( implode( "\n", $query ) );
	}

	/**
	 * @param array $key_values
	 * @param array $args
	 *
	 * @return object|null
	 */
	public static function find_one_by( array $key_values = [], array $args = [] ) {
		$args['limit'] = 1;

		$results = static::find_by( $key_values, $args );

		return $results ? reset( $results ) : null;
	}

	/**
	 * @param string $field
	 * @param array  $key_values
	 * @param array  $args
	 *
	 * @return mixed|null
	 */
	public static function find_var_by( $field, array $key_values = [], array $args = [] ) {
		$args['fields'] = $field;

		$result = static::find_one_by( $key_values, $args );

		return $result ? $result->{$field} : null;
	}

	/**
	 * @param string $field
	 * @param array  $key_values
	 * @param array  $args
	 *
	 * @return array
	 */
	public static function find_col_by( $field, array $key_values = [], array $args = [] ) {
		$args['fields'] = $field;

		$results = static::find_by( $key_values, $args );

		return $results ? wp_list_pluck( $results, $field ) : [];
	}

	/**
	 * @param array $key_values
	 * @param array $args
	 *
	 * @return int
	 */
	public static function count( array $key_values = [], array $args = [] ) {
		$args['fields'] = 'COUNT(*) AS count';

		$result = static::find_one_by( $key_values, $args );

		return $result ? $result->count : 0;
	}

	/**
	 * @param int|string $primary_key
	 *
	 * @return object|null
	 */
	public static function get( $primary_key ) {
		if ( ! static::get_primary_key() ) {
			throw new \BadMethodCallException( sprintf( 'The %s::$primary_key key has not been set.', __CLASS__ ) );
		}

		return static::find_one_by( [ static::get_primary_key() => $primary_key ] );
	}

	/**
	 * @return int
	 */
	public static function get_last_insert_id() {
		return static::$db->insert_id;
	}

	/**
	 * @param        $key_values
	 * @param string $association
	 *
	 * @return string
	 */
	protected static function make_conditions( $key_values, $association = 'AND' ) {
		$where = [];

		if ( ! is_array( $key_values ) ) {
			$key_values = [ $key_values ];
		}

		foreach ( $key_values as $key => $value ) {
			if ( $key === '__OR__' ) {
				$where[] = '(' . static::make_conditions( $value, 'OR' ) . ')';

			} elseif ( $key === '__AND__' ) {
				$where[] = '(' . static::make_conditions( $value ) . ')';

			} elseif ( is_int( $key ) ) {
				$where[] = $value;

			} elseif ( is_array( $value ) ) {
				$is_int = true;

				foreach ( $value as $_value ) {
					if ( ! is_int( $_value ) ) {
						$is_int = false;
						break;
					}
				}

				if ( $is_int ) {
					$value = "{$key} IN (" . implode( ", ", $value ) . ")";

				} else {
					$value = "{$key} IN ('" . implode( "', '", $value ) . "')";
				}

				$where[] = $value;

			} elseif ( is_int( $value ) ) {
				$where[] = "{$key} = {$value}";

			} else {
				$where[] = "{$key} = '{$value}'";
			}
		}

		return $where ? implode( " {$association} ", $where ) : '1=1';
	}
}
