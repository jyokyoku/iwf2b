<?php

namespace Iwf2b;

abstract class AbstractOption {
	const PREFIX = 'ON_';

	/**
	 * Get a option
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function get( $key ) {
		$value = apply_filters( 'iwf2b/option/get', null, $key, static::class );

		if ( $value !== null ) {
			return $value;
		}

		return \get_option( $key );
	}

	/**
	 * Set a option
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public static function set( $key, $value ) {
		$result = (bool) apply_filters( 'iwf2b/option/set', false, $key, $value, static::class );

		if ( ! $result ) {
			$result = \update_option( $key, $value );
		}

		return $result;
	}

	/**
	 * Clear all options
	 *
	 * @return void
	 */
	public static function clear() {
		$reflection = new \ReflectionClass( static::class );
		$constants  = $reflection->getConstants();

		foreach ( $constants as $key => $value ) {
			if ( strpos( $key, static::PREFIX ) !== 0 ) {
				continue;
			}

			$result = (bool) apply_filters( 'iwf2b/option/delete', false, $value, static::class );

			if ( ! $result ) {
				\delete_option( $value );
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
	public static function __callStatic( string $name, array $arguments ) {
		if ( strpos( $name, static::PREFIX ) === 0 && defined( 'static::' . $name ) ) {
			$key = constant( 'static::' . $name );

			if ( isset( $arguments[0] ) ) {
				self::set( $key, $arguments[0] );

				return null;
			}

			return self::get( $key );
		}

		throw new \Exception( 'Undefined constant: ' . $name );
	}
}
