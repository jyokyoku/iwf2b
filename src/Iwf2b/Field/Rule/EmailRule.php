<?php

namespace Iwf2b\Field\Rule;

/**
 * Class EmailRule
 * @package Iwf2b\Field\Rule
 */
class EmailRule implements RuleInterface {
	use RuleTrait;

	/**
	 * Check the email domains from DNS
	 */
	protected $check_dns = false;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		switch ( true ) {
			case filter_var( $this->value, FILTER_VALIDATE_EMAIL ) === false:
			case ! preg_match( '/@([^@\[]++)\z/', $this->value, $matches ):
				return false;

			case ! (bool) $this->check_dns:
			case checkdnsrr( $matches[1], 'MX' ):
			case checkdnsrr( $matches[1], 'A' ):
			case checkdnsrr( $matches[1], 'AAAA' ):
				return true;

			default:
				return false;
		}
	}

	protected function get_default_param() {
		return 'check_dns';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is not a valid email address.', 'iwf2b' );
	}
}
