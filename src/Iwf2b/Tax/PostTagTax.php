<?php

namespace Iwf2b\Tax;

/**
 * Class PostTagTax
 * @package Iwf2b\Tax
 */
class PostTagTax extends AbstractTax {
	/**
	 * {@inheritdoc}
	 */
	protected $taxonomy = 'post_tag';

	/**
	 * {@inheritdoc}
	 */
	protected $builtin = true;
}
