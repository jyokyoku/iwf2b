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
	protected $post_type = 'post';

	/**
	 * {@inheritdoc}
	 */
	protected $builtin = true;
}
