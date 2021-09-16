<?php

namespace Iwf2b\Post;

/**
 * Class PagePost
 * @package Iwf2b\Post
 */
class PagePost extends AbstractPost {
	/**
	 * {@inheritdoc}
	 */
	protected $post_type = 'page';

	/**
	 * {@inheritdoc}
	 */
	protected $builtin = true;
}
