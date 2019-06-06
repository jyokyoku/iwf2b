<?php

namespace Iwf2b\Post;

use Iwf2b\AbstractSingleton;
use Iwf2b\Util;

abstract class AbstractPost extends AbstractSingleton {
	protected static $post_type = '';

	protected static $args = [];

	protected static $builtin = false;

	protected static $find_args = [];

	protected function initialize() {
		add_action( 'init', [ $this, 'register_post_type' ] );
	}

	public function register_post_type() {
		if ( ! static::$builtin ) {
			register_post_type( static::$post_type, static::$args );
		}
	}

	/**
	 * @param int|\WP_Post $post_id
	 *
	 * @return \WP_Post|null
	 */
	public static function get( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || ! static::is_valid( $post ) ) {
			return null;
		}

		return $post;
	}

	/**
	 * @return string
	 */
	public static function get_post_type() {
		return static::$post_type;
	}

	/**
	 * @param $post_id
	 *
	 * @return bool
	 */
	public static function is_valid( $post_id ) {
		$post = get_post( $post_id );

		return $post && $post->post_type === static::$post_type;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function create_args( array $args = [] ) {
		$args              = array_merge( static::$find_args, $args );
		$args['post_type'] = static::$post_type;

		return $args;
	}

	/**
	 * @param array $args
	 *
	 * @return int[]|\WP_Post[]
	 */
	public static function get_posts( array $args = [] ) {
		return get_posts( static::create_args( $args ) );
	}

	/**
	 * @param int|array $args
	 *
	 * @return \WP_Post|null
	 */
	public static function get_post( $args = [] ) {
		$args['posts_per_page'] = 1;
		$posts                  = get_posts( static::create_args( $args ) );

		if ( $posts ) {
			return reset( $posts );
		}

		return null;
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_Query
	 */
	public static function get_query( array $args = [] ) {
		return new \WP_Query( static::create_args( $args ) );
	}

	/**
	 * @param int|\WP_Post $post_id
	 * @param string $taxonomy
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_terms( $post_id, $taxonomy, array $args = [] ) {
		$post = static::get( $post_id );

		if ( ! $post ) {
			return [];
		}

		$terms = get_object_term_cache( $post->ID, $taxonomy );

		if ( false === $terms ) {
			$terms = wp_get_object_terms( $post->ID, $taxonomy, $args );

			if ( is_wp_error( $terms ) ) {
				return [];
			}

			$term_ids = wp_list_pluck( $terms, 'term_id' );
			wp_cache_add( $post->ID, $term_ids, $taxonomy . '_relationships' );
		}

		return $terms;
	}

	/**
	 * @param int|\WP_Post $post_id
	 * @param string $taxonomy
	 * @param array $args
	 *
	 * @return null|\WP_Term
	 */
	public static function get_term( $post_id, $taxonomy, array $args = [] ) {
		$terms = static::get_terms( $post_id, $taxonomy, $args );

		if ( $terms ) {
			return reset( $terms );
		}

		return null;
	}

	/**
	 * @param string $template
	 * @param string $default
	 *
	 * @return string
	 */
	public static function get_permalink_by_tmpl( $template, $default = '' ) {
		$post = static::get_post( [
			'meta_query' => [
				[
					'key'   => '_wp_page_template',
					'value' => $template,
				],
			],
		] );

		if ( $post ) {
			return get_permalink( $post );
		}

		return $default;
	}

	/**
	 * 投稿のアイキャッチ画像を取得
	 *
	 * @param int|\WP_Post $post
	 * @param bool|string $search_post_key
	 * @param bool|string $dummy_image
	 *
	 * @return array
	 */
	public static function get_thumbnail( $post, $search_post_key = false, $dummy_image = true ) {
		$thumbnail = iwf_get_post_thumbnail_data( $post, $search_post_key );

		if ( empty( $thumbnail ) && $dummy_image ) {
			if ( filter_var( $dummy_image, FILTER_VALIDATE_URL ) && preg_match( '|^https?://.*$|', $dummy_image ) ) {
				$thumbnail['src'] = $dummy_image;

			} else {
				$thumbnail['src'] = Util::get_dummy_image();
			}

			$thumbnail['alt'] = get_the_title( $post );
		}

		return $thumbnail;
	}

	/**
	 * @param $post
	 * @param $key
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function get_meta( $post, $key, array $args = [] ) {
		$value = function_exists( 'get_field' ) ? get_field( $key, $post ) : get_post_meta( $post->ID, $key, true );

		return Util::filter( $value, $args );
	}
}