<?php

namespace Iwf2b\Tax;

class PostTagTax extends AbstractTax {
	protected static $taxonomy = 'post_tag';

	protected static $builtin = true;
}