<?php

namespace Iwf2b\Html;

/**
 * Class HtmlTagClose
 * @package Iwf2b\Html
 */
class HtmlTagClose implements HtmlNodeInterface {
	/**
	 * Tag template
	 *
	 * @var string
	 */
	protected static $format = '</%s>';

	/**
	 * Tag name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * HtmlTagClose constructor.
	 *
	 * @param $name
	 */
	public function __construct( $name ) {
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function render() {
		return sprintf( static::$format, $this->name );
	}
}
