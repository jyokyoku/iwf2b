<?php

namespace Iwf2b\User;

use Iwf2b\AbstractSingleton;
use Iwf2b\Arr;
use Iwf2b\DefineMetaTrait;
use Iwf2b\Util;

/**
 * Class AbstractUser
 *
 * @package Iwf2b\User
 */
class AbstractUser extends AbstractSingleton {
	use DefineMetaTrait;

	/**
	 * Role slug
	 *
	 * @var string
	 */
	protected $role = '';

	/**
	 * Role label
	 *
	 * @var string
	 */
	protected $role_label = '';

	/**
	 * Capabilities
	 *
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * Search conditions
	 *
	 * @var array
	 */
	protected $find_args = [];

	/**
	 * Insert the default metas when user added
	 *
	 * @var bool
	 */
	protected $insert_default_meta = true;

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		add_action( 'init', [ $this, 'register_roles' ] );
		add_filter( 'user_register', [ $this, 'insert_default_meta' ], 10, 3 );
	}

	/**
	 * Register roles
	 */
	public function register_roles() {
		if ( $this->role ) {
			$role = get_role( $this->role );

			if ( ! $role ) {
				$role = add_role( $this->role, $this->role_label ?: $this->role );
			}

			if ( $this->capabilities ) {
				foreach ( (array) $this->capabilities as $capability ) {
					if ( ! $role->has_cap( $capability ) ) {
						$role->add_cap( $capability );
					}
				}
			}
		}
	}

	/**
	 * @param int $user_id
	 */
	public function insert_default_meta( $user_id ) {
		if ( ! $this->insert_default_meta || ! static::is_valid( $user_id ) ) {
			return;
		}

		foreach ( $this->get_meta_defines() as $meta_key ) {
			if ( ! metadata_exists( 'user', $user_id, $meta_key ) ) {
				add_user_meta( $user_id, $meta_key, '' );
			}
		}
	}

	/**
	 * @param $user_id
	 *
	 * @return \WP_User|int|string
	 */
	public static function get( $user_id ) {
		$self = static::get_instance();
		$user = null;

		if ( $user_id instanceof \WP_User ) {
			$user = $user_id;

		} elseif ( is_numeric( $user_id ) && preg_match( '/^[0-9]+?$/', $user_id ) ) {
			$user = get_user_by( 'id', (int) $user_id );

		} elseif ( is_string( $user_id ) ) {
			if ( is_email( $user_id ) ) {
				$user = get_user_by( 'email', $user_id );

			} else {
				$user = get_user_by( 'login', $user_id );

				if ( ! $user ) {
					$user = get_user_by( 'slug', $user_id );
				}
			}
		}

		if ( ! $user || ( $self->role && ! user_can( $user, $self->role ) ) ) {
			return null;
		}

		return $user;
	}

	/**
	 * @param $user_id
	 *
	 * @return int
	 */
	public static function get_id( $user_id = null ) {
		$user = static::get( $user_id );

		if ( ! $user ) {
			return 0;
		}

		return $user->ID;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function create_args( array $args = [] ) {
		$self = static::get_instance();
		$args = array_merge( $self->find_args, $args );

		if ( $self->role ) {
			$args['role'] = $self->role;
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
		return static::get_instance()->role;
	}

	/**
	 * @return string
	 */
	public static function get_role_label() {
		return static::get_instance()->role_label;
	}

	/**
	 * @param $user_id
	 */
	public static function is_valid( $user_id = null ) {
		return static::get( $user_id ) ? true : false;
	}

	/**
	 * @param int|string|\WP_User $user_id
	 * @param string              $key
	 * @param mixed               $args
	 *
	 * @return mixed
	 */
	public static function get_meta( $user_id, $key, $args = [] ) {
		$user = static::get( $user_id );

		if ( ! $user ) {
			return null;
		}

		if ( ! is_array( $args ) ) {
			$args = [ 'default' => $args ];
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
	 * @param array  $userdata
	 *
	 * @return int|\WP_Error
	 */
	public static function insert( $user_login, $user_pass, array $userdata = [] ) {
		$self = static::get_instance();

		$userdata['user_login'] = $user_login;
		$userdata['user_pass']  = $user_pass;

		if ( $self->role ) {
			$userdata['role'] = $self->role;
		}

		unset( $userdata['ID'] );

		return wp_insert_user( $userdata );
	}
}
