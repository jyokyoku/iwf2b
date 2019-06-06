<?php

namespace Iwf2b;

class Util {
	/**
	 * @return string
	 */
	public static function get_dummy_image() {
		return get_stylesheet_directory_uri() . '/img/no_image.jpg';
	}

	/**
	 * @param mixed $value
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function filter( $value, array $args = [] ) {
		$synonym = [
			'd'    => 'default',
			'r'    => 'replace',
			'f'    => 'filters',
			'pre'  => 'prefix',
			'post' => 'postfix',
		];

		foreach ( $synonym as $synonym_key => $arg_key ) {
			if ( isset( $args[ $synonym_key ] ) && empty( $args[ $arg_key ] ) ) {
				$args[ $arg_key ] = $args[ $synonym_key ];
			}

			unset( $args[ $synonym_key ] );
		}

		$args = wp_parse_args( $args, [
			'default' => null,
			'filters' => [],
			'prefix'  => null,
			'postfix' => null,
			'replace' => '',
		] );

		if ( empty( $value ) && $args['default'] !== null ) {
			return $args['default'];
		}

		if ( $value ) {
			if ( $args['replace'] ) {
				$value = $args['replace'];
			}

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
	 * @param array $data
	 * @param mixed ...$args
	 *
	 * @return array
	 */
	public static function array_merge_deep( array $data, ...$args ) {
		$return = $data;

		foreach ( $args as &$current_arg ) {
			$stack[] = [ (array) $current_arg, &$return ];
		}

		unset( $current_arg );

		while ( ! empty( $stack ) ) {
			foreach ( $stack as $current_key => &$current_merge ) {
				foreach ( $current_merge[0] as $key => &$val ) {
					if ( ! empty( $current_merge[1][ $key ] ) && (array) $current_merge[1][ $key ] === $current_merge[1][ $key ] && (array) $val === $val ) {
						$stack[] = [ &$val, &$current_merge[1][ $key ] ];

					} elseif ( (int) $key === $key && isset( $current_merge[1][ $key ] ) ) {
						$current_merge[1][] = $val;

					} else {
						$current_merge[1][ $key ] = $val;
					}
				}

				unset( $stack[ $current_key ] );
			}

			unset( $current_merge );
		}

		return $return;
	}

	/**
	 * @param $value
	 * @param array $protocols
	 * @param array $attributes
	 *
	 * @return string|null
	 */
	public static function auto_link( $value, $protocols = [ 'http', 'mail' ], array $attributes = [] ) {
		// Link attributes
		$attr = '';
		foreach ( $attributes as $key => $val ) {
			$attr = ' ' . $key . '="' . htmlentities( $val ) . '"';
		}

		$links = [];

		// Extract existing links and tags
		$value = preg_replace_callback( '~(<a .*?>.*?</a>|<.*?>)~i', function ( $match ) use ( &$links ) {
			return '<' . array_push( $links, $match[1] ) . '>';
		}, $value );

		// Extract text links for each protocol
		foreach ( (array) $protocols as $protocol ) {
			switch ( $protocol ) {
				case 'http':
				case 'https':
					$value = preg_replace_callback( '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ( $match ) use ( $protocol, &$links, $attr ) {
						if ( $match[1] ) {
							$protocol = $match[1];
						}
						$link = $match[2] ?: $match[3];

						return '<' . array_push( $links, "<a $attr href=\"$protocol://$link\">$protocol://$link</a>" ) . '>';
					}, $value );
					break;

				case 'mail':
					$value = preg_replace_callback( '~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ( $match ) use ( &$links, $attr ) {
						return '<' . array_push( $links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>" ) . '>';
					}, $value );
					break;

				case 'twitter':
					$value = preg_replace_callback( '~(?<!\w)[@#](\w++)~', function ( $match ) use ( &$links, $attr ) {
						return '<' . array_push( $links, "<a $attr href=\"https://twitter.com/" . ( $match[0][0] === '@' ? '' : 'search/%23' ) . $match[1] . "\">{$match[0]}</a>" ) . '>';
					}, $value );
					break;

				default:
					$value = preg_replace_callback( '~' . preg_quote( $protocol, '~' ) . '://([^\s<]+?)(?<![\.,:])~i', function ( $match ) use ( $protocol, &$links, $attr ) {
						return '<' . array_push( $links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>" ) . '>';
					}, $value );
					break;
			}
		}

		// Insert all link
		return preg_replace_callback( '/<(\d+)>/', function ( $match ) use ( &$links ) {
			return $links[ $match[1] - 1 ];
		}, $value );
	}

