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
	 * Auto initialize classes
	 *
	 * @var array
	 */
	protected $auto_init_classes = [];

	/**
	 * Already initialized classes
	 *
	 * @var array
	 */
	protected $initialized_classes = [];

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
			$this->parse_auto_init_classes( $_base_directory, $_base_namespace );
		}

		foreach ( $this->auto_init_classes as $class ) {
			$this->load_dependencies( $class );
		}
	}

	/**
	 * @param string $directory
	 * @param string $namespace
	 */
	private function parse_auto_init_classes( $directory, $namespace ) {
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
					$this->auto_init_classes[] = $namespace_class;
				}
			}
		}
	}

	/**
	 * @param $class
	 *
	 * @return bool
	 */
	private function load_dependencies( $class ) {
		$dependencies = $class::get_dependencies();

		if ( $dependencies ) {
			if ( in_array( $class, $dependencies ) ) {
				throw new \UnexpectedValueException( sprintf( 'The class "%s" recursively depends on itself.', $class ) );
			}

			foreach ( $dependencies as $dependency_class ) {
				if ( ! in_array( $dependency_class, $this->initialized_classes ) ) {
					if ( ! in_array( $dependency_class, $this->auto_init_classes ) ) {
						return false;
					}

					if ( ! $this->load_dependencies( $dependency_class ) ) {
						return false;
					}
				}
			}
		}

		if ( ! in_array( $class, $this->initialized_classes ) ) {
			$class::get_instance();

			$this->initialized_classes[] = $class;
		}

		return true;
	}
}
