<?php

namespace Iwf2b;

/**
 * Class App
 * @package Iwf2b
 */
class App {
	/**
	 * Instance
	 *
	 * @var App
	 */
	protected static $instance;

	/**
	 * @return App
	 */
	public static function get_instance() {
		if ( ! static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * App constructor.
	 */
	protected function __construct() {
		load_textdomain( 'iwf2b', __DIR__ . '/Lang/' . get_locale() . '.mo' );

		$this->bootstrap( __DIR__, __NAMESPACE__ );
	}

	/**
	 * @param array|string $base_dir
	 * @param string $base_namespace
	 */
	public function bootstrap( $base_directory, $base_namespace = null ) {
		if ( is_array( $base_directory ) && $base_namespace === null ) {
			$class_maps = $base_directory;

		} else if ( is_string( $base_directory ) && ! empty( $base_namespace ) ) {
			$class_maps = [ $base_directory => $base_namespace ];

		} else {
			throw new \InvalidArgumentException( sprintf( "The \$base_directroy must be an array or string and set the \$base_namespace if \$base_directory is a string." ) );
		}

		foreach ( $class_maps as $_base_directory => $_base_namespace ) {
			$this->load_recursive( $_base_directory, $_base_namespace );
		}
	}

	/**
	 * @param string $directory
	 * @param string $namespace
	 */
	private function load_recursive( $directory, $namespace ) {
		$directory = untrailingslashit( $directory );
		$namespace = rtrim( $namespace, '\\' );

		$files = new \RegexIterator(
			new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator(
					$directory,
					\FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS
				)
			),
			'/\.php$/i',
			\RegexIterator::MATCH
		);

		foreach ( $files as $file ) {
			if ( $file->isDir() ) {
				continue;
			}

			$relative_directory = str_replace( $directory, '', $file->getPath() );
			$class              = substr( $file->getFilename(), 0, - 4 );
			$namespace_class    = $namespace . str_replace( '/', '\\', rtrim( $relative_directory, '/' ) ) . '\\' . $class;

			try {
				$ref = new \ReflectionClass( $namespace_class );

			} catch ( \ReflectionException $e ) {
				continue;
			}

			if ( $ref->isAbstract() || $ref->isInterface() || $ref->isTrait() ) {
				continue;
			}

			if ( $ref->isSubclassOf( __NAMESPACE__ . '\AbstractSingleton' ) ) {
				if ( $namespace_class::auto_init() ) {
					$namespace_class::get_instance();
				}
			}
		}
	}
}