	/**
	 * @param $text
	 *
	 * @return mixed
	 */
	public static function reverse_wpautop( $text ) {
		$text = str_replace( "\n", "", $text );
		$text = str_replace( "<p>", "", $text );
		$text = str_replace( [ "<br />", "<br>", "<br/>" ], "\n", $text );
		$text = str_replace( "</p>", "\n\n", $text );

		return $text;
	}

	/**
	 * エレメントを取得
	 *
	 * @param $slug
	 * @param string $name
	 * @param array $vars
	 */
	public static function element( $slug, $name = '', $vars = [] ) {
		$templates = [];
		$name      = (string) $name;

		if ( $name !== '' ) {
			$templates[] = "parts/{$slug}-{$name}.php";
		}

		$templates[]    = "parts/{$slug}.php";
		$_template_file = locate_template( $templates, false, false );

		if ( $_template_file ) {
			extract( $vars, EXTR_OVERWRITE );
			include $_template_file;
		}
	}

	/**
	 * @param $text
	 * @param $vars
	 * @param string $bounds
	 *
	 * @return mixed
	 */
	public static function replace( $text, $vars, $bounds = '%' ) {
		$replaces = $searches = [];

		foreach ( $vars as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$searches[] = $bounds . $key . $bounds;
			$replaces[] = (string) $value;
		}

		return str_replace( $searches, $replaces, $text );
	}

	/**
	 * @param string $message
	 * @param bool $with_callee
	 */
	public static function log( $message = null, $with_callee = true ) {
		if ( ! is_string( $message ) ) {
			$message = print_r( $message, true );
		}

		$log_dir = trailingslashit( WP_CONTENT_DIR . '/iwf2b-logs' );

		if ( ! is_dir( $log_dir ) ) {
			if ( ! @mkdir( $log_dir ) && ! is_dir( $log_dir ) ) {
				if ( is_super_admin() ) {
					wp_die( 'Could not make a log directory. - ' . $log_dir );
				}
			}
		}

		$log_file = $log_dir . date( 'Y-m-d', current_time( 'timestamp' ) ) . '.log';

		if ( ! is_file( $log_file ) ) {
			if ( ! @touch( $log_file ) ) {
				if ( is_super_admin() ) {
					wp_die( 'Could not make a log file. - ' . $log_file );
				}
			}
		}

		$time = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
		$line = sprintf( '[%s] %s', $time, $message );

		if ( $with_callee ) {
			$backtrace = debug_backtrace();

			if ( strpos( $backtrace[0]['file'], 'iwf2b/Util.php' ) !== false ) {
				$callee = $backtrace[1];

			} else {
				$callee = $backtrace[0];
			}

			$line .= sprintf( ' - in %s, line %s', $callee['file'], $callee['line'] );
		}

		$line .= PHP_EOL;

		file_put_contents( $log_file, $line, FILE_APPEND );
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
		 * Fromアドレスをheader形式に変換
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
		 * CCアドレスをheader形式に変換
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
		 * BCCアドレスをheader形式に変換
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
		 * 送信先アドレスを配列化
		 */
		if ( strpos( $to, ',' ) !== false ) {
			$to = array_filter( array_map( 'trim', explode( ',', $to ) ) );
		}

		if ( ! is_array( $to ) ) {
			$to = (array) $to;
		}

		/**
		 * 送信先アドレスを正しい形式に変換
		 */
		$to_addrs = [];

		foreach ( $to as $to_name => $to_addr ) {
			if ( ! is_email( $to_addr ) ) {
				continue;
			}

			$to_addrs[] = $to_name && ! is_int( $to_name ) ? $to_name . ' <' . $to_addr . '>' : $to_addr;
		}

		/**
		 * テンプレートに変数を展開
		 */
		$mail_body = static::replace( $mail_body, $vars, '%' );
		$subject   = static::replace( $subject, $vars, '%' );

		/**
		 * 送信処理
		 */
		if ( $result = wp_mail( $to_addrs, $subject, $mail_body, $headers, $args['attachments'] ) ) {
			static::log( sprintf( 'Email sent success - To: %s, Title: %s, Body: %s', implode( ',', $to_addrs ), $subject, $mail_body ) );

		} else {
			static::log( sprintf( 'Email sent failure - To: %s, Title: %s, Body: %s', implode( ',', $to_addrs ), $subject, $mail_body ) );
		}

		return $result;
	}
}