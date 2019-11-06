<?php

namespace Iwf2b\Form;

/**
 * Interface FormRendererInterface
 * @package Iwf2b\Form
 */
interface FormRendererInterface {
	/**
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function set_value( $value );

	/**
	 * @param array $attrs
	 *
	 * @return $this
	 */
	public function set_attrs( array $attrs );

	/**
	 * @param string $content
	 *
	 * @return $this
	 */
	public function set_content( $content );

	/**
	 * @param callable $before_render
	 *
	 * @return $this
	 */
	public function set_before_render( $before_render );

	/**
	 * @param callable $after_render
	 *
	 * @return $html
	 */
	public function set_after_render( $after_render );

	/**
	 * @return mixed
	 */
	public function get_value();

	/**
	 * @return array
	 */
	public function get_attrs();

	/**
	 * @return string
	 */
	public function get_content();

	/**
	 * @return callable
	 */
	public function get_before_render();

	/**
	 * @return callable
	 */
	public function get_after_render();

	/**
	 * @param callable|null $callback
	 *
	 * @return string
	 */
	public function render( callable $callback = null );

	/**
	 * @return string
	 */
	public function __toString();
}
