<?php

namespace Iwf2b;

/**
 * Class Text
 * @package Iwf2b
 */
class Text {
	/**
	 * @param $text
	 * @param $vars
	 * @param string $bounds
	 *
	 * @return string
	 */
	public static function replace( $text, $vars, $bounds = '%' ) {
		$replaces = $searches = [];

		foreach ( $vars as $key => $value ) {
			$searches[] = $bounds . $key . $bounds;
			$replaces[] = static::stringify( $value );
		}

		return str_replace( $searches, $replaces, $text );
	}

	/**
	 * @param $text
	 * @param int $length
	 * @param string $ellipsis
	 *
	 * @return string
	 */
	public static function truncate( $text, $length = 200, $ellipsis = '...' ) {
		$text = wp_strip_all_tags( strip_shortcodes( $text ), true );

		if ( mb_strlen( $text ) > $length ) {
			$text = mb_substr( $text, 0, $length ) . $ellipsis;
		}

		return $text;
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

		return trim( $text );
	}

	/**
	 * @param $string
	 * @param string $to
	 *
	 * @return string
	 */
	public static function convert_eol( $string, $to = "\n" ) {
		return strtr( $string, [ "\r\n" => $to, "\r" => $to, "\n" => $to ] );
	}

	/**
	 * @param mixed $value
	 * @param string $glue
	 *
	 * @return string
	 */
	public static function stringify( $value, $glue = ', ' ) {
		if ( $value instanceof \Traversable ) {
			$value = iterator_to_array( $value, false );
		}

		if ( is_array( $value ) ) {
			$converted = [];

			foreach ( $value as $key => $_value ) {
				$converted[ $key ] = static::stringify( $_value, $glue );
			}

			$value = implode( $glue, $converted );

		} else if ( is_object( $value ) ) {
			if ( method_exists( $value, '__toString' ) ) {
				$value = (string) $value;

			} else {
				$value = '(Object)';
			}

		} else if ( $value === true ) {
			$value = 'true';

		} else if ( $value === false ) {
			$value = 'false';

		} else {
			$value = (string) $value;
		}

		return $value;
	}

	/**
	 * @param string $value
	 * @param string $algo
	 * @param string $salt
	 *
	 * @return string
	 */
	public static function short_hash( $value, $algo = 'crc32b', $salt = '' ) {
		if ( ! is_string( $value ) ) {
			$value = serialize( $value );
		}

		return strtr( rtrim( base64_encode( pack( 'H*', hash( $algo, $value . $salt ) ) ), '=' ), '+/', '-_' );
	}

	/**
	 * Convert case style
	 *
	 * @param string $text
	 * @param string $to_case
	 */
	public static function convert_case( $text, $to_case = 'snake' ) {
		switch ( $to_case ) {
			case 'snake':
				$text = ltrim( preg_replace( '/[\-_]+/', '_', strtolower( preg_replace( '/[A-Z]/', '_\0', $text ) ) ), '_' );
				break;

			case 'kebab':
				$text = ltrim( preg_replace( '/[_\-]+/', '-', strtolower( preg_replace( '/[A-Z]/', '-\0', $text ) ) ), '-' );
				break;

			case 'pascal':
				$text = strtr( ucwords( strtr( $text, [ '_' => ' ', '-' => ' ' ] ) ), [ ' ' => '' ] );
				break;

			case 'camel':
				$text = lcfirst( strtr( ucwords( strtr( $text, [ '_' => ' ', '-' => ' ' ] ) ), [ ' ' => '' ] ) );
				break;
		}

		return $text;
	}
}