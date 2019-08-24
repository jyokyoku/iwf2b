<?php

namespace Iwf2b;

/**
 * Class View
 */
class View {
	protected $template_file = '';

	protected $action_files = [];

	public function set_template_file( $file ) {
		$this->template_file = $file;
	}

	public function add_action_file( $file ) {
		if ( ! is_array( $file ) ) {
			$this->action_files[] = $file;

		} else {
			foreach ( $file as $_file ) {
				$this->action_files[] = $_file;
			}
		}
	}

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
	 * @param $slug
	 * @param string $name
	 * @param array $vars
	 */
	public static function element( $slug, $name = '', $vars = [] ) {
		$templates = [];
		$name      = (string) $name;

		if ( $name !== '' ) {
			$templates[] = "elements/{$slug}-{$name}.php";
		}

		$templates[]   = "elements/{$slug}.php";
		$template_file = locate_template( $templates, false, false );

		$view = new static();

		$view->set_template_file( $template_file );
		$view->load_global( $vars );
	}
}
