<?php

namespace Iwf2b;

/**
 * Class AbstractSingleton
 * @package Iwf2b
 */
abstract class AbstractSingleton {
	/**
	 * Instances
	 *
	 * @var array AbstractSingleton[]
	 */
	private static $instances = [];

	/**
	 * Allow auto initialize
	 *
	 * @var bool
	 */
	protected static $auto_init = true;

	/**
	 * Dependency class names
	 *
	 * @var array
	 */
	protected static $dependencies = [];

	/**
	 * AbstractSingleton constructor.
	 */
	final private function __construct() {
		if ( ! isset( self::$instances[ get_called_class() ] ) ) {
			call_user_func_array( [ $this, 'initialize' ], func_get_args() );
		}
	}

	/**
	 * @return static
	 */
	final public static function get_instance() {
		$class = get_called_class();

		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new static();
		}

		return self::$instances[ $class ];
	}

	/**
	 * @return bool
	 */
	final public static function auto_init() {
		return static::$auto_init;
	}

	/**
	 * @return array
	 */
	final public static function get_dependencies() {
		return static::$dependencies;
	}

	/**
	 * Initialize
	 */
	abstract protected function initialize();
}
