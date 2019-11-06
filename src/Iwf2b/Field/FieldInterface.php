<?php

namespace Iwf2b\Field;

use Iwf2b\Field\Rule\RuleInterface;

/**
 * Interface FieldInterface
 * @package Iwf2b\Field
 */
interface FieldInterface {
	/**
	 * FieldInterface constructor.
	 *
	 * @param string $name
	 * @param string $label
	 */
	public function __construct( $name, $label );

	/**
	 * @return string
	 */
	public function get_name();

	/**
	 * @return string
	 */
	public function get_label();

	/**
	 * @param bool $escape
	 *
	 * @return mixed
	 */
	public function get_value( $escape = false );

	/**
	 * @return array
	 */
	public function get_choices();

	/**
	 * @return array
	 */
	public function get_errors();

	/**
	 * @param RuleInterface $rule
	 *
	 * @return $this
	 */
	public function add_rule( RuleInterface $rule );

	/**
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function set_value( $value );

	/**
	 * @param array|string $choices
	 *
	 * @return $this
	 */
	public function set_choices( $choices );

	/**
	 * @param string $message
	 *
	 * @return $this
	 */
	public function set_error( $message );

	/**
	 * @return $this
	 */
	public function clear_rules();

	/**
	 * @param bool $break_on_first_error
	 *
	 * @return bool
	 */
	public function validate( $break_on_first_error = false );

	/**
	 * @return bool
	 */
	public function is_valid();

	/**
	 * @return string
	 */
	public function __toString();
}
