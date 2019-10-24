<?php

namespace Iwf2b\Post;

/**
 * Class Post
 * @package Iwf2b\Post
 */
class Post extends AbstractPost {
	/**
	 * {@inheritdoc}
	 */
	protected static $post_type = 'post';

	/**
	 * {@inheritdoc}
	 */
	protected static $builtin = true;
}