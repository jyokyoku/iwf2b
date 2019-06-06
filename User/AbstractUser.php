<?php

namespace Iwf2b\User;

use Iwf2b\AbstractSingleton;

class AbstractUser extends AbstractSingleton {
	protected static $role = '';

	protected static $role_label = '';

	protected static $capabilities = [];

	protected static $find_args = [];

	protected function initialize() {
		add_action( 'init', [ $this, 'register_roles' ] );
	}

	public function register_roles() {
		if ( static::$role ) {
			$role = get_role( static::$role );

			if ( ! $role ) {
				$role = add_role( static::$role, static::$role_label ?: static::$role );
			}

			if ( static::$capabilities ) {
				foreach ( (array) static::$capabilities as $capability ) {
					if ( ! $role->has_cap( $capability ) ) {
						$role->add_cap( $capability );
					}
				}
			}
		}
	}

	/**
	 * @param $user_id
	 *
	 * @return \WP_User|null
	 */
	public static function get( $user_id ) {
		if ( is_object( $user_id ) && ! empty( $user_id->ID ) ) {
			$user_id = $user_id->ID;
		}

		if ( ! is_numeric( $user_id ) ) {
			return null;
		}

		return get_userdata( $user_id );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function create_args( array $args = [] ) {
		$args = array_merge( static::$find_args, $args );

		if ( static::$role ) {
			$args['role'] = static::$role;
		}

		return $args;
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_User[]
	 */
	public static function get_users( array $args = [] ) {
		return get_users( static::create_args( $args ) );
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_User|null
	 */
	public static function get_user( array $args = [] ) {
		$users = static::get_users( $args );

		if ( $users ) {
			return reset( $users );
		}

		return null;
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_User_Query
	 */
	public static function get_query( $args = [] ) {
		return new \WP_User_Query( static::create_args( $args ) );
	}

	/**
	 * @return string
	 */
	public static function get_role() {
		return static::$role;
	}

	/**
	 * @return string
	 */
	public static function get_role_label() {
		return static::$role_label;
	}

	/**
	 * @param $user_id
	 */
	public static function is_valid( $user_id = null ) {
		$user = null;

		if ( $user_id ) {
			$user = static::get( $user_id );

		} else if ( $user_id === null ) {
			$user = wp_get_current_user();
		}

		if ( ! $user || $user->_deleted ) {
			return false;
		}

		if ( empty( static::$role ) ) {
			return true;
		}

		return user_can( $user, static::$role );
	}

	/**
	 * 権限設定
	 */
	public static function init_role() {
		$role = get_role( static::$role );

		if ( ! $role ) {
			$role = add_role( static::$role, static::$role_label );
		}
	}
}