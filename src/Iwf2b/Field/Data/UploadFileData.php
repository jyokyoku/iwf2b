<?php

namespace Iwf2b\Field\Data;

use Iwf2b\Field\Data\Exception\FileDataException;

class UploadFileData extends FileData {
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $mime_type;

	/**
	 * @var bool
	 */
	protected $is_uploaded = false;

	/**
	 * @var int
	 */
	protected $error;

	public function __construct( $path, $name = null, $mime_type = null, $error = null ) {
		parent::__construct( $path );

		if ( empty( $name ) ) {
			$name = $this->get_basename();
		}

		$this->name      = $name;
		$this->mime_type = $mime_type ?: 'application/octet-stream';
		$this->error     = $error ?: UPLOAD_ERR_OK;
	}

	/**
	 * @return string
	 */
	public function get_client_name() {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function get_client_mime_type() {
		return $this->mime_type;
	}

	/**
	 * @return string
	 */
	public function get_client_extension() {
		return strtolower( pathinfo( $this->get_client_name(), PATHINFO_EXTENSION ) );
	}

	/**
	 * @return int
	 */
	public function get_error() {
		return $this->error;
	}

	/**
	 * @return string
	 */
	public function get_error_message() {
		/**
		 * Based on Symfony
		 * Copyright (c) 2004-2019 Fabien Potencier (https://symfony.com)
		 */
		$errors = [
			UPLOAD_ERR_INI_SIZE   => __( 'The file "%s" exceeds your upload_max_filesize ini directive.', 'iwf2b' ),
			UPLOAD_ERR_FORM_SIZE  => __( 'The file "%s" exceeds the upload limit defined in your form.', 'iwf2b' ),
			UPLOAD_ERR_PARTIAL    => __( 'The file "%s" was only partially uploaded.', 'iwf2b' ),
			UPLOAD_ERR_NO_FILE    => __( 'No file was uploaded.', 'iwf2b' ),
			UPLOAD_ERR_CANT_WRITE => __( 'The file "%s" could not be written on disk.', 'iwf2b' ),
			UPLOAD_ERR_NO_TMP_DIR => __( 'File could not be uploaded: missing temporary directory.', 'iwf2b' ),
			UPLOAD_ERR_EXTENSION  => __( 'File upload was stopped by a PHP extension.', 'iwf2b' ),
		];

		return isset( $errors[ $this->error ] ) ? $errors[ $this->error ] : __( 'The file "%s" was not uploaded due to an unknown error.', 'iwf2b' );
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return $this->error === UPLOAD_ERR_OK && is_uploaded_file( $this->get_path() );
	}

	/**
	 * @return bool
	 */
	public function is_uploaded() {
		return $this->is_uploaded;
	}

	/**
	 * @return $this
	 */
	public function upload() {
		return $this->upload_as( $this->get_client_name() );
	}

	/**
	 * @param string $file_name
	 *
	 * @return $this
	 */
	public function upload_as( $file_name ) {
		if ( $this->is_uploaded() ) {
			throw new FileDataException( sprintf( 'The file "%s" has already been uploaded.', $this->get_path() ) );
		}

		if ( ! $this->is_valid() ) {
			throw new FileDataException( sprintf( 'The file "%s" is not a valid uploaded file.', $this->get_path() ) );
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$file = [
			'tmp_name' => $this->get_path(),
			'name'     => $file_name,
			'size'     => $this->get_size(),
			'type'     => $this->get_client_mime_type(),
			'error'    => $this->get_error(),
		];

		$result = wp_handle_upload( $file, [
			'test_form' => false,
			'test_type' => false,
			'test_size' => false,
		] );

		if ( ! empty( $result['error'] ) ) {
			throw new FileDataException( $result['error'] );
		}

		$this->is_uploaded = true;
		$this->path        = $result['file'];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function move( $directory, $name = '', array $args = [] ) {
		if ( ! $this->is_uploaded() ) {
			throw new FileDataException( sprintf( 'The file "%s" has not been uploaded.', $this->get_path() ) );
		}

		parent::move( $directory, $name, $args );

		return $this;
	}
}