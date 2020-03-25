<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Field\Data\FileData;
use Iwf2b\Field\Rule\Exception\MissingRequiredParamsException;
use Iwf2b\Text;
use Iwf2b\Util;

/**
 * Trait RuleTrait
 * @package Iwf2b\Field\
 *
 * Based on Symfony
 * Copyright (c) 2004-2019 Fabien Potencier (https://symfony.com)
 */
trait RuleTrait {
	/**
	 * Error message
	 *
	 * @var string
	 */
	protected $message = '';

	/**
	 * Validate value
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * RuleTrait constructor.
	 *
	 * @param mixed $config
	 */
	public function __construct( $config = null ) {
		if ( $config !== null && ! is_array( $config ) && $this->get_default_param() ) {
			$config = [ $this->get_default_param() => $config ];
		}

		$var_names       = array_keys( get_object_vars( $this ) );
		$required_params = array_flip( $this->get_required_params() );
		$param_types     = $this->get_param_types();

		if ( $config ) {
			foreach ( $config as $key => $value ) {
				if ( in_array( $key, $var_names ) ) {
					if ( array_key_exists( $key, $param_types ) ) {
						$types      = (array) $param_types[ $key ];
						$valid_type = false;

						foreach ( $types as $type ) {
							if ( ( function_exists( 'is_' . $type ) && call_user_func( 'is_' . $type, $value ) )
							     || ( function_exists( 'ctype_' . $type ) && call_user_func( 'ctype_' . $type, $value ) )
							     || $value instanceof $type
							) {
								$valid_type = true;
								break;
							}
						}

						if ( ! $valid_type ) {
							throw new \InvalidArgumentException( sprintf( 'The param "%s" must be "%s" type', $key, implode( '" or "', $types ) ) );
						}
					}

					$this->{$key} = $value;
					unset( $required_params[ $key ] );
				}
			}
		}

		if ( count( $required_params ) > 0 ) {
			throw ( new MissingRequiredParamsException( sprintf( 'The params "%s" must be set for %s', implode( '", "', array_keys( $required_params ) ), get_class( $this ) ) ) )
				->set_params( $required_params );
		}
	}

	/**
	 * @return bool
	 */
	final public function validate() {
		if ( $this->is_empty( $this->value ) && $this->through_if_empty() ) {
			return true;
		}

		return $this->do_validation();
	}

	/**
	 * @param mixed $value
	 */
	public function set_value( $value ) {
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function get_message() {
		$message  = $this->message ?: $this->get_default_message();
		$replaces = get_object_vars( $this );

		return Text::replace( $message, $replaces, '%' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function through_if_empty() {
		return true;
	}

	/**
	 * @return bool
	 */
	abstract protected function do_validation();

	/**
	 * @return string
	 */
	protected function get_default_message() {
		return preg_replace( '|^(?:.+\\\)?(.+?)Rule$|i', '$1', get_class( $this ) );
	}

	/**
	 * @return string
	 */
	protected function get_default_param() {
		return '';
	}

	/**
	 * @return array
	 */
	protected function get_required_params() {
		return [];
	}

	/**
	 * @return array
	 */
	protected function get_param_types() {
		return [];
	}

	/**
	 * @param mixed $value
	 *
	 * @return bool
	 */
	protected function is_empty( $value ) {
		if ( Util::is_empty( $value, true ) ) {
			return true;
		}

		if ( $value instanceof FileData ) {
			return $value->get_path() == '';
		}

		return false;
	}
}
