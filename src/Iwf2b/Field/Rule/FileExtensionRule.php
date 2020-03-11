<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Arr;
use Iwf2b\Field\Data\FileData;
use Iwf2b\Field\Data\UploadFileData;

class FileExtensionRule implements RuleInterface {
	use RuleTrait {
		RuleTrait::__construct as rule_construct;
	}

	/**
	 * @var array
	 */
	protected $extension = [];

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $config = null ) {
		$this->rule_construct( $config );

		if ( is_string( $this->extension ) ) {
			$this->extension = wp_parse_list( $this->extension );
		}

		Arr::apply( $this->extension, 'strtolower' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function do_validation() {
		if ( $this->value instanceof UploadFileData ) {
			return in_array( $this->value->get_client_extension(), $this->extension );
		}

		if ( $this->value instanceof FileData ) {
			return in_array( $this->value->get_extension(), $this->extension );
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This file type should be in [ %extension% ].', 'iwf2b' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'extension';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'extension' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'extension' => [ 'array', 'string' ] ];
	}
}