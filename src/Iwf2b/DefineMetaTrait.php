<?php

namespace Iwf2b;

use Iwf2b\Post\AbstractPost;
use Iwf2b\Tax\AbstractTax;
use Iwf2b\User\AbstractUser;

trait DefineMetaTrait {
	private $reflection;

	private $meta_defines;

	private static $META_PREFIX = 'MK_';

	private function get_reflection() {
		if ( $this->reflection === null ) {
			$this->reflection = new \ReflectionClass( $this );
		}

		return $this->reflection;
	}

	public function get_meta_defines() {
		if ( $this->meta_defines === null ) {
			$ref       = $this->get_reflection();
			$constants = $ref->getConstants();
			$metas     = [];

			foreach ( $constants as $constant_name => $meta_key ) {
				if ( strpos( $constant_name, static::$META_PREFIX ) === 0 && is_string( $meta_key ) ) {
					$metas[] = $meta_key;
				}
			}

			$this->meta_defines = $metas;
		}

		return $this->meta_defines;
	}

	/**
	 * Get the meta
	 *
	 * @param int|\WP_Post|\WP_Term|\WP_User $object_id
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get_meta( $object_id, $key ) {
		$value = apply_filters( 'iwf2b/meta/get', null, $object_id, $key, static::class );

		if ( $value !== null ) {
			return $value;
		}

		if ( $object_id instanceof \WP_Post ) {
			$object_id = $object_id->ID;
		} elseif ( $object_id instanceof \WP_Term ) {
			$object_id = $object_id->term_id;
		} elseif ( $object_id instanceof \WP_User ) {
			$object_id = $object_id->ID;
		}

		switch ( true ) {
			case is_subclass_of( static::class, AbstractPost::class ):
				$value = \get_post_meta( $object_id, $key, true );
				break;
			case is_subclass_of( static::class, AbstractTax::class ):
				$value = \get_term_meta( $object_id, $key, true );
				break;
			case is_subclass_of( static::class, AbstractUser::class ):
				$value = \get_user_meta( $object_id, $key, true );
				break;
		}

		return $value;
	}

	/**
	 * Set the meta
	 *
	 * @param int|\WP_Post|\WP_Term|\WP_User $object_id
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function set_meta( $object_id, $key, $value ) {
		$result = apply_filters( 'iwf2b/meta/set', null, $object_id, $key, $value, static::class );

		if ( $result === null ) {
			if ( $object_id instanceof \WP_Post ) {
				$object_id = $object_id->ID;
			} elseif ( $object_id instanceof \WP_Term ) {
				$object_id = $object_id->term_id;
			} elseif ( $object_id instanceof \WP_User ) {
				$object_id = $object_id->ID;
			}

			switch ( true ) {
				case is_subclass_of( static::class, AbstractPost::class ):
					$result = \update_post_meta( $object_id, $key, $value );
					break;
				case is_subclass_of( static::class, AbstractTax::class ):
					$result = \update_term_meta( $object_id, $key, $value );
					break;
				case is_subclass_of( static::class, AbstractUser::class ):
					$result = \update_user_meta( $object_id, $key, $value );
					break;
			}
		}

		return $result;
	}

	/**
	 * Clear the all meta
	 *
	 * @param int|\WP_Post|\WP_Term|\WP_User $object_id
	 *
	 * @return void
	 */
	public static function clear_meta( $object_id ) {
		$reflection = new \ReflectionClass( static::class );
		$constants  = $reflection->getConstants();

		foreach ( $constants as $key => $value ) {
			if ( strpos( $key, static::$META_PREFIX ) !== 0 ) {
				continue;
			}

			$result = apply_filters( 'iwf2b/meta/delete', null, $value, static::class );

			if ( $result === null ) {
				if ( $object_id instanceof \WP_Post ) {
					$object_id = $object_id->ID;
				} elseif ( $object_id instanceof \WP_Term ) {
					$object_id = $object_id->term_id;
				} elseif ( $object_id instanceof \WP_User ) {
					$object_id = $object_id->ID;
				}

				switch ( true ) {
					case is_subclass_of( static::class, AbstractPost::class ):
						\delete_post_meta( $object_id, $value );
						break;
					case is_subclass_of( static::class, AbstractTax::class ):
						\delete_term_meta( $object_id, $value );
						break;
					case is_subclass_of( static::class, AbstractUser::class ):
						\delete_user_meta( $object_id, $value );
						break;
				}
			}
		}
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function __callStatic( $name, $arguments ) {
		if ( strpos( $name, static::$META_PREFIX ) === 0 && defined( 'static::' . $name ) ) {
			$key = constant( 'static::' . $name );

			if ( isset( $arguments[0], $arguments[1] ) ) {
				return static::set_meta( $arguments[0], $key, $arguments[1] );
			}

			if ( isset( $arguments[0] ) ) {
				return static::get_meta( $arguments[0], $key );
			}

			throw new \Exception( 'Object ID is not specified: ' . $name );
		}

		throw new \Exception( 'Undefined meta key constant: ' . $name );
	}
}
