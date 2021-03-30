<?php

namespace Iwf2b\Model;

use Iwf2b\AbstractSingleton;
use Iwf2b\Text;

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
	 * @var string
	 */
	protected static $sql = '';

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

		$this->migrate_table();

		static::$db = $wpdb;
	}

	protected function migrate_table() {
		if ( ! static::$table_name || ! static::$sql ) {
			return;
		}

		$sql_config_name = static::$table_name . '_db_version';

		if ( version_compare( static::$sql_version, get_option( $sql_config_name ), '!=' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$sql = Text::replace( static::$sql, [
				'table_name' => static::table_name(),
				'charset'    => static::$db->get_charset_collate(),
			] );

			dbDelta( $sql );

			if ( ! static::$db->last_error ) {
				update_option( $sql_config_name, static::$sql_version );
			}
		}
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
	 * @param       $code
	 * @param array $args
	 *
	 * @return array|null
	 */
	public static function find_by( array $key_values = array(), array $args = [] ) {
		$table_name = static::table_name();

		$args = wp_parse_args( $args, [
			'fields'  => [],
			'join'    => null,
			'groupby' => null,
			'orderby' => null,
			'limit'   => 0,
			'offset'  => 0,
		] );

		$query = array(
			'select'  => "SELECT *",
			'from'    => "FROM {$table_name}",
			'join'    => '',
			'where'   => 'WHERE ' . static::make_conditions( $key_values ),
			'groupby' => '',
			'orderby' => '',
			'limit'   => '',
			'offset'  => '',
		);

		if ( $args['fields'] ) {
			if ( ! is_array( $args['fields'] ) ) {
				$args['fields'] = [ $args['fields'] ];
			}

			$query['select'] = "SELECT " . implode( ', ', array_unique( array_filter( $args['fields'] ) ) );
		}

		if ( $args['join'] ) {
			$query['join'] = $args['join'];
		}

		if ( $args['groupby'] ) {
			$query['groupby'] = "GROUP BY {$args['groupby']}";
		}

		if ( $args['orderby'] ) {
			$query['orderby'] = "ORDER BY {$args['orderby']}";
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
	 * @param       $code
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
