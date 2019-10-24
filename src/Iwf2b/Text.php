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

		return $text;
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
}