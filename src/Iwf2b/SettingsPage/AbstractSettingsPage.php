<?php

namespace Iwf2b\SettingsPage;

use Iwf2b\AbstractSingleton;
use Iwf2b\Arr;
use Iwf2b\Text;
use Iwf2b\View;

/**
 * Class AbstractSettingsPage
 * @package Iwf2b\SettingsPage
 */
abstract class AbstractSettingsPage extends AbstractSingleton {
	/**
	 * Action files dir
	 *
	 * @var string
	 */
	protected static $action_directory = '';

	/**
	 * Template files dir
	 *
	 * @var string
	 */
	protected static $template_directory = '';

	/**
	 * Menu slug
	 *
	 * @var string
	 */
	protected static $menu_slug = '';

	/**
	 * Menu title
	 *
	 * @var string
	 */
	protected static $menu_title = '';

	/**
	 * Args for registration
	 *
	 * @var array
	 */
	protected static $args = [];

	/**
	 * Variables for template
	 *
	 * @var array
	 */
	protected static $view_vars = [];

	/**
	 * @var View
	 */
	protected static $view;

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		if ( ! static::$menu_slug ) {
			throw new \RuntimeException( sprintf( 'The variable "%s::$menu_slug" must be not empty.', get_class( $this ) ) );
		}

		if ( ! static::$menu_title ) {
			throw new \RuntimeException( sprintf( 'The variable "%s::$menu_title" must be not empty.', get_class( $this ) ) );
		}

		add_action( 'admin_menu', [ $this, 'register' ] );

		add_action( '_admin_menu', [ $this, 'action' ] );

		// Replaces directory keyword from static::$action_dir and static::$template_dir.
		$replaces = [
			'template_directory'   => TEMPLATEPATH,
			'stylesheet_directory' => STYLESHEETPATH,
			'plugin_directory'     => WP_PLUGIN_DIR,
			'content_directory'    => WP_CONTENT_DIR,
		];

		if ( static::$action_directory ) {
			static::$action_directory = Text::replace( static::$action_directory, $replaces );
		}

		if ( static::$template_directory ) {
			static::$template_directory = Text::replace( static::$template_directory, $replaces );
		}

		static::$args = Arr::merge_intersect_key( [
			'parent'     => '',
			'page_title' => static::$menu_title,
			'capability' => 'manage_options',
			'icon'       => '',
			'position'   => null,
		], static::$args );

		static::$view = new View();
	}

	/**
	 * Register pages
	 */
	public function register() {
		if ( static::$args['parent'] ) {
			add_submenu_page(
				static::$args['parent'],
				static::$args['page_title'],
				static::$menu_title,
				static::$args['capability'],
				static::$menu_slug,
				[ $this, 'template' ]
			);

		} else {
			add_menu_page(
				static::$args['page_title'],
				static::$menu_title,
				static::$args['capability'],
				static::$menu_slug,
				[ $this, 'template' ],
				static::$args['icon'],
				static::$args['position']
			);
		}
	}

	/**
	 * Hook action
	 */
	public function action() {
		global $plugin_page;

		if ( $plugin_page === static::$menu_slug ) {
			$action      = Arr::get( $_REQUEST, 'action' );
			$file_name   = static::generate_file_name( $plugin_page, $action, '.php' );
			$action_file = trailingslashit( static::$action_directory ) . $file_name;

			static::$view->add_action_file( $action_file );
			static::$view_vars = static::$view->do_action();
		}
	}

	/**
	 * Dispatch settings page
	 */
	public function template() {
		global $plugin_page;

		$action        = Arr::get( $_REQUEST, 'action' );
		$file_name     = static::generate_file_name( $plugin_page, $action, '.php' );
		$template_file = trailingslashit( static::$template_directory ) . $file_name;

		static::$view->set_template_file( $template_file );
		static::$view->load( static::$view_vars, false );
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