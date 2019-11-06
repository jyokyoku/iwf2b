<?php

namespace Iwf2b\Field\Rule;

/**
 * Class EmailRule
 * @package Iwf2b\Field\Rule
 */
class EmailRule extends AbstractRule {
	/**
	 * {@inheritdoc}
	 */
	protected function do_validate() {
		return false !== filter_var( $this->value, FILTER_VALIDATE_EMAIL ) && ']' !== substr( $this->value, - 1 );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is not a valid email address.', 'iwf2b' );
	}
}
