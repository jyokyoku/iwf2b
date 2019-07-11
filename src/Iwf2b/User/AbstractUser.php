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
		$user = null;

		if ( $user_id instanceof \WP_User ) {
			$user = $user_id;

		} else if ( preg_match( '/^[0-9]+?$/', $user_id ) ) {
			$user = get_user_by( 'id', (int) $user_id );

		} else if ( is_string( $user_id ) ) {
			if ( is_email( $user_id ) ) {
				$user = get_user_by( 'email', $user_id );

			} else {
				$user = get_user_by( 'login', $user_id );

				if ( ! $user ) {
					$user = get_user_by( 'slug', $user_id );
				}
			}
		}

		if ( ! $user || ( static::$role && ! user_can( $user, static::$role ) ) ) {
			return null;
		}

		return $user;
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
		return static::get( $user_id ) ? true : false;
	}
}