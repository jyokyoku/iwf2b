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
	 * SQL version
	 *
	 * @var string
	 */
	protected static $sql_version = '';

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

		$sql_config_name = static::$table_name . '_db_version';

		if ( version_compare( static::$sql_version, get_option( $sql_config_name ), '!=' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

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

			dbDelta( $sql );

			if ( ! static::$db->last_error ) {
				update_option( $sql_config_name, static::$sql_version );
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
	public static function find_one_by( array $key_values, array $args = [] ) {
		$args['limit'] = 1;

		$results = static::find_by( $key_values, $args );

		return $results ? reset( $results ) : null;
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

			} else {
				if ( is_int( $key ) ) {
					$where[] = $value;

				} else {
					if ( ! is_int( $value ) ) {
						$value = "'{$value}'";
					}

					$where[] = "{$key} = {$value}";
				}
			}
		}

		return $where ? implode( " {$association} ", $where ) : '1=1';
	}
}
