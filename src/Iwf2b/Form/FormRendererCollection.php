<?php

namespace Iwf2b\Form;

/**
 * Class FormRendererCollection
 * @package Iwf2b\Form
 */
class FormRendererCollection implements FormRendererInterface, \ArrayAccess, \IteratorAggregate, \Countable {
	/**
	 * Callback of before render
	 *
	 * @var callable
	 */
	protected $before_render;

	/**
	 * Callback of after render
	 *
	 * @var callable
	 */
	protected $after_render;

	/**
	 * Value attribute
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Other tag attributes
	 *
	 * @var array
	 */
	protected $attrs = [];

	/**
	 * FormRendererInterface instances
	 *
	 * @var FormRendererInterface[]
	 */
	protected $forms = [];

	/**
	 * {@inheritdoc}
	 */
	public function set_value( $value ) {
		$this->value = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_attrs( array $attrs ) {
		$this->attrs = $attrs;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_content( $content ) {
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_before_render( $before_render ) {
		if ( $before_render && ! is_callable( $before_render ) ) {
			throw new \InvalidArgumentException( 'The before render is must be callable or empty.' );
		}

		$this->before_render = $before_render;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_after_render( $after_render ) {
		if ( $after_render && ! is_callable( $after_render ) ) {
			throw new \InvalidArgumentException( 'The after render is must be callable or empty.' );
		}

		$this->after_render = $after_render;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_attrs() {
		return $this->attrs;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_content() {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_before_render() {
		return $this->before_render;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_after_render() {
		return $this->after_render;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( callable $callback = null ) {
		if ( $this->before_render ) {
			call_user_func( $this->before_render, $this );
		}

		$html = $callback ? call_user_func( $callback, $this ) : implode( "\n", $this->forms );

		if ( $this->after_render ) {
			call_user_func_array( $this->after_render, [ &$html, $this ] );
		}

		return (string) $html;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * @return \ArrayIterator|FormRendererInterface[]
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->forms );
	}

	/**
	 * @param string $index
	 *
	 * @return FormRendererInterface|null
	 */
	public function offsetGet( $index ) {
		return isset( $this->forms[ $index ] ) ? $this->forms[ $index ] : null;
	}

	/**
	 * @param string $index
	 *
	 * @return bool
	 */
	public function offsetExists( $index ) {
		return isset( $this->forms[ $index ] );
	}

	/**
	 * @param string $index
	 * @param FormRendererInterface $value
	 */
	public function offsetSet( $index, $value ) {
		if ( ! $value instanceof FormRendererInterface ) {
			throw new \InvalidArgumentException( 'The value must be an Object and that implements the "FormRendererInterface"' );
		}

		if ( $index ) {
			$this->forms[ $index ] = $value;

		} else {
			$this->forms[] = $value;
		}
	}

	/**
	 * @param string $index
	 */
	public function offsetUnset( $index ) {
		unset( $this->forms[ $index ] );
	}

	/**
	 * @return int
	 */
	public function count() {
		return count( $this->forms );
	}
}
