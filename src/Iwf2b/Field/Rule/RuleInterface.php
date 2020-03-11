<?php

namespace Iwf2b\Field\Rule;

/**
 * Interface RuleInterface
 * @package Iwf2b\Field\Rule
 */
interface RuleInterface {
	/**
	 * RuleInterface constructor.
	 *
	 * @param array|mixed $config
	 */
	public function __construct( $config = null );

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
