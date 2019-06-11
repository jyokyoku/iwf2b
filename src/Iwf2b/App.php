<?php

namespace Iwf2b;

class App {
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
	 * Create all instances of AbstractSingleton sub classes.
	 */
	public function bootstrap( $base_dir, $base_namespace ) {
		$recursive_include = function ( $parent_dir, $namespace ) use ( &$recursive_include ) {
			foreach ( scandir( $parent_dir ) as $file_name ) {
				if ( $file_name === '.' || $file_name === '..' ) {
					continue;
				}

				$file_path = $parent_dir . '/' . $file_name;

				if ( is_file( $file_path ) && strrpos( $file_name, '.php' ) !== false && $file_path !== __FILE__ ) {
					$class           = substr( $file_name, 0, - 4 );
					$namespace_class = $namespace . '\\' . substr( $file_name, 0, - 4 );

					if ( substr( $class, 0, 8 ) === 'Abstract' || substr( $class, - 9 ) === 'Interface' || substr( $class, - 6 ) === 'Trait' ) {
						continue;
					}

					if ( is_subclass_of( $namespace_class, __NAMESPACE__ . '\AbstractSingleton' ) ) {
						if ( $namespace_class::auto_init() ) {
							$namespace_class::get_instance();
						}
					}

				} else if ( is_dir( $file_path ) ) {
					$recursive_include( $file_path, $namespace . '\\' . basename( $file_path ) );
				}
			}
		};

		$recursive_include( $base_dir, $base_namespace );
	}
}
