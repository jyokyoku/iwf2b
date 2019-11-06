<?php

namespace Iwf2b\Html;

/**
 * Interface HtmlNodeCollectionInterface
 * @package Iwf2b\Html
 */
interface HtmlNodeCollectionInterface extends \ArrayAccess, \IteratorAggregate, \Countable {
	/**
	 * @param HtmlNodeInterface $element
	 *
	 * @return $this
	 */
	public function add( HtmlNodeInterface $element );

	/**
	 * @return $this
	 */
	public function clear();

	/**
	 * @return string
	 */
	public function render();
}
