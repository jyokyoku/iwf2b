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
	protected static $taxonomy = 'post_tag';

	/**
	 * {@inheritdoc}
	 */
	protected static $builtin = true;
}