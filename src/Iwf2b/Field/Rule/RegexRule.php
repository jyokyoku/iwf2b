<?php

namespace Iwf2b\Field\Rule;

/**
 * Class RegexRule
 * @package Iwf2b\Field\Rule
 */
class RegexRule extends AbstractRule {
	/**
	 * Regex pattern
	 *
	 * @var string
	 */
	public $pattern;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validate() {
		$result = @preg_match( $this->pattern, $this->value );

		if ( preg_last_error() !== PREG_NO_ERROR ) {
			throw new \RuntimeException( sprintf( 'The error "%s" in regex pattern.', $this->get_error_name( preg_last_error() ) ) );
		}

		return $result === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_error_name( $error_code ) {
		return array_flip( get_defined_constants( true )['pcre'] )[ $error_code ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'pattern';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'pattern' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'pattern' => 'string' ];
	}
}
