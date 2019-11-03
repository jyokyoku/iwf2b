<?php

namespace Iwf2b;

/**
 * Class Filesystem
 * @package Iwf2b
 */
class Filesystem {
	/**
	 * @param string $directory
	 * @param int $mode
	 *
	 * @return bool
	 */
	public static function mkdir( $directory, $mode = 0777 ) {
		if ( ! is_dir( $directory ) ) {
			if ( ! @mkdir( $directory, $mode, true ) && ! is_dir( $directory ) ) {
				throw new \RuntimeException( sprintf( 'Unable to create the "%s" directory', $directory ) );
			}
		}

		return true;
	}

	/**
	 * @param string $path
	 * @param string $base_directory
	 *
	 * @return bool
	 */
	public static function is_child_path( $path, $base_directory ) {
		$base_directory = trailingslashit( $base_directory );

		if ( strpos( $path, $base_directory ) === 0 ) {
			return true;
		}

		if ( strpos( $path, '/' ) !== 0 && file_exists( $base_directory . $path ) ) {
			return true;
		}

		return false;
	}
}