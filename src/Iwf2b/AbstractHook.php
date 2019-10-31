<?php

namespace Iwf2b;

/**
 * Class AbstractHook
 * @package Iwf2b
 */
abstract class AbstractHook extends AbstractSingleton {
	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		// テーマの初期化
		add_action( 'init', [ $this, 'init_theme' ] );

		// チェックされたタクソノミーが一番上にこないようにする
		add_filter( 'wp_terms_checklist_args', [ $this, 'terms_checklist_args' ], 10, 2 );

		// TinyMCEのオーバーライドチェック
		add_action( 'admin_enqueue_scripts', [ $this, 'check_tinymce_override' ] );

		// 標準ウィジェットのクリーンアップ
		add_action( 'widgets_init', [ $this, 'remove_default_widgets' ] );

		// 管理画面メニューのクリーンアップ
		add_action( 'admin_menu', [ $this, 'remove_admin_menu' ] );

		// 管理画面ダッシュボードのクリーンアップ
		add_action( 'wp_dashboard_setup', [ $this, 'remove_dashboard_widgets' ] );

		// 管理バーのクリーンアップ
		add_filter( 'wp_before_admin_bar_render', [ $this, 'remove_admin_bar_menu' ] );

		// テンプレート表示前の処理
		add_action( 'template_redirect', [ $this, 'before_filter' ] );

		// クエリーフィルター
		add_action( 'pre_get_posts', [ $this, 'query_filter' ] );

		// テンプレートフィルター
		add_filter( 'template_include', [ $this, 'template_filter' ], 100, 1 );

		// the_content実行前のフィルター
		add_action( 'the_content', [ $this, 'pre_the_content' ], 1 );

		// SQLフィルター
		add_filter( 'posts_clauses', [ $this, 'posts_sql_filter' ], 10, 2 );

		// CSS/JavaScriptの読み込み
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// headタグ内の独自スクリプト
		add_action( 'wp_head', [ $this, 'head_scripts' ] );

		// Contact Form 7のHTML5生成を中止
		add_filter( 'wpcf7_support_html5', '__return_false' );

		// Contact Form 7の設定画面表示を管理者に限定する
		add_filter( 'wpcf7_map_meta_cap', [ $this, 'cf7_meta_cap' ] );

		// Yoastのメタボックスの位置を調整
		add_filter( 'wpseo_metabox_prio', [ $this, 'yoast_seo_metabox_priority' ] );

