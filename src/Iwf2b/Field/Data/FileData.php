<?php

namespace Iwf2b\Field\Data;

use Iwf2b\Arr;
use Iwf2b\Field\Data\Exception\FileDataException;
use Iwf2b\Filesystem;

class FileData {
	/**
	 * @var string
	 */
	protected $path;

	/**
	 * @var string
	 */
	private $base_dir;

	/**
	 * @var string
	 */
	private $base_url;

	/**
	 * FileData constructor.
	 *
	 * @param $path
	 */
	public function __construct( $path ) {
		$this->path = wp_normalize_path( $path );

		$this->base_url = WP_CONTENT_URL;
		$this->base_dir = WP_CONTENT_DIR;
	}

	/**
	 * @return string
	 */
	public function get_path() {
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function get_basename() {
		return pathinfo( $this->path, PATHINFO_BASENAME );
	}

	/**
	 * @return string
	 */
	public function get_base_dir() {
		return trailingslashit( $this->base_dir );
	}

	/**
	 * @return string
	 */
	public function get_base_url() {
		return trailingslashit( $this->base_url );
	}

	/**
	 * @return false|int
	 */
	public function get_size() {
		return $this->exists() ? filesize( $this->get_path() ) : false;
	}

	/**
	 * @return string
	 */
	public function get_url() {
		if ( $this->in_base_dir() ) {
			return str_replace( $this->get_base_dir(), $this->get_base_url(), $this->get_path() );
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function get_extension() {
		return strtolower( pathinfo( $this->get_path(), PATHINFO_EXTENSION ) );
	}

	/**
	 * @return bool
	 */
	public function exists() {
		return is_file( $this->get_path() );
	}

	/**
	 * @return bool
	 */
	public function in_base_dir() {
		return Filesystem::is_child_path( $this->get_path(), $this->get_base_dir() );
	}

	/**
	 * @param string $directory
	 * @param string $name
	 * @param array $args
	 *
	 * @return $this
	 */
	public function move( $directory, $name = '', array $args = [] ) {
		$args = Arr::merge_intersect_key( [
			'unique_file_name_callback' => null,
		], $args );

		if ( ! $this->exists() ) {
			throw new FileDataException( sprintf( 'The file "%s" does not exists.', $this->get_path() ) );
		}

		if ( path_is_absolute( $directory ) ) {
			$new_path = $directory;

		} else {
			$new_path = $this->get_base_dir() . $directory;
		}

		if ( is_file( $new_path ) ) {
			throw new FileDataException( sprintf( 'The directory "%s" exists as a file.', $directory ) );
		}

		if ( ! is_dir( $new_path ) ) {
			Filesystem::mkdir( $new_path );
		}

		$new_path = trailingslashit( realpath( $new_path ) );
		$new_path .= wp_unique_filename( $new_path, $name ?: $this->get_basename(), $args['unique_file_name_callback'] );

		set_error_handler( function ( $type, $msg ) use ( &$error ) {
			$error = $msg;
		} );
		$renamed = @rename( $this->get_path(), $new_path );
		restore_error_handler();

		if ( ! $renamed ) {
			throw new FileDataException( sprintf( 'Could not move the file "%s" to "%s" (%s)', $this->get_path(), $new_path, strip_tags( $error ) ) );
		}

		$this->path = $new_path;

		return $this;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->get_path();
	}
}