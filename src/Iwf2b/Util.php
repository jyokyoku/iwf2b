<?php

namespace Iwf2b;

/**
 * Class Util
 * @package Iwf2b
 */
class Util {
	/**
	 * Based on Laravel
	 * Copyright (c) Taylor Otwell (https://laravel.com)
	 *
	 * @param mixed $value
	 * @param bool $check_empty_string
	 *
	 * @return bool
	 */
	public static function is_empty( $value, $check_empty_string = false ) {
		if ( $value === null || $value === false ) {
			return true;
		}

		if ( is_string( $value ) ) {
			if ( $check_empty_string && trim( $value ) === '' ) {
			return true;
		}

			if ( $value === '' ) {
				return true;
			}
		}

		if ( ( is_array( $value ) || $value instanceof \Countable ) && count( $value ) < 1 ) {
			return true;
		}

		return false;
	}

	/**
	 * @param mixed $value
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function filter( $value, array $args = [] ) {
		$synonym = [
			'ift'  => 'if_true',
			'iff'  => 'if_false',
			'd'    => 'default',
			'f'    => 'filters',
			'pre'  => 'prefix',
			'post' => 'postfix',
		];

		foreach ( $synonym as $synonym_key => $arg_key ) {
			if ( isset( $args[ $synonym_key ] ) && ! isset( $args[ $arg_key ] ) ) {
				$args[ $arg_key ] = $args[ $synonym_key ];
			}

			unset( $args[ $synonym_key ] );
		}

		$args = wp_parse_args( $args, [
			'if_true'  => null,
			'if_false' => null,
			'default'  => null,
			'filters'  => [],
			'prefix'   => null,
			'postfix'  => null,
		] );

		if ( ! static::is_empty( $value ) && $args['if_true'] !== null ) {
			$value = $args['if_true'];
		}

		if ( static::is_empty( $value ) && $args['if_false'] !== null ) {
			$value = $args['if_false'];
		}

		if ( static::is_empty( $value ) && $args['default'] !== null ) {
			return $args['default'];
		}

		if ( ! static::is_empty( $value ) ) {
			if ( $args['filters'] ) {
				if ( ! is_array( $args['filters'] ) ) {
					$args['filters'] = [ $args['filters'] ];
				}

				foreach ( $args['filters'] as $filter_func => $filter_args ) {
					if ( is_int( $filter_func ) && is_callable( $filter_args ) ) {
						$filter_func = $filter_args;
						$filter_args = [];
					}

					if ( is_callable( $filter_func ) ) {
						if ( is_array( $filter_args ) ) {
							$filter_args = array_values( $filter_args );

						} else {
							$filter_args = [ $filter_args ];
						}

						array_unshift( $filter_args, $value );

						$value = call_user_func_array( $filter_func, $filter_args );
					}
				}
			}

			if ( is_scalar( $value ) && ( $args['prefix'] !== null || $args['postfix'] !== null ) ) {
				$value = $args['prefix'] . $value . $args['postfix'];
			}
		}

		return $value;
	}

	/**
	 * @param string|array $to
	 * @param string $subject
	 * @param string $mail_body
	 * @param array $args
	 *
	 * @return bool
	 */
	public static function mail( $to, $subject, $mail_body, array $args = [] ) {
		$args = Arr::merge_intersect_key( [
			'from'        => '',
			'cc'          => [],
			'bcc'         => [],
			'attachments' => [],
		], $args );

		$headers = [];

		if ( $args['from'] ) {
			// Format "from" address
			if ( is_array( $args['from'] ) ) {
				$from_addr = reset( $args['from'] );
				$from_name = key( $args['from'] );
				$headers[] = $from_name ? 'From: ' . $from_name . ' <' . $from_addr . '>' : 'From: ' . $from_addr;

			} else {
				$headers[] = 'From: ' . $args['from'];
			}
		}

		if ( $args['cc'] ) {
			// Format "cc" addresses
			if ( ! is_array( $args['cc'] ) ) {
				$args['cc'] = (array) $args['cc'];
			}

			foreach ( $args['cc'] as $cc_name => $cc_addr ) {
				if ( ! is_email( $cc_addr ) ) {
					continue;
				}

				$headers[] = $cc_name ? 'Cc: ' . $cc_name . ' <' . $cc_addr . '>' : 'Cc: ' . $cc_addr;
			}
		}

		if ( $args['bcc'] ) {
			// Format "bcc" addresses
			if ( ! is_array( $args['bcc'] ) ) {
				$args['bcc'] = (array) $args['bcc'];
			}

			foreach ( $args['bcc'] as $bcc_name => $bcc_addr ) {
				if ( ! is_email( $bcc_addr ) ) {
					continue;
				}

				$headers[] = $bcc_name ? 'Bcc: ' . $bcc_name . ' <' . $bcc_addr . '>' : 'Bcc: ' . $bcc_addr;
			}
		}

		if ( is_string( $to ) && strpos( $to, ',' ) !== false ) {
			$to = array_filter( array_map( 'trim', explode( ',', $to ) ) );

		} else if ( ! is_array( $to ) ) {
			$to = (array) $to;
		}

		// Format "to" addresses
		$formatted_to = [];

		foreach ( $to as $to_name => $to_addr ) {
			if ( ! is_email( $to_addr ) ) {
				continue;
			}

			$formatted_to[] = $to_name && ! is_int( $to_name ) ? $to_name . ' <' . $to_addr . '>' : $to_addr;
		}

		return wp_mail( $formatted_to, $subject, $mail_body, $headers, $args['attachments'] );
	}
}