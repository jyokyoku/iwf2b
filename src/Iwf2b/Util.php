<?php

namespace Iwf2b;

class Util {
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

		$is_empty = function ( $value ) {
			return $value === '' || $value === [] || $value === false || $value === null;
		};

		if ( ! $is_empty( $value ) && $args['if_true'] !== null ) {
			$value = $args['if_true'];
		}

		if ( $is_empty( $value ) && $args['if_false'] !== null ) {
			$value = $args['if_false'];
		}

		if ( $is_empty( $value ) && $args['default'] !== null ) {
			return $args['default'];
		}

		if ( ! $is_empty( $value ) ) {
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
	 * @param $template
	 * @param $to
	 * @param $subject
	 * @param $vars
	 * @param array $args
	 *
	 * @return bool
	 */
	public static function mail( $mail_body, $to, $subject, $vars = [], $args = [] ) {
		$args = wp_parse_args( $args, [
			'from'        => '',
			'cc'          => [],
			'bcc'         => [],
			'attachments' => [],
		] );

		if ( ! is_array( $vars ) ) {
			$vars = (array) $vars;
		}

		foreach ( $vars as $i => $var ) {
			if ( is_array( $var ) ) {
				$vars[ $i ] = array_values( $var );
			}
		}

		$headers = [];

		/**
		 * "From" address to header format
		 */
		if ( $args['from'] ) {
			if ( is_array( $args['from'] ) ) {
				$from_addr = reset( $args['from'] );
				$from_name = key( $args['from'] );
				$headers[] = $from_name ? 'From: ' . $from_name . ' <' . $from_addr . '>' : 'From: ' . $from_addr;

			} else {
				$headers[] = 'From: ' . $args['from'];
			}
		}

		/**
		 * "CC" address to header format
		 */
		if ( $args['cc'] ) {
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

		/**
		 * "BCC" address to header format
		 */
		if ( $args['bcc'] ) {
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

		/**
		 * "TO" address to array
		 */
		if ( strpos( $to, ',' ) !== false ) {
			$to = array_filter( array_map( 'trim', explode( ',', $to ) ) );
		}

		if ( ! is_array( $to ) ) {
			$to = (array) $to;
		}

		/**
		 * "TO" address to correct format
		 */
		$to_addrs = [];

		foreach ( $to as $to_name => $to_addr ) {
			if ( ! is_email( $to_addr ) ) {
				continue;
			}

			$to_addrs[] = $to_name && ! is_int( $to_name ) ? $to_name . ' <' . $to_addr . '>' : $to_addr;
		}

		/**
		 * Create mail body and subject
		 */
		$mail_body = Text::replace( $mail_body, $vars, '%' );
		$subject   = Text::replace( $subject, $vars, '%' );

		/**
		 * Submit mail
		 */
		if ( $result = wp_mail( $to_addrs, $subject, $mail_body, $headers, $args['attachments'] ) ) {
			static::log( sprintf( 'Email sent success - To: %s, Title: %s, Body: %s', implode( ',', $to_addrs ), $subject, $mail_body ) );

		} else {
			static::log( sprintf( 'Email sent failure - To: %s, Title: %s, Body: %s', implode( ',', $to_addrs ), $subject, $mail_body ) );
		}

		return $result;
	}
}