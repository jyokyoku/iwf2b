<?php

namespace Iwf2b\Field\Rule;

/**
 * Class RequiredRule
 * @package Iwf2b\Field\Rule
 */
class RequiredRule extends AbstractRule {
	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		return ! $this->is_empty( $this->value );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function through_if_empty() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is required.', 'iwf2b' );
	}
}
