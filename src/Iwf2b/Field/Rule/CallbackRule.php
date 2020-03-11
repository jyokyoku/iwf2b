<?php

namespace Iwf2b\Field\Rule;

/**
 * Class CallbackRule
 * @package Iwf2b\Field\Rule
 */
class CallbackRule implements RuleInterface {
	use RuleTrait;

	/**
	 * Callback
	 *
	 * @var callable
	 */
	public $callback;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		return (bool) call_user_func( $this->callback, $this->value );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'callback';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'callback' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'callback' => 'callable' ];
	}
}
