<?php

namespace Iwf2b\Html;

/**
 * Class HtmlNodeCollection
 * @package Iwf2b\Html
 */
class HtmlNodeCollection implements HtmlNodeCollectionInterface {
	/**
	 * Html nodes
	 *
	 * @var HtmlNodeInterface[]
	 */
	protected $nodes = [];

	/**
	 * @param HtmlNodeInterface $node
	 *
	 * @return HtmlNodeCollectionInterface
	 */
	public function add( HtmlNodeInterface $node ) {
		$this->nodes[] = $node;

		return $this;
	}

	/**
	 * @return HtmlNodeCollectionInterface
	 */
	public function clear() {
		$this->nodes = [];

		return $this;
	}

	/**
	 * @return string
	 */
	public function render() {
		$html = '';

		foreach ( $this->nodes as $node ) {
			$html .= $node->render();
		}

		return $html;
	}

	/**
	 * @return \ArrayIterator|HtmlNodeInterface[]
	 */
	public function getIterator() {
		return new \ArrayIterator( $this->nodes );
	}

	/**
	 * @param string $index
	 *
	 * @return HtmlNodeInterface|null
	 */
	public function offsetGet( $index ) {
		return isset( $this->nodes[ $index ] ) ? $this->nodes[ $index ] : null;
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
	 * @param HtmlNodeInterface $value
	 */
	public function offsetSet( $index, $value ) {
		if ( ! $value instanceof HtmlNodeInterface ) {
			throw new \InvalidArgumentException( 'The value must be an Object and that implements the "HtmlNodeInterface"' );
		}

		if ( $index ) {
			$this->nodes[] = $value;

		} else {
			$this->nodes[ $index ] = $value;
		}
	}

	/**
	 * @param string $index
	 */
	public function offsetUnset( $index ) {
		unset( $this->nodes[ $index ] );
	}

	/**
	 * @return int
	 */
	public function count() {
		return count( $this->nodes );
	}
}
