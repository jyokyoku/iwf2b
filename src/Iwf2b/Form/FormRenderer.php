<?php

namespace Iwf2b\Form;

use Iwf2b\Arr;
use Iwf2b\Html\Html;
use Iwf2b\Text;

/**
 * Class FormRenderer
 * @package Iwf2b\Form
 */
class FormRenderer implements FormRendererInterface {
	/**
	 * Tag name
	 *
	 * @var string
	 */
	protected $tag;

	/**
	 * Name attribute
	 *
	 * @var string
	 */
	protected $name;

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
	 * Tag content
	 *
	 * @var string
	 */
	protected $content;

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
	 * Form constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag, $name ) {
		$this->tag  = $tag;
		$this->name = $name;
	}

	/**
	 * @param $attrs
	 *
	 * @return $this
	 */
	public function set_attrs( array $attrs ) {
		ksort($attrs);
		$this->attrs = $attrs;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function set_content( $content ) {
		$this->content = $content;

		return $this;
	}

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
	public function set_before_render( $before_render ) {
		if ( $before_render && ! is_callable( $before_render ) ) {
			throw new \InvalidArgumentException( 'The before render is must be callable or null.' );
		}

		$this->before_render = $before_render;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_after_render( $after_render ) {
		if ( $after_render && ! is_callable( $after_render ) ) {
			throw new \InvalidArgumentException( 'The after render is must be callable or null.' );
		}

		$this->after_render = $after_render;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_tag() {
		return $this->tag;
	}

	/**
	 * @return mixed
	 */
	public function get_name() {
		return $this->name;
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
		return $this->content;
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
		$this->attrs['name'] = $this->name;

		if ( ! Arr::exists( $this->attrs, 'id' ) ) {
			$this->attrs['id'] = $this->generate_id();
		}

		if ( $this->before_render ) {
			call_user_func( $this->before_render, $this );
		}

		$html = $callback ? call_user_func( $callback, $this ) : Html::tag( $this->tag, $this->attrs, $this->content, [ 'escape' => false ] );

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
	 * @param $name
	 *
	 * @return string
	 */
	protected function generate_id() {
		return '_' . str_replace( [ '[', ']' ], '', Text::short_hash( $this->name . serialize( $this->attrs ) ) );
	}
}
