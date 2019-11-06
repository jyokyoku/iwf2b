<?php

namespace Iwf2b\Field\Rule\Exception;

/**
 * Class MissingRequiredParamsException
 * @package Iwf2b\Field\Rule\Exception
 */
class MissingRequiredParamsException extends \RuntimeException {
	/**
	 * Error params
	 */
	private $params;

	/**
	 * @param $params
	 *
	 * @return $this
	 */
	public function set_params( $params ) {
		$this->params = $params;

		return $this;
	}

	/**
	 * @param $params
	 *
	 * @return mixed
	 */
	public function get_params( $params ) {
		return $this->params;
	}
}
