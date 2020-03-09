<?php

namespace Iwf2b\Field\Rule;

/**
 * Class UrlRule
 * @package Iwf2b\Field\Rule
 */
class UrlRule extends AbstractRule {
	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		return false !== filter_var( $this->value, FILTER_VALIDATE_URL ) && preg_match( '@^https?+://@i', $this->value );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is not a valid URL.', 'iwf2b' );
	}
}
