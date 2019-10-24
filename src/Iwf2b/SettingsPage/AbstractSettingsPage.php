<?php

namespace Iwf2b\SettingsPage;

use Iwf2b\AbstractSingleton;
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
	protected static $action_dir = '';

	/**
	 * Template files dir
	 *
	 * @var string
	 */
	protected static $template_dir = '';

	/**
	 * Menu slug
	 *
	 * @var string
	 */
	protected static $menu_slug = '';

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
	protected static $loader = null;

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		add_action( 'admin_menu', [ $this, 'register' ] );

		add_action( '_admin_menu', [ $this, 'action' ] );

		static::$args = wp_parse_args( static::$args, [
			'parent'     => '',
			'page_title' => '',
			'menu_title' => '',
			'capability' => '',
			'icon'       => '',
			'position'   => '',
		] );

		static::$loader = new View();
	}

	/**
	 * Register pages
	 */
	public function register() {
		if ( static::$args['parent'] ) {
			add_submenu_page(
				static::$args['parent'],
				static::$args['page_title'],
				static::$args['menu_title'],
				static::$args['capability'],
				static::$menu_slug,
				[ $this, 'template' ]
			);

		} else {
			add_menu_page(
				static::$args['page_title'],
				static::$args['menu_title'],
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
			$action      = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;
			$file_name   = static::generate_file_name( $plugin_page, $action, '.php' );
			$action_file = trailingslashit( static::$action_dir ) . $file_name;

			static::$loader->add_action_file( $action_file );
			static::$view_vars = static::$loader->do_action();
		}
	}

	/**
	 * Dispatch settings page
	 */
	public function template() {
		global $plugin_page;

		$action        = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null;
		$file_name     = static::generate_file_name( $plugin_page, $action, '.php' );
		$template_file = trailingslashit( static::$template_dir ) . $file_name;

		static::$loader->set_template_file( $template_file );
		static::$loader->load( static::$view_vars, false );
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