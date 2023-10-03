<?php

namespace Iwf2b\SettingsPage;

use Iwf2b\AbstractSingleton;
use Iwf2b\Arr;
use Iwf2b\Text;
use Iwf2b\View;

/**
 * Class AbstractSettingsPage
 *
 * @package Iwf2b\SettingsPage
 */
abstract class AbstractSettingsPage extends AbstractSingleton {
	/**
	 * Action files dir (deprecated)
	 *
	 * @var string
	 * @deprecated
	 */
	protected $action_directory = '';

	/**
	 * Action files dir
	 *
	 * @var string
	 */
	protected $action_dir = '';

	/**
	 * Template files dir (deprecated)
	 *
	 * @var string
	 * @deprecated
	 */
	protected $template_directory = '';

	/**
	 * Template files dir
	 *
	 * @var string
	 */
	protected $template_dir = '';

	/**
	 * Menu slug
	 *
	 * @var string
	 */
	protected $menu_slug = '';

	/**
	 * Menu title
	 *
	 * @var string
	 */
	protected $menu_title = '';

	/**
	 * Args for registration
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Variables for template
	 *
	 * @var array
	 */
	protected $view_vars = [];

	/**
	 * Ignore actions
	 *
	 * @var string[]
	 */
	protected $ignore_actions = [ '-1' ];

	/**
	 * @var View
	 */
	protected $view;

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		if ( ! $this->menu_slug ) {
			throw new \RuntimeException( sprintf( 'The variable "%s::$menu_slug" must be not empty.', get_class( $this ) ) );
		}

		if ( ! $this->menu_title ) {
			throw new \RuntimeException( sprintf( 'The variable "%s::$menu_title" must be not empty.', get_class( $this ) ) );
		}

		add_action( 'admin_menu', [ $this, 'register' ] );

		add_action( '_admin_menu', [ $this, 'action' ] );

		// Replaces directory keyword from $this->action_dir and $this->template_dir.
		$replaces = [
			'template_directory'   => get_template_directory(), // Backward compatibility.
			'template_dir'         => get_template_directory(),
			'stylesheet_directory' => get_stylesheet_directory(), // Backward compatibility.
			'stylesheet_dir'       => get_stylesheet_directory(),
			'plugin_directory'     => trailingslashit( WP_CONTENT_DIR ) . 'plugins', // Backward compatibility.
			'plugin_dir'           => trailingslashit( WP_CONTENT_DIR ) . 'plugins',
			'content_directory'    => WP_CONTENT_DIR, // Backward compatibility.
			'wp_content'           => WP_CONTENT_DIR,
		];

		// Backward compatibility.
		if ( $this->action_directory && ! $this->action_dir ) {
			$this->action_dir = $this->action_directory;
		}

		// Backward compatibility.
		if ( $this->template_directory && ! $this->template_dir ) {
			$this->template_dir = $this->template_directory;
		}

		if ( $this->action_dir ) {
			$this->action_dir = Text::replace( $this->action_dir, $replaces, '%' );
		}

		if ( $this->template_dir ) {
			$this->template_dir = Text::replace( $this->template_dir, $replaces, '%' );
		}

		$this->args = Arr::merge_intersect_key( [
			'parent'                   => '',
			'page_title'               => $this->menu_title,
			'capability'               => 'manage_options',
			'icon'                     => '',
			'position'                 => null,
			'remove_duplicate_submenu' => true,
		], $this->args );

		if ( $this->menu_slug && $this->args['remove_duplicate_submenu'] ) {
			add_action( 'admin_init', function () {
				remove_submenu_page( $this->menu_slug, $this->menu_slug );
			} );
		}

		$this->view = new View();
	}

	/**
	 * Register pages
	 */
	public function register() {
		if ( $this->args['parent'] ) {
			add_submenu_page(
				$this->args['parent'],
				$this->args['page_title'],
				$this->menu_title,
				$this->args['capability'],
				$this->menu_slug,
				[ $this, 'template' ]
			);

		} else {
			add_menu_page(
				$this->args['page_title'],
				$this->menu_title,
				$this->args['capability'],
				$this->menu_slug,
				[ $this, 'template' ],
				$this->args['icon'],
				$this->args['position']
			);
		}
	}

	/**
	 * Hook action
	 */
	public function action() {
		global $plugin_page;

		if ( $plugin_page === $this->menu_slug ) {
			$action = Arr::get( $_REQUEST, 'action' );

			if ( in_array( $action, $this->ignore_actions ) ) {
				$action = '';
			}

			$file_name   = static::generate_file_name( $plugin_page, $action, '.php' );
			$action_file = trailingslashit( $this->action_dir ) . $file_name;

			$this->view->add_action_file( $action_file );
			$this->view_vars = $this->view->do_action();
		}
	}

	/**
	 * Dispatch settings page
	 */
	public function template() {
		global $plugin_page;

		$action = Arr::get( $_REQUEST, 'action' );

		if ( in_array( $action, $this->ignore_actions ) ) {
			$action = '';
		}

		$file_name     = static::generate_file_name( $plugin_page, $action, '.php' );
		$template_file = trailingslashit( $this->template_dir ) . $file_name;

		$this->view->set_template_file( $template_file );
		$this->view->load( $this->view_vars, false );
	}

	/**
	 * @param $base_name
	 * @param $action
	 * @param $ext
	 *
	 * @return string
	 */
	protected static function generate_file_name( $base_name, $action, $ext ) {
		if ( $action ) {
			$file_name = $base_name . '/' . $action . $ext;

		} else {
			$file_name = $base_name . $ext;
		}

		return $file_name;
	}
}
