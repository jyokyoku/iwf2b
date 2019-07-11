<?php

namespace Iwf2b\SettingsPage;

use Iwf2b\AbstractSingleton;
use Iwf2b\View;

abstract class AbstractSettingsPage extends AbstractSingleton {
	protected static $action_dir = '';

	protected static $template_dir = '';

	protected static $parent = '';

	protected static $page_title = '';

	protected static $menu_title = '';

	protected static $capability = '';

	protected static $menu_slug = '';

	protected static $icon = '';

	protected static $position = null;

	protected static $view_vars = [];

	/**
	 * @var View
	 */
	protected static $loader = null;

	protected function initialize() {
		add_action( 'admin_menu', [ $this, 'register' ] );

		add_action( '_admin_menu', [ $this, 'action' ] );

		static::$loader = new View();
	}

	/**
	 * Register pages
	 */
	public function register() {
		if ( static::$parent ) {
			add_submenu_page( static::$parent, static::$page_title, static::$menu_title, static::$capability, static::$menu_slug, [ $this, 'template' ] );

		} else {
			add_menu_page( static::$page_title, static::$menu_title, static::$capability, static::$menu_slug, [ $this, 'template' ], static::$icon, static::$position );
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