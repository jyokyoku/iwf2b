<?php

namespace Iwf2b\Html;

use Iwf2b\Arr;

/**
 * Class HtmlTag
 * @package Iwf2b\Html
 */
class HtmlTag implements HtmlNodeInterface {
	/**
	 * Tag template
	 *
	 * @var string
	 */
	protected static $format = '<%s%s>';

	/**
	 * Attribute template
	 *
	 * @var string
	 */
	protected static $attr_format = '%s="%s"';

	/**
	 * Standalone tags
	 *
	 * @var array
	 */
	protected static $standalone_tags = [
		'area',
		'base',
		'br',
		'col',
		'hr',
		'img',
		'input',
		'link',
		'meta',
		'param',
	];

	/**
	 * Standalone attributes
	 *
	 * @var array
	 */
	protected static $standalone_attrs = [
		'compact',
		'checked',
		'declare',
		'readonly',
		'disabled',
		'selected',
		'defer',
		'ismap',
		'nohref',
		'noshade',
		'nowrap',
		'multiple',
		'noresize',
	];

	/**
	 * Tag name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Attributes
	 *
	 * @var array
	 */
	protected $attrs = [];

	/**
	 * Escape attributes
	 *
	 * @var bool
	 */
	protected $escape_attrs = true;

	/**
	 * HtmlTag constructor.
	 *
	 * @param string $name
	 * @param array $attrs
	 * @param bool $escape
	 */
	public function __construct( $name, array $attrs = [], $escape_attrs = true ) {
		$this->name         = $name;
		$this->attrs        = $attrs;
		$this->escape_attrs = $escape_attrs;
	}

	/**
	 * @return bool
	 */
	public function is_standalone() {
		return in_array( $this->name, static::$standalone_tags );
	}

	/**
	 * @return string
	 */
	public function render() {
		$attrs = static::parse_attrs( $this->attrs, $this->escape_attrs );

		if ( $attrs ) {
			$attrs = ' ' . $attrs;
		}

		return sprintf( static::$format, $this->name, $attrs );
	}

	/**
	 * @param array $attrs
	 * @param bool $escape
	 *
	 * @return string
	 */
	public static function parse_attrs( array $attrs = [], $escape = true ) {
		$formatted = [];

		foreach ( $attrs as $prop => $value ) {
			if ( is_array( $value ) ) {
				$value = Arr::implode( ' ', $value );
			}

			$value = trim( $value );

			if ( is_int( $prop ) ) {
				if ( empty( $value ) ) {
					continue;
				}

				$prop = $value;
			}

			if ( in_array( $prop, static::$standalone_attrs ) ) {
				if ( $value !== true && $value !== '1' && $value !== 1 && $value !== $prop ) {
					continue;
				}

				$formatted[] = $prop;

			} else {
				$formatted[] = sprintf( static::$attr_format, $prop, $escape ? esc_attr( $value ) : $value );
			}
		}

		return implode( ' ', $formatted );
	}
}
