<?php

namespace Iwf2b\Html;

/**
 * Class HtmlTagValue
 * @package Iwf2b\Html
 */
class HtmlTagValue implements HtmlNodeInterface {
	/**
	 * Value
	 *
	 * @var string
	 */
	protected $value;

	/**
	 * Escape value
	 *
	 * @var bool
	 */
	protected $escape = true;

	/**
	 * HtmlValue constructor.
	 *
	 * @param $value
	 */
	public function __construct( $value, $escape = true ) {
		$this->value  = $value;
		$this->escape = $escape;
	}

	/**
	 * @return string
	 */
	public function render() {
		return $this->escape ? esc_html( $this->value ) : $this->value;
	}
}
