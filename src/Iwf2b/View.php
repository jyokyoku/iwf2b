<?php

namespace Iwf2b;

/**
 * Class View
 *
 * @package Iwf2b
 */
class View {
	/**
	 * Template file
	 *
	 * @var string
	 */
	protected $template_file = '';

	/**
	 * Action files
	 *
	 * @var array
	 */
	protected $action_files = [];

	/**
	 * Element include base path
	 *
	 * @var string
	 */
	protected static $base_path;

	/**
	 * @param $file
	 */
	public function set_template_file( $file ) {
		$this->template_file = $file;
	}

	/**
	 * @param $file
	 */
	public function add_action_file( $file ) {
		if ( ! is_array( $file ) ) {
			$this->action_files[] = $file;

		} else {
			foreach ( $file as $_file ) {
				$this->action_files[] = $_file;
			}
		}
	}

	/**
	 * @param array $view_vars
	 * @param bool  $do_action
	 */
	public function load( array $view_vars = [], $do_action = true ) {
		if ( ! $this->template_file ) {
			return;
		}

		$action_view_vars = [];

		if ( $do_action ) {
			$action_view_vars = $this->do_action();
		}

		if ( $action_view_vars ) {
			$view_vars = array_merge( $view_vars, $action_view_vars );
		}

		if ( $view_vars ) {
			extract( $view_vars, EXTR_SKIP );
		}

		include $this->template_file;
	}

	/**
	 * @param array $view_vars
	 * @param bool  $do_action
	 */
	public function load_global( array $view_vars = [], $do_action = true ) {
		if ( ! $this->template_file ) {
			return;
		}

		global $wp_query, $query_string, $posts, $post, $request;

		if ( is_a( $wp_query, 'WP_Query' ) ) {
			foreach ( (array) $wp_query->query_vars as $key => $value ) {
				global ${$key};
			}

			if ( $wp_query->is_single() || $wp_query->is_page() ) {
				global $more, $single;
			}

			if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
				global $authordata;
			}
		}

		$action_view_vars = [];

		if ( $do_action ) {
			$action_view_vars = $this->do_action();
		}

		if ( $action_view_vars ) {
			$view_vars = array_merge( $view_vars, $action_view_vars );
		}

		if ( $view_vars ) {
			extract( $view_vars, EXTR_SKIP );
		}

		include $this->template_file;
	}

	/**
	 * @return array
	 */
	public function do_action() {
		if ( ! $this->action_files ) {
			return [];
		}

		$view_vars_store = [];

		foreach ( $this->action_files as $action_file ) {
			if ( is_file( $action_file ) ) {
				$temp_view_vars = include( $action_file );

				if ( is_array( $temp_view_vars ) ) {
					$view_vars_store[] = $temp_view_vars;
				}
			}
		}

		return $view_vars_store ? call_user_func_array( 'array_merge', $view_vars_store ) : [];
	}

	/**
	 * @param $path
	 */
	public static function set_base_path( $path ) {
		static::$base_path = untrailingslashit( $path );
	}

	/**
	 * @param        $slug
	 * @param string $name
	 * @param array  $vars
	 */
	public static function element( $slug, $name = '', $vars = [] ) {
		$templates = [];
		$name      = (string) $name;

		if ( $name !== '' ) {
			$templates[] = "elements/{$slug}-{$name}.php";
		}

		$templates[]   = "elements/{$slug}.php";
		$template_file = static::locate_template( $templates );

		if ( ! $template_file ) {
			throw new \InvalidArgumentException( sprintf( 'The template file "%s" could not be found.', $template_file ) );
		}

		$view = new static();

		$view->set_template_file( $template_file );
		$view->load_global( $vars );
	}

	/**
	 * @param array $template_names
	 *
	 * @return string
	 */
	protected static function locate_template( array $template_names ) {
		$located = '';

		foreach ( $template_names as $template_name ) {
			if ( static::$base_path && file_exists( static::$base_path . '/' . $template_name ) ) {
				$located = static::$base_path . '/' . $template_name;

			} elseif ( file_exists( STYLESHEETPATH . '/' . $template_name ) ) {
				$located = STYLESHEETPATH . '/' . $template_name;

			} elseif ( file_exists( TEMPLATEPATH . '/' . $template_name ) ) {
				$located = TEMPLATEPATH . '/' . $template_name;

			} elseif ( file_exists( ABSPATH . WPINC . '/theme-compat/' . $template_name ) ) {
				$located = ABSPATH . WPINC . '/theme-compat/' . $template_name;
			}

			if ( $located ) {
				break;
			}
		}

		return $located;
	}
}
