<?php

namespace Iwf2b\User;

use Iwf2b\AbstractSingleton;
use Iwf2b\Arr;
use Iwf2b\Util;

/**
 * Class AbstractUser
 * @package Iwf2b\User
 */
class AbstractUser extends AbstractSingleton {
	/**
	 * Role slug
	 *
	 * @var string
	 */
	protected static $role = '';

	/**
	 * Role label
	 *
	 * @var string
	 */
	protected static $role_label = '';

	/**
	 * Capabilities
	 *
	 * @var array
	 */
	protected static $capabilities = [];

	/**
	 * Search conditions
	 *
	 * @var array
	 */
	protected static $find_args = [];

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		add_action( 'init', [ $this, 'register_roles' ] );
	}

	/**
	 * Register roles
	 */
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
	 * @return \WP_User|int|string
	 */
	public static function get( $user_id ) {
		$user = null;

		if ( $user_id instanceof \WP_User ) {
			$user = $user_id;

		} else if ( is_numeric( $user_id ) && preg_match( '/^[0-9]+?$/', $user_id ) ) {
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

	/**
	 * @param int|string|\WP_User $user_id
	 * @param string $key
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public static function get_meta( $user_id, $key, $args = [] ) {
		$user = static::get( $user_id );

		if ( ! $user ) {
			return null;
		}

		if ( ! is_array( $args ) ) {
			$args = [ 'd' => $args ];
		}

		$acf_options = Arr::merge_intersect_key( [
			'noautop' => false,
			'raw'     => false,
		], (array) Arr::get( $args, 'acf', [] ) );

		unset( $args['acf'] );

		if ( $acf_options['noautop'] ) {
			remove_filter( 'acf_the_content', 'wpautop' );
		}

		$value = function_exists( 'get_field' ) ? get_field( $key, $user, ! $acf_options['raw'] ) : get_user_meta( $user->ID, $key, true );

		if ( $acf_options['noautop'] ) {
			add_filter( 'acf_the_content', 'wpautop' );
		}

		return Util::filter( $value, $args );
	}

	/**
	 * @param string $user_login
	 * @param string $user_pass
	 * @param array $userdata
	 *
	 * @return int|\WP_Error
	 */
	public static function insert( $user_login, $user_pass, array $userdata = [] ) {
		$userdata['user_login'] = $user_login;
		$userdata['user_pass']  = $user_pass;

		if ( static::get_role() ) {
			$userdata['role'] = static::get_role();
		}

		unset( $userdata['ID'] );

		return wp_insert_user( $userdata );
	}
}