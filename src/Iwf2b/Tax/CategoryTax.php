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
	protected $taxonomy = 'category';

	/**
	 * {@inheritdoc}
	 */
	protected $builtin = true;
}