		// headerタグ内をクリーンに
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'rest_output_link_wp_head' );

		// フィード系リンクを削除
		remove_action( 'wp_head', 'feed_links_extra', 3 );

		// 絵文字関連を削除
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		add_filter( 'emoji_svg_url', '__return_false' );

		// ウェルカムパネルを削除
		remove_action( 'welcome_panel', 'wp_welcome_panel' );

		// 検索キーワードに含まれる全角スペースを半角に
		add_action( 'init', [ $this, 'filter_search_keyword' ] );

		// 投稿URLを自動生成する
		add_filter( 'wp_unique_post_slug', [ $this, 'auto_post_slug' ], 10, 4 );

		// タイトル・本文をそのまま出力できるように
		remove_filter( 'the_title', 'wptexturize' );
		remove_filter( 'the_excerpt', 'wptexturize' );
		remove_filter( 'the_content', 'wptexturize' );
		remove_filter( 'comment_text', 'wptexturize' );

		// プレビューにもメタデータを保存する
		add_filter( 'get_post_metadata', [ $this, 'get_preview_post_meta_data' ], 10, 4 );
		add_action( 'wp_insert_post', [ $this, 'save_preview_post' ] );

		if ( class_exists( 'ACF' ) ) {
			// ACF対応
			add_action( 'init', [ $this, 'acf_load_config' ], 1 );
			add_filter( 'acf_activated', [ $this, 'acf_activated' ] );
			add_filter( 'acf/settings/path', [ $this, 'acf_settings_path' ] );
			add_filter( 'acf/settings/dir', [ $this, 'acf_settings_dir' ] );
			add_filter( 'acf/load_field', [ $this, 'acf_default_vars' ] );
			add_action( 'save_preview_postmeta', [ $this, 'acf_save_preview_postmeta' ] );
			add_action( 'save_post', [ $this, 'acf_field_auto_export' ], 1000, 3 );

			if ( ! apply_filters( 'acf_activated', false ) ) {
				add_filter( 'acf/settings/show_admin', '__return_false' );
			}
		}

		// アップデート通知をOFF
		//remove_action( 'wp_version_check', 'wp_version_check' );
		//remove_action( 'admin_init', '_maybe_update_core' );
		//remove_action( 'admin_init', '_maybe_update_plugins' );
		//remove_action( 'admin_init', '_maybe_update_themes' );
		//add_filter( 'pre_site_transient_update_core', '__return_zero' );
		//add_filter( 'pre_site_transient_update_plugins', '__return_zero' );
	}

	/**
	 * Yoastのメタボックスの位置を調整
	 *
	 * @return string
	 */
	function yoast_seo_metabox_priority() {
		return 'low';
	}

	/**
	 * @param string $slug
	 * @param int $post_ID
	 * @param $post_status
	 * @param $post_type
	 *
	 * @return string
	 */
	function auto_post_slug( $slug, $post_ID, $post_status, $post_type ) {
		if ( preg_match( '/(%[0-9a-f]{2})+/', $slug ) ) {
			if ( $post_ID ) {
				$slug = $post_type . '-' . $post_ID;
			}
		}

		return $slug;
	}

	/**
	 * 検索クエリに含まれる全角スペースを半角に
	 */
	public function filter_search_keyword() {
		if ( isset( $_GET['s'] ) ) {
			$_GET['s'] = wp_unslash( $_GET['s'] );
			$_GET['s'] = mb_convert_kana( $_GET['s'], 's' );
			$_GET['s'] = preg_replace( "/ +/", " ", $_GET['s'] );
			$_GET['s'] = wp_slash( $_GET['s'] );
		}
	}

	/**
	 * テーマの初期化
	 */
	public function init_theme() {
		$path = dirname( $_SERVER['REQUEST_URI'] );

		if ( isset( $_GET['show_acf'] ) ) {
			setcookie( 'show_acf', 1, 0, $path );

		} else if ( isset( $_GET['hide_acf'] ) ) {
			setcookie( 'show_acf', 0, time() - 3600, $path );
		}
	}

	/**
	 * ACFフィールドの初期設定
	 */
	public function acf_load_config() {
		if ( ! $this->acf_activated() ) {
			$files = scandir( get_stylesheet_directory(), SCANDIR_SORT_ASCENDING );

			foreach ( $files as $file ) {
				if ( preg_match( '|^acf-export(?:-.*)?\.json$|', $file, $matches ) ) {
					$field_groups = json_decode( file_get_contents( trailingslashit( get_stylesheet_directory() ) . $file ), true );

					if ( ! empty( $field_groups ) ) {
						foreach ( $field_groups as $field_group ) {
							acf_add_local_field_group( $field_group );
						}
					}
				}
			}
		}
	}

	/**
	 * ACF Activated
	 *
	 * @return bool
	 */
	public function acf_activated() {
		return ! empty( $_COOKIE['show_acf'] ) || isset( $_GET['show_acf'] );
	}

	/**
	 * ACF Path
	 */
	public function acf_settings_path( $path ) {
		$new_path = get_stylesheet_directory() . '/vendor/advanced-custom-fields-pro/';

		if ( is_dir( $new_path ) ) {
			$path = $new_path;

		} else {
			$new_path = get_template_directory() . '/vendor/advanced-custom-fields-pro/';

			if ( is_dir( $new_path ) ) {
				$path = $new_path;
			}
		}

		return $path;
	}

	/**
	 * ACF Dir
	 */
	public function acf_settings_dir( $dir ) {
		$new_dir = get_stylesheet_directory() . '/vendor/advanced-custom-fields-pro/';

		if ( is_dir( $new_dir ) ) {
			$dir = get_stylesheet_directory_uri() . '/vendor/advanced-custom-fields-pro/';

		} else {
			$new_dir = get_template_directory() . '/vendor/advanced-custom-fields-pro/';

			if ( is_dir( $new_dir ) ) {
				$dir = get_template_directory_uri() . '/vendor/advanced-custom-fields-pro/';
			}
		}

		return $dir;
	}

	/**
	 * ACFのフィールドにデフォルト値を動的に指定
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function acf_default_vars( $field ) {
		return $field;
	}

	/**
	 * ACFフィールド定義を自動的にjson出力
	 *
	 * @param $post_id
	 * @param $post
	 * @param $update
	 */
	public function acf_field_auto_export( $post_id, $post, $update ) {
		if ( $post->post_type !== 'acf-field-group' ) {
			return;
		}

		$field_groups = acf_get_field_groups();
		$json         = array();

		foreach ( $field_groups as $field_group ) {
			$field_group['fields'] = acf_get_fields( $field_group );
			$json[]                = acf_prepare_field_group_for_export( $field_group );
		}

		$hash      = Text::short_hash( 'acf_export' );
		$file_name = 'acf-export-9' . $hash . '.json';

		file_put_contents( trailingslashit( get_stylesheet_directory() ) . $file_name, acf_json_encode( $json ) );
	}

	/**
	 * プレビュー時にACFのメタデータも保存する
	 *
	 * @param $post_id
	 */
	public function acf_save_preview_postmeta( $post_id ) {
		if ( ! function_exists( 'get_field_object' ) ) {
			return;
		}

		if ( isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
			foreach ( $_POST['acf'] as $key => $value ) {
				$field = get_field_object( $key );

				if ( empty( $field['name'] ) || empty( $field['key'] ) ) {
					continue;
				}

				update_metadata( 'post', $post_id, $field['name'], $value );
				update_metadata( 'post', $post_id, "_" . $field['name'], $field['key'] );
			}
		}
	}

	/**
	 * プレビューのメタ情報を取得する
	 *
	 * @param $meta_value
	 * @param $post_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return mixed
	 */
	public function get_preview_post_meta_data( $meta_value, $post_id, $meta_key, $single ) {
		global $post;

		if ( ! empty( $_GET['preview'] ) && $post->ID == $post_id && url_to_postid( $_SERVER['REQUEST_URI'] ) == $post_id ) {
			$preview = wp_get_post_autosave( $post_id );

			if ( $preview ) {
				if ( $post_id != $preview->ID ) {
					$meta_value = get_post_meta( $preview->ID, $meta_key, $single );
				}
			}
		}

		return $meta_value;
	}

	/**
	 * プレビュー時にメタデータも保存するためのフックを実行する
	 *
	 * @param $post_id
	 */
	public function save_preview_post( $post_id ) {
		if ( wp_is_post_revision( $post_id ) ) {
			do_action( 'save_preview_postmeta', $post_id );
		}
	}

	/**
	 * Contact Form 7の設定画面表示を管理者に限定する
	 */
	public function cf7_meta_cap( $meta_caps ) {
		$meta_caps['wpcf7_edit_contact_form']   = 'manage_options';
		$meta_caps['wpcf7_edit_contact_forms']  = 'manage_options';
		$meta_caps['wpcf7_read_contact_forms']  = 'manage_options';
		$meta_caps['wpcf7_delete_contact_form'] = 'manage_options';

		return $meta_caps;
	}

	/**
	 * TinyMCEのオーバーライドチェック
	 */
	public function check_tinymce_override() {
		global $pagenow, $post_type;

		if ( in_array( $pagenow, [ 'post.php', 'post-new.php' ] ) && $post_type === 'page' ) {
			add_filter( 'tiny_mce_before_init', [ $this, 'override_mce_options' ] );
		}
	}

	/**
	 * TinyMCEの空タグ削除を抑制
	 *
	 * @param array $init_array
	 *
	 * @return array
	 */
	public function override_mce_options( $init_array ) {
		global $allowedposttags;

		$init_array['valid_elements']          = '*[*]';
		$init_array['extended_valid_elements'] = '*[*]';
		$init_array['valid_children']          = '+a[' . implode( '|', array_keys( $allowedposttags ) ) . ']';
		$init_array['indent']                  = true;
		$init_array['wpautop']                 = false;

		return $init_array;
	}

	/**
	 * チェックされたタクソノミーが一番上にこないようにする
	 *
	 * @param array $args
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function terms_checklist_args( $args, $post_id ) {
		if ( ! isset( $args['checked_ontop'] ) || $args['checked_ontop'] !== false ) {
			$args['checked_ontop'] = false;
		}

		return $args;
	}

	/**
	 * テンプレート表示前の処理
	 */
	public function before_filter() {
	}

	/**
	 * テンプレートフィルター
	 *
	 * @param string $template
	 */
	public function template_filter( $template ) {
		$view = new View();

		$view->add_action_file( locate_template( 'actions/common.php' ) );
		$view->add_action_file( locate_template( 'actions/' . basename( $template ) ) );

		$view->set_template_file( $template );
		$view->load_global();

		exit;
	}

	/**
	 * クエリーフィルター
	 *
	 * @param \WP_Query $the_query
	 */
	public function query_filter( $the_query ) {
	}

	/**
	 * SQLフィルター
	 *
	 * @param array $sql
	 * @param \WP_Query $the_query
	 *
	 * @return array
	 */
	public function posts_sql_filter( $sql, $the_query ) {
		return $sql;
	}

	/**
	 * the_content実行前のフィルター
	 */
	public function pre_the_content( $content ) {
		if ( is_page() ) {
			// 固定ページだけ wpautop フィルターを削除
			remove_filter( 'the_content', 'wpautop' );
		}

		return $content;
	}

	/**
	 * CSS/JavaScriptの読み込み
	 */
	public function enqueue_scripts() {
	}

	/**
	 * headタグ内の独自スクリプト
	 */
	public function head_scripts() {
		?>
		<script>
			var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ) ?>';
		</script>
		<?php
	}

	/**
	 * 管理画面メニューのクリーンアップ
	 */
	public function remove_admin_menu() {
		remove_menu_page( 'index.php' ); //ダッシュボード
		remove_menu_page( 'separator1' ); // セパレータ1
		remove_menu_page( 'edit.php' ); // 投稿
		remove_menu_page( 'upload.php' ); // メディア
		remove_menu_page( 'edit-tags.php?taxonomy=link_category' ); // リンク
		remove_menu_page( 'edit.php?post_type=page' ); // 固定ページ
		remove_menu_page( 'edit-comments.php' ); // コメント
		remove_menu_page( 'separator2' ); // セパレータ2
		remove_menu_page( 'themes.php' ); // 外観
		remove_menu_page( 'plugins.php' ); // プラグイン
		remove_menu_page( 'users.php' ); // ユーザー
		remove_menu_page( 'tools.php' ); // ツール
		remove_menu_page( 'options-general.php' ); // 設定
		remove_menu_page( 'separator-last' ); // セパレータlast
	}

	/**
	 * 管理画面ダッシュボードのクリーンアップ
	 */
	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_right_now', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_activity', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_incoming_links', 'dashboard', 'normal' );
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_quick_press', 'dashboard', 'side' );
		remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );
	}

	/**
	 * 標準ウィジェットのクリーンアップ
	 */
	public function remove_default_widgets() {
		unregister_widget( 'WP_Widget_Pages' );
		unregister_widget( 'WP_Widget_Calendar' );
		unregister_widget( 'WP_Widget_Archives' );
		unregister_widget( 'WP_Widget_Links' );
		unregister_widget( 'WP_Widget_Meta' );
		unregister_widget( 'WP_Widget_Search' );
		unregister_widget( 'WP_Widget_Text' );
		unregister_widget( 'WP_Widget_Categories' );
		unregister_widget( 'WP_Widget_Recent_Posts' );
		unregister_widget( 'WP_Widget_Recent_Comments' );
		unregister_widget( 'WP_Widget_RSS' );
		unregister_widget( 'WP_Widget_Tag_Cloud' );
		unregister_widget( 'WP_Nav_Menu_Widget' );
	}

	/**
	 * 管理バーのクリーンアップ
	 */
	public function remove_admin_bar_menu() {
		global $wp_admin_bar;

		// top-secondary
		$wp_admin_bar->remove_menu( 'top-secondary' );
		$wp_admin_bar->remove_menu( 'my-account' );
		$wp_admin_bar->remove_menu( 'user-actions' );
		$wp_admin_bar->remove_menu( 'user-info' );
		$wp_admin_bar->remove_menu( 'edit-profile' );
		$wp_admin_bar->remove_menu( 'logout' );

		// menu-toggle
		$wp_admin_bar->remove_menu( 'menu-toggle' );

		// wp-logo-external
		$wp_admin_bar->remove_menu( 'wp-logo-external' );
		$wp_admin_bar->remove_menu( 'wporg' );
		$wp_admin_bar->remove_menu( 'documentation' );
		$wp_admin_bar->remove_menu( 'support-forums' );

		// site-name
		$wp_admin_bar->remove_menu( 'site-name' );
		$wp_admin_bar->remove_menu( 'view-site' );

		// wp-logo
		$wp_admin_bar->remove_menu( 'wp-logo' );
		$wp_admin_bar->remove_menu( 'about' );
		$wp_admin_bar->remove_menu( 'feedback' );

		// updates
		$wp_admin_bar->remove_menu( 'updates' );

		// comments
		$wp_admin_bar->remove_menu( 'comments' );

		// new-content
		$wp_admin_bar->remove_menu( 'new-content' );
		$wp_admin_bar->remove_menu( 'new-post' );
		$wp_admin_bar->remove_menu( 'new-media' );
		$wp_admin_bar->remove_menu( 'new-page' );
		$wp_admin_bar->remove_menu( 'new-user' );
	}
}