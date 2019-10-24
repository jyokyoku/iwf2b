<?php

namespace Iwf2b\Tax;

/**
 * Class CategoryTax
 * @package Iwf2b\Tax
 */
class CategoryTax extends AbstractTax {
	/**
	 * {@inheritdoc}
	 */
	protected static $taxonomy = 'category';

	/**
	 * {@inheritdoc}
	 */
	protected static $builtin = true;
}