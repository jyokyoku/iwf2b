<?php

namespace Iwf2b\Field\Rule;

/**
 * Interface RuleInterface
 * @package Iwf2b\Field\Rule
 */
interface RuleInterface {
	/**
	 * @param mixed $value
	 */
	public function set_value( $value );

	/**
	 * @return bool
	 */
	public function validate();

	/**
	 * @return string
	 */
	public function get_message();
}
