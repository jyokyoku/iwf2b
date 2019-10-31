<?php

namespace Iwf2b;

/**
 * Class Arr
 * @package Iwf2b
 */
class Arr {
	/**
	 * Get a value from array
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $default
	 *
	 * @return mixed|null
	 */
	public static function get( array $array, $key, $default = null ) {
		if ( is_array( $key ) ) {
			$return = [];

			foreach ( $key as $sub_key => $sub_default ) {
				if ( is_int( $sub_key ) && ( is_string( $sub_default ) || is_numeric( $sub_default ) ) ) {
					$sub_key     = (string) $sub_default;
					$sub_default = $default;
				}

				$return[ $sub_key ] = static::get( $array, $sub_key, $sub_default );
			}

			return $return;
		}

		$key_parts = explode( '.', $key );
		$return    = $array;

		foreach ( $key_parts as $i => $key_part ) {
			if ( ! is_array( $return ) || ( ! array_key_exists( $key_part, $return ) ) ) {
				return $default;
			}

			$return = $return[ $key_part ];
		}

		return $return;
	}

	/**
	 * Set the value to array
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $value
	 */
	public static function set( array &$array, $key, $value = null ) {
		if ( is_array( $key ) ) {
			foreach ( $key as $sub_key => $sub_value ) {
				static::set( $array, $sub_key, $sub_value );
			}

		} else {
			$key_parts = explode( '.', $key );

			while ( count( $key_parts ) > 1 ) {
				$key_part = array_shift( $key_parts );

				if ( ! isset( $array[ $key_part ] ) || ! is_array( $array[ $key_part ] ) ) {
					$array[ $key_part ] = [];
				}

				$array =& $array[ $key_part ];
			}

			$array[ array_shift( $key_parts ) ] = $value;
		}
	}

	/**
	 * Delete the key form array
	 *
	 * @param array $array
	 * @param mixed $key
	 *
	 * @return array|bool
	 */
	public static function delete( array &$array, $key ) {
		if ( is_array( $key ) ) {
			$return = [];

			foreach ( $key as $sub_key ) {
				$return[ $sub_key ] = static::delete( $array, $sub_key );
			}

			return $return;
		}

		$key_parts = explode( '.', $key );
		$this_key  = array_shift( $key_parts );

		if ( ! array_key_exists( $this_key, $array ) ) {
			return false;
		}

		if ( ! empty( $key_parts ) ) {
			$key = implode( '.', $key_parts );

			return is_array( $array[ $this_key ] ) ? static::delete( $array[ $this_key ], $key ) : false;
		}

		unset( $array[ $this_key ] );

		return true;
	}

	/**
	 * Check if the key exists in array
	 *
	 * @param array $array
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public static function exists( array $array, $key ) {
		$key_parts = explode( '.', $key );
		$current   = $array;

		foreach ( $key_parts as $key_part ) {
			if ( ! is_array( $current ) || ! array_key_exists( $key_part, $current ) ) {
				return false;
			}

			$current = $current[ $key_part ];
		}

		return true;
	}

	/**
	 * Based on CakePHP
	 * Copyright (c) 2005-present, Cake Software Foundation, Inc. (https://cakefoundation.org)
	 *
	 * @param array $array
	 * @param mixed $_args
	 *
	 * @return array
	 */
	public static function merge( array $array, array $_args ) {
		$args   = array_slice( func_get_args(), 1 );
		$return = $array;

		foreach ( $args as &$merge ) {
			$stack[] = [ (array) $merge, &$return ];
		}

		unset( $merge );
		static::merge_recursive( $stack );

		return $return;
	}

	/**
	 * @param array $array
	 * @param array $_args
	 *
	 * @return array
	 */
	public static function merge_intersect_key( array $array, array $_args ) {
		$args   = array_slice( func_get_args(), 1 );
		$return = $array;

		foreach ( $args as &$merge ) {
			$stack[] = [ (array) $merge, &$return ];
		}

		unset( $merge );
		static::merge_recursive( $stack, true );

		return $return;
	}

	/**
	 * Based on CakePHP
	 * Copyright (c) 2005-present, Cake Software Foundation, Inc. (https://cakefoundation.org)
	 *
	 * @param $stack
	 * @param bool $intersect_key
	 */
	protected static function merge_recursive( $stack, $intersect_key = false ) {
		while ( ! empty( $stack ) ) {
			foreach ( $stack as $i => &$merges ) {
				foreach ( $merges[0] as $key => &$val ) {
					if ( is_array( $merges[1] ) && ! empty( $merges[1][ $key ] ) && (array) $merges[1][ $key ] === $merges[1][ $key ] && (array) $val === $val ) {
						$stack[] = [ &$val, &$merges[1][ $key ] ];

					} else if ( $intersect_key ) {
						if ( array_key_exists( $key, $merges[1] ) ) {
							$merges[1][ $key ] = $val;
						}

					} else if ( (int) $key === $key && isset( $merges[1][ $key ] ) ) {
						$merges[1][] = $val;

					} else {
						$merges[1][ $key ] = $val;
					}
				}

				unset( $stack[ $i ] );
			}

			unset( $merges );
		}
	}

	/**
	 * @param string $glue
	 * @param array $pieces
	 *
	 * @return string
	 */
	public static function implode( $glue, $pieces ) {
		foreach ( $pieces as $key => $piece ) {
			if ( is_array( $piece ) ) {
				$pieces[ $key ] = static::implode( $glue, $piece );

			} else {
				$pieces[ $key ] = Text::stringify( $piece, $glue );
			}
		}

		return implode( $glue, $pieces );
	}

	/**
	 * @param mixed $values
	 * @param callable $callable
	 * @param mixed $_args
	 */
	public static function apply( &$values, callable $callable, $_args = null ) {
		$args = array_slice( func_get_args(), 2 );

		if ( is_array( $values ) ) {
			foreach ( $values as $key => &$value ) {
				$func_args = array_merge( [ &$value, $callable ], $args );
				call_user_func_array( 'static::apply', $func_args );
			}

		} else {
			$func_args = array_merge( [ &$values ], $args );
			$values    = call_user_func_array( $callable, $func_args );
		}
	}
}
