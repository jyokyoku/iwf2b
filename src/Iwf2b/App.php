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
	 * @param string $parent_directory
	 * @param string $namespace
	 */
	private function load_recursive( $parent_dir, $namespace ) {
		foreach ( scandir( $parent_dir ) as $file_name ) {
			if ( $file_name === '.' || $file_name === '..' ) {
				continue;
			}

			$file_path = $parent_dir . '/' . $file_name;

			if ( is_file( $file_path ) && strrpos( $file_name, '.php' ) !== false && ctype_upper( substr( $file_name, 0, 1 ) ) && $file_path !== __FILE__ ) {
				$class = substr( $file_name, 0, - 4 );

				if ( substr( $class, 0, 8 ) === 'Abstract' || substr( $class, - 9 ) === 'Interface' || substr( $class, - 6 ) === 'Trait' ) {
					continue;
				}

				$namespace_class = $namespace . '\\' . substr( $file_name, 0, - 4 );

				try {
					$ref = new \ReflectionClass( $namespace_class );

				} catch ( \ReflectionException $e ) {
					continue;
				}

				if ( $ref->isSubclassOf( __NAMESPACE__ . '\AbstractSingleton' ) ) {
					if ( $namespace_class::auto_init() ) {
						$namespace_class::get_instance();
					}
				}

			} else if ( is_dir( $file_path ) ) {
				$this->load_recursive( $file_path, $namespace . '\\' . basename( $file_path ) );
			}
		}
	}
}
