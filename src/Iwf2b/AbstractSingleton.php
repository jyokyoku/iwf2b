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
	protected $auto_init = true;

	/**
	 * Dependency class names
	 *
	 * @var array
	 */
	protected $dependencies = [];

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
		return static::get_instance()->auto_init;
	}

	/**
	 * @return array
	 */
	final public static function get_dependencies() {
		return static::get_instance()->dependencies;
	}

	/**
	 * Initialize
	 */
	abstract protected function initialize();

	/**
	 * Defined static getter and setter
	 *
	 * Usage:
	 * __CLASS__::get_{property}()
	 * __CLASS__::get_{property}( $value )
	 *
	 * @param $method
	 * @param $args
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public static function __callStatic( $method, $args ) {
		if ( preg_match( '/^([gs]et)_(.*)$/', $method, $match ) ) {
			try {
				$reflector = new \ReflectionClass( get_called_class() );

			} catch ( \ReflectionException $exception ) {
				throw $exception;
			}

			$property = $match[2];

			if ( $reflector->hasProperty( $property ) ) {
				$property = $reflector->getProperty( $property );
				$property->setAccessible( true );

				switch ( $match[1] ) {
					case 'get':
						return $property->getValue();

					case 'set':
						$property->setValue( $args[0] );
				}

			} else {
				throw new \InvalidArgumentException( "Property {$property} doesn't exist" );
			}
		}
	}
}
