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
			if ( empty( static::$args['labels'] ) ) {
				static::$args['labels'] = [
					'name'                  => static::$args['label'],
					'singular_name'         => static::$args['label'],
					'add_new_item'          => sprintf( _x( 'Add New %s', 'post', 'iwf2b' ), static::$args['label'] ),
					'edit_item'             => sprintf( _x( 'Edit %s', 'post', 'iwf2b' ), static::$args['label'] ),
					'new_item'              => sprintf( __( 'New %s', 'iwf2b' ), static::$args['label'] ),
					'view_item'             => sprintf( _x( 'View %s', 'post', 'iwf2b' ), static::$args['label'] ),
					'view_items'            => sprintf( __( 'View %s', 'iwf2b' ), static::$args['label'] ),
					'search_items'          => sprintf( _x( 'Search %s', 'post', 'iwf2b' ), static::$args['label'] ),
					'not_found'             => sprintf( _x( 'No %s found.', 'post', 'iwf2b' ), static::$args['label'] ),
					'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'iwf2b' ), static::$args['label'] ),
					'parent_item_colon'     => sprintf( _x( 'Parent %s:', 'post', 'iwf2b' ), static::$args['label'] ),
					'all_items'             => sprintf( _x( 'All %s', 'post', 'iwf2b' ), static::$args['label'] ),
					'archives'              => sprintf( __( '%s Archives', 'iwf2b' ), static::$args['label'] ),
					'attributes'            => sprintf( __( '%s Attributes', 'iwf2b' ), static::$args['label'] ),
					'insert_into_item'      => sprintf( __( 'Insert into %s', 'iwf2b' ), static::$args['label'] ),
					'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'iwf2b' ), static::$args['label'] ),
					'filter_items_list'     => sprintf( __( 'Filter %s list', 'iwf2b' ), static::$args['label'] ),
					'items_list_navigation' => sprintf( _x( '%s list navigation', 'post', 'iwf2b' ), static::$args['label'] ),
					'items_list'            => sprintf( _x( '%s list', 'post', 'iwf2b' ), static::$args['label'] ),
				];
			}

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
	 * @param int|\WP_Post $post_id
	 * @param bool|string $search_post_key
	 * @param string $dummy_image
	 *
	 * @return array
	 */
	public static function get_thumbnail( $post_id, $search_post_key = false, $dummy_image = '' ) {
		$data = [
			'src' => '',
			'alt' => '',
		];

		$post = static::get( $post_id );

		if ( ! $post ) {
			return $data;
		}

		$data['src'] = $dummy_image;
		$data['alt'] = get_the_title( $post );

		if ( has_post_thumbnail( $post->ID ) ) {
			$attachment_id = get_post_thumbnail_id( $post->ID );
			$attachment    = get_post( $attachment_id );

			if ( $attachment ) {
				$image_src   = wp_get_attachment_image_src( $attachment->ID, '' );
				$data['src'] = isset( $image_src[0] ) ? $image_src[0] : '';

				$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

				if ( empty( $alt ) ) {
					$alt = $attachment->post_excerpt;
				}

				if ( empty( $alt ) ) {
					$alt = $attachment->post_title;
				}

				$data['alt'] = trim( wp_strip_all_tags( $alt, true ) );
			}

		} else if ( $search_post_key && isset( $post->{$search_post_key} )
		            && preg_match( '/<img[^>]*?src\s*=\s*["\']([^"\']+)["\'].*?\/?>/i', $post->{$search_post_key}, $matches ) ) {
			$data['src'] = $matches[1];
		}

		return $data;
	}

	/**
	 * @param int|\WP_Post $post
	 * @param string $key
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function get_meta( $post_id, $key, array $args = [] ) {
		$post = static::get( $post_id );

		if ( ! $post ) {
			return null;
		}

		$value = function_exists( 'get_field' ) ? get_field( $key, $post ) : get_post_meta( $post->ID, $key, true );

		return Util::filter( $value, $args );
	}

	/**
	 * @param int|\WP_Post $post_id
	 * @param bool $include_current
	 * @param bool $reverse
	 *
	 * @return \WP_Post[]
	 */
	public static function get_parents( $post_id, $include_current = false, $reverse = false ) {
		$post = static::get( $post_id );

		if ( ! $post ) {
			return [];
		}

		$tree = $include_current ? [ $post ] : [];

		while ( $post->post_parent ) {
			$post = static::get( $post->post_parent );

			if ( ! $post ) {
				break;
			}

			$tree[] = $post;
		}

		return $reverse ? $tree : array_reverse( $tree );
	}
}