<?php

namespace Iwf2b\Html;

use Iwf2b\Arr;
/**
 * Class Html
 * @package Iwf2b\Html
 */
class Html {
	/**
	 * Tag stack
	 *
	 * @var array
	 */
	protected $tag_stack = [];

	/**
	 * HtmlNodeCollection instance
	 *
	 * @var HtmlNodeCollection
	 */
	protected $node_collection;

	/**
	 * Html constructor.
	 *
	 * @param HtmlNodeCollectionInterface|null $node_collection
	 */
	public function __construct( HtmlNodeCollectionInterface $node_collection = null ) {
		$this->node_collection = $node_collection ?: new HtmlNodeCollection();
	}

	/**
	 * @param $method
	 * @param $args
	 *
	 * @return $this
	 */
	public function __call( $method, $args ) {
		if ( preg_match( '/^(open|close)_([a-zA-Z_]+)$/', $method, $matches ) ) {
			$this->{$matches[1]}( $matches[2], $args );

		} else {
			$attributes = ! empty( $args ) ? (array) array_shift( $args ) : [];
			$this->open( $method, $attributes );
		}

		return $this;
	}

	/**
	 * Delegate to __call() method
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		return $this->{$name}();
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * @param string $tag
	 * @param array $attrs
	 *
	 * @return $this
	 */
	public function open( $tag, array $attrs = [] ) {
		$tag     = strtolower( $tag );
		$element = new HtmlTag( $tag, $attrs );

		if ( ! $element->is_standalone() ) {
			$this->tag_stack[] = $tag;
		}

		$this->node_collection->add( $element );

		return $this;
	}

	/**
	 * @return $this
	 */
	public function close() {
		if ( ! empty( $this->tag_stack ) ) {
			$current_tag = array_pop( $this->tag_stack );

			$this->node_collection->add( new HtmlTagClose( $current_tag ) );
		}

		return $this;
	}

	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function value( $value, $escape = true ) {
		$this->node_collection->add( new HtmlTagValue( $value, $escape ) );

		return $this;
	}

	/**
	 * @param $callback
	 *
	 * @return $this
	 */
	public function callback( callable $callback ) {
		$args = func_get_args();
		$args = array_splice( $args, 1 );

		$result = call_user_func_array( $callback, $args );

		if ( is_scalar( $result ) ) {
			$this->value( (string) $result );
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function all_close() {
		while ( $this->tag_stack ) {
			$this->close();
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clear() {
		$this->node_collection->clear();
		$this->tag_stack = [];

		return $this;
	}

	/**
	 * @return string
	 */
	public function render() {
		$this->all_close();

		$html = $this->node_collection->render();

		$this->clear();

		return $html;
	}

	/**
	 * @param string $name
	 * @param array $attrs
	 * @param string $value
	 * @param array $args
	 *
	 * @return string
	 */
	public static function tag( $name, array $attrs = [], $value = null, array $args = [] ) {
		$args = Arr::merge_intersect_key( [
			'escape'       => true,
			'escape_attrs' => true,
		], $args );

		$tag = new HtmlTag( $name, $attrs, $args['escape_attrs'] );

		if ( $tag->is_standalone() ) {
			$html = $tag->render();

		} else {
			$close_tag = new HtmlTagClose( $name );

			$html = $tag->render();
			$html .= $args['escape'] ? esc_html( $value ) : $value;
			$html .= $close_tag->render();
		}

		return $html;
	}
}
