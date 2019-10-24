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
	protected static $post_type = 'page';

	/**
	 * {@inheritdoc}
	 */
	protected static $builtin = true;
}