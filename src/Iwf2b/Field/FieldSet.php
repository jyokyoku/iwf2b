<?php

namespace Iwf2b\Field;

/**
 * Class FieldSet
 * @package Iwf2b\Field
 */
class FieldSet implements \ArrayAccess, \IteratorAggregate, \Countable {
	/**
	 * FieldInterface instances
	 *
	 * @var FieldInterface[]
	 */
	protected $fields = [];

	/**
	 * Validated status
	 *
	 * @var bool
	 */
	protected $is_valid;

	/**
	 * Instances
	 *
	 * @var array
	 */
	private static $instances = [];

	/**
	 * Validation constructor.
	 */
	final private function __construct() {
	}

	/**
	 * @param string $name
	 *
	 * @return FieldSet
	 */
	public static function get_instance( $name ) {
		if ( empty( static::$instances[ $name ] ) ) {
			static::$instances[ $name ] = new static();
		}

		return static::$instances[ $name ];
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function exists( $name ) {
		return isset( static::$instances[ $name ] );
	}

	/**
	 * @param $name
	 */
	public static function destroy( $name ) {
		if ( $name instanceof FieldSet ) {
			$instance = $name;
			$name     = array_search( $name, static::$instances );

			if ( $name === false ) {
				throw new \InvalidArgumentException( "Specified instance does not exists in the instance list." );
			}

		} else if ( isset( static::$instances[ $name ] ) ) {
			$instance = static::$instances[ $name ];

		} else {
			throw new \InvalidArgumentException( "Specified '{$name}' does not exists in the instance list." );
		}

		foreach ( $instance as $i => $field ) {
			unset( $instance[ $i ] );
		}

		unset( static::$instances[ $name ] );
	}

	/**
	 * @param FieldInterface $field
	 *
	 * @return FieldInterface
	 */
	public function add_field( FieldInterface $field ) {
		$this->fields[ $field->get_name() ] = $field;

		return $field;
	}

	/**
	 * @param array $values
	 */
	public function set_values( array $values ) {
		foreach ( $values as $field_name => $value ) {
			if ( isset( $this->fields[ $field_name ] ) ) {
				$this->fields[ $field_name ]->set_value( $value );
			}
		}
	}

	/**
	 * @return array
	 */
	public function get_values() {
		$values = [];

		foreach ( $this->fields as $field_name => $field ) {
			$values[ $field_name ] = $field->get_value();
		}

		return $values;
	}

	/**
	 * @return array
	 */
	public function get_errors() {
		$errors = [];

		foreach ( $this->fields as $field ) {
			$errors[ $field->get_name() ] = $field->get_errors();
		}

		return array_filter( $errors );
	}

	/**
	 * @param bool $break_on_first_error
	 *
	 * @return bool
	 */
	public function validate( $break_on_first_error = true ) {
		$this->is_valid = true;

		foreach ( $this->fields as $field_name => $field ) {
			if ( $field->validate( $break_on_first_error ) === false ) {
				$this->is_valid = false;
			}
		}

		return $this->is_valid;
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		if ( $this->is_valid === null ) {
			$this->validate();
		}

		return $this->is_valid;
	}

	/**
	 * @return \ArrayIterator|FieldInterface[]
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->fields );
	}

	/**
	 * @param string $index
	 *
	 * @return FieldInterface|null
	 */
	public function offsetGet( $index ) {
		return isset( $this->fields[ $index ] ) ? $this->fields[ $index ] : null;
	}

	/**
	 * @param string $index
	 *
	 * @return bool
	 */
	public function offsetExists( $index ) {
		return isset( $this->fields[ $index ] );
	}

	/**
	 * @param string $index
	 * @param FieldInterface $value
	 */
	public function offsetSet( $index, $value ) {
		if ( ! $value instanceof FieldInterface ) {
			throw new \InvalidArgumentException( 'The value must be an Object and that implements the "FieldInterface"' );
		}

		if ( $index != $value->get_name() ) {
			throw new \InvalidArgumentException( 'The index must be same the returns of get_name() method in "FieldInterface"' );
		}

		$this->fields[ $index ] = $value;
	}

	/**
	 * @param string $index
	 */
	public function offsetUnset( $index ) {
		unset( $this->fields[ $index ] );
	}

	/**
	 * @return int
	 */
	public function count() {
		return count( $this->fields );
	}
}
