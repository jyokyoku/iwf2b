<?php


namespace Iwf2b;


class ViewHelper {
	/**
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
}