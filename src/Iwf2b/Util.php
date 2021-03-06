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
		$args = wp_parse_args( $args, [
			'empty'    => null,
			'nonempty' => null,
			'default'  => null,
			'filters'  => [],
			'prefix'   => null,
			'suffix'   => null,
		] );

		if ( empty( $value ) && $args['empty'] !== null ) {
			return $args['empty'];
		}

		if ( ! empty( $value ) && $args['nonempty'] !== null ) {
			return $args['nonempty'];
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

			if ( ( is_string( $value ) || is_numeric( $value ) ) ) {
				if ( $args['prefix'] !== null ) {
					$value = $args['prefix'] . $value;
				}

				if ( $args['suffix'] !== null ) {
					$value .= $args['suffix'];
				}
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

	/**
	 * @param \ReflectionFunctionAbstract|\ReflectionClass|\ReflectionProperty $reflection
	 * @param array|string $search
	 *
	 * @return array
	 */
	public static function parse_phpdoc_tags( $reflection, $search = [] ) {
		if ( ! $reflection instanceof \ReflectionClass
		     && ! $reflection instanceof \ReflectionFunctionAbstract
		     && ! $reflection instanceof \ReflectionProperty
		) {
			throw new \InvalidArgumentException( 'Invalid variable type of $target. The $target required the instance of ReflectionClass or ReflectionFunctionAbstract or ReflectionProperty.' );
		}

		if ( $search && ! is_array( $search ) ) {
			$search = [ $search ];
		}

		$tags    = [];
		$comment = Text::convert_eol( $reflection->getDocComment() );

		if ( preg_match_all( "/@(.+?)(?:\s+(.*))?[\n*]/u", $comment, $matches ) ) {
			foreach ( $matches[1] as $i => $tag ) {
				if ( $search && ! in_array( $tag, $search ) ) {
					continue;
				}

				$tags[ $tag ] = trim( $matches[2][ $i ] );
			}
		}

		return $tags;
	}

	/**
	 * @param $class
	 *
	 * @return array
	 */
	public static function namespace_split( $class ) {
		$pos = strrpos( $class, '\\' );

		if ( $pos === false ) {
			return [ '', $class ];
		}

		return [ substr( $class, 0, $pos ), substr( $class, $pos + 1 ) ];
	}
}