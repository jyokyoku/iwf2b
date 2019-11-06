<?php

namespace Iwf2b\Field\Rule;

/**
 * Class UniqueEmailRule
 * @package Iwf2b\Field\Rule
 */
class UniqueEmailRule extends AbstractRule {
	/**
	 * @var array
	 */
	protected $exclude = [];

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $config = null ) {
		parent::__construct( $config );

		if ( is_string( $this->exclude ) ) {
			$this->exclude = wp_parse_list( $this->exclude );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function do_validate() {
		if ( ! empty( $this->exclude ) && in_array( $this->value, $this->exclude ) ) {
			return true;
		}

		return ! email_exists( $this->value );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This email address is already registered.', 'iwf2b' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'exclude';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'exclude' => [ 'string', 'array' ] ];
	}
}