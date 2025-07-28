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
	public function getIterator(): \Traversable {
		return new \ArrayIterator( $this->nodes );
	}

	/**
	 * @param string $index
	 *
	 * @return HtmlNodeInterface|null
	 */
	public function offsetGet( $index ): mixed {
		return isset( $this->nodes[ $index ] ) ? $this->nodes[ $index ] : null;
	}

	/**
	 * @param string $index
	 *
	 * @return bool
	 */
	public function offsetExists( $index ): bool {
		return isset( $this->nodes[ $index ] );
	}

	/**
	 * @param string $index
	 * @param HtmlNodeInterface $value
	 */
	public function offsetSet( $index, $value ): void {
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
	public function offsetUnset( $index ): void {
		unset( $this->nodes[ $index ] );
	}

	/**
	 * @return int
	 */
	public function count(): int {
		return count( $this->nodes );
	}
}
