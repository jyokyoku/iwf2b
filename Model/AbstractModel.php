<?php

namespace Iwf2b\Model;

use Iwf2b\AbstractSingleton;

abstract class AbstractModel extends AbstractSingleton {
	protected static $table_name = '';

	protected static $sql_version = '';

	protected static $sql = '';

	/**
	 * @var \wpdb
	 */
	public static $db;

	/**
	 * @return bool
	 */
	protected function initialize() {
		global $wpdb;

		static::$db = $wpdb;

		if ( ! static::$table_name || ! static::$sql ) {
			return false;
		}

		$sql_config_name = static::$table_name . '_db_version';

		if ( version_compare( static::$sql_version, get_option( $sql_config_name ), '!=' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$table_name = static::table_name();
			$sql        = str_replace( [ '%table_name%', '%charset%' ], [ $table_name, static::$db->get_charset_collate() ], static::$sql );

			dbDelta( $sql );
			update_option( $sql_config_name, static::$sql_version );
		}

		return true;
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . static::$table_name;
	}

	/**
	 * @param $code
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
			'offset'  => 0
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
	 * @param $code
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
	 * @param $key_values
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