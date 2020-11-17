<?php

namespace Iwf2b\Post;

use Iwf2b\AbstractSingleton;
use Iwf2b\Arr;
use Iwf2b\Util;

/**
 * Class AbstractPost
 *
 * @package Iwf2b\Post
 */
abstract class AbstractPost extends AbstractSingleton {
	/**
	 * Post type slug
	 *
	 * @var string
	 */
	protected static $post_type;

	/**
	 * Args for registration
	 *
	 * @var array
	 */
	protected static $args = [];

	/**
	 * Builtin post type
	 *
	 * @var bool
	 */
	protected static $builtin = false;

	/**
	 * Search conditions
	 *
	 * @var array
	 */
	protected static $find_args = [];

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		if ( ! static::$post_type ) {
			throw new \RuntimeException( sprintf( 'The variable "%s::$post_type" must be not empty.', get_class( $this ) ) );
		}

		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'use_block_editor_for_post_type', [ $this, 'use_block_editor' ], 10, 2 );
	}

	/**
	 * Register post type
	 */
	public function register_post_type() {
		$args = wp_parse_args( static::$args, [
			'supports' => [],
		] );

		if ( ! static::$builtin ) {
			if ( ! empty( $args['label'] ) && empty( $args['labels'] ) ) {
				$args['labels'] = [
					'name'                  => $args['label'],
					'singular_name'         => $args['label'],
					'add_new_item'          => sprintf( _x( 'Add New %s', 'post', 'iwf2b' ), $args['label'] ),
					'edit_item'             => sprintf( _x( 'Edit %s', 'post', 'iwf2b' ), $args['label'] ),
					'new_item'              => sprintf( __( 'New %s', 'iwf2b' ), $args['label'] ),
					'view_item'             => sprintf( _x( 'View %s', 'post', 'iwf2b' ), $args['label'] ),
					'view_items'            => sprintf( __( 'View %s', 'iwf2b' ), $args['label'] ),
					'search_items'          => sprintf( _x( 'Search %s', 'post', 'iwf2b' ), $args['label'] ),
					'not_found'             => sprintf( _x( 'No %s found.', 'post', 'iwf2b' ), $args['label'] ),
					'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'iwf2b' ), $args['label'] ),
					'parent_item_colon'     => sprintf( _x( 'Parent %s:', 'post', 'iwf2b' ), $args['label'] ),
					'all_items'             => sprintf( _x( 'All %s', 'post', 'iwf2b' ), $args['label'] ),
					'archives'              => sprintf( __( '%s Archives', 'iwf2b' ), $args['label'] ),
					'attributes'            => sprintf( __( '%s Attributes', 'iwf2b' ), $args['label'] ),
					'insert_into_item'      => sprintf( __( 'Insert into %s', 'iwf2b' ), $args['label'] ),
					'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'iwf2b' ), $args['label'] ),
					'filter_items_list'     => sprintf( __( 'Filter %s list', 'iwf2b' ), $args['label'] ),
					'items_list_navigation' => sprintf( _x( '%s list navigation', 'post', 'iwf2b' ), $args['label'] ),
					'items_list'            => sprintf( _x( '%s list', 'post', 'iwf2b' ), $args['label'] ),
				];
			}

			if ( is_array( $args['supports'] ) ) {
				if ( in_array( 'thumbnail', $args['supports'] ) ) {
					add_theme_support( 'post-thumbnails', [ static::$post_type ] );
				}
			}

			register_post_type( static::$post_type, $args );

		} else {
			add_post_type_support( static::$post_type, $args['supports'] );
		}
	}

	/**
	 * @param $use_block_editor
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function use_block_editor( $use_block_editor, $post_type ) {
		if ( static::$post_type && $post_type === static::$post_type ) {
			$supports = get_all_post_type_supports( $post_type );

			if ( ! empty( $supports['classic-editor'] ) ) {
				return false;
			}
		}

		return $use_block_editor;
	}

	/**
	 * @param $post_id
	 *
	 * @return \WP_Post|null
	 */
	public static function get( $post_id ) {
		if ( $post_id instanceof \WP_Post ) {
			$post = $post_id;

		} elseif ( is_numeric( $post_id ) && preg_match( '/^[0-9]+?$/', $post_id ) ) {
			$post = get_post( $post_id );

		} elseif ( is_string( $post_id ) ) {
			$post = get_page_by_path( $post_id, OBJECT, static::$post_type );

			if ( ! $post ) {
				$post = get_page_by_title( $post_id, OBJECT, static::$post_type );
			}
		}

		if ( ! $post || ( static::$post_type && $post->post_type !== static::$post_type ) ) {
			return null;
		}

		return $post;
	}

	/**
	 * @param $post_id
	 *
	 * @return int
	 */
	public static function get_id( $post_id ) {
		$post = static::get( $post_id );

		if ( ! $post ) {
			return 0;
		}

		return $post->ID;
	}

	/**
	 * @return string
	 */
	public static function get_post_type() {
		return static::$post_type;
	}

	/**
	 * Alias of static::get_post_type()
	 *
	 * @return string
	 */
	public static function get_slug() {
		return static::get_post_type();
	}

	/**
	 * @param $post_id
	 *
	 * @return bool
	 */
	public static function is_valid( $post_id ) {
		return static::get( $post_id ) ? true : false;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function create_args( array $args = [] ) {
		$args = array_merge( static::$find_args, $args );

		if ( static::$post_type ) {
			$args['post_type'] = static::$post_type;
		}

		// convert 'template' keyword to meta_query
		if ( isset( $args['template'] ) ) {
			$args['meta_query']['_wp_page_template'] = [
				'key'   => '_wp_page_template',
				'value' => $args['template'],
			];

			unset( $args['template'] );
		}

		// convert 'thumbnail' keyword to meta_query
		if ( isset( $args['thumbnail'] ) ) {
			if ( $args['thumbnail'] === true ) {
				$args['meta_query'][] = [
					'key' => '_thumbnail_id',
				];

			} elseif ( preg_match( '#^[\d]+?$#', $args['thumbnail'] ) ) {
				$args['meta_query']['_thumbnail_id'] = [
					'key'   => '_thumbnail_id',
					'value' => (int) $args['thumbnail'],
				];
			}

			unset( $args['thumbnail'] );
		}

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
	 * @param array $args
	 *
	 * @return \WP_Post|null
	 */
	public static function get_post( array $args = [] ) {
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
	 * @param string       $taxonomy
	 * @param array        $args
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
	 * @param string       $taxonomy
	 * @param array        $args
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
	 * @param int|\WP_Post $post_id
	 * @param bool|string  $search_post_key
	 * @param string       $dummy_image
	 *
	 * @return array
	 */
	public static function get_thumbnail( $post_id, $args = [], $deprecated = '' ) {
		$post = static::get( $post_id );

		if ( ! $post ) {
			return [];
		}

		if ( ! is_array( $args ) ) {
			$args = [
				'search_post_key' => $args,
				'dummy_image'     => $deprecated,
				'thumbnail_size'  => '',
				'alt'             => '',
				'get_size'        => false,
			];

		} else {
			$args = wp_parse_args( $args, [
				'search_post_key' => false,
				'dummy_image'     => '',
				'thumbnail_size'  => '',
				'alt'             => '',
				'get_size'        => false,
			] );
		}

		$data['src']    = $args['dummy_image'];
		$data['alt']    = $args['alt'];
		$data['width']  = null;
		$data['height'] = null;

		if ( has_post_thumbnail( $post->ID ) ) {
			$attachment_id = get_post_thumbnail_id( $post->ID );
			$attachment    = get_post( $attachment_id );

			if ( $attachment ) {
				$image_src = wp_get_attachment_image_src( $attachment->ID, $args['thumbnail_size'] );

				if ( $image_src ) {
					$data['src']    = $image_src[0];
					$data['width']  = $image_src[1];
					$data['height'] = $image_src[2];

					if ( ! $data['alt'] ) {
						$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );

						if ( empty( $alt ) ) {
							$alt = $attachment->post_excerpt;
						}

						if ( empty( $alt ) ) {
							$alt = $attachment->post_title;
						}

						$data['alt'] = trim( wp_strip_all_tags( $alt, true ) );
					}
				}
			}

		} elseif (
			$args['search_post_key']
			&& isset( $post->{$args['search_post_key']} )
			&& preg_match( '|<img[^>]*?src\s*=\s*["\']([^"\']+)["\'].*?>|i', $post->{$args['search_post_key']}, $src )
		) {
			$data['src'] = $src[1];

			if ( $args['get_size'] ) {
				$sizes = @getimagesize( $data['src'] );

				if ( isset( $sizes[0], $sizes[1] ) ) {
					$data['width']  = $sizes[0];
					$data['height'] = $sizes[1];
				}
			}

			if ( ! $data['alt'] && preg_match( '|\s*alt\s*=\s*["\']([^"\']+)["\']|i', $src[0], $alt ) ) {
				$data['alt'] = $alt[1];
			}

		} elseif ( $data['src'] ) {
			if ( $args['get_size'] ) {
				$sizes = @getimagesize( $data['src'] );

				if ( isset( $sizes[0], $sizes[1] ) ) {
					$data['width']  = $sizes[0];
					$data['height'] = $sizes[1];
				}
			}

		} else {
			return [];
		}

		return $data;
	}

	/**
	 * @param int|\WP_Post $post
	 * @param string       $key
	 * @param mixed        $args
	 *
	 * @return mixed
	 */
	public static function get_meta( $post_id, $key, $args = [] ) {
		$post = static::get( $post_id );

		if ( ! $post ) {
			return null;
		}

		if ( ! is_array( $args ) ) {
			$args = [ 'default' => $args ];
		}

		$acf_options = Arr::merge_intersect_key( [
			'noautop' => false,
			'raw'     => false,
		], (array) Arr::get( $args, 'acf', [] ) );

		unset( $args['acf'] );

		if ( $acf_options['noautop'] ) {
			remove_filter( 'acf_the_content', 'wpautop' );
		}

		$value = function_exists( 'get_field' ) ? get_field( $key, $post, ! $acf_options['raw'] ) : get_post_meta( $post->ID, $key, true );

		if ( $acf_options['noautop'] ) {
			add_filter( 'acf_the_content', 'wpautop' );
		}

		return Util::filter( $value, $args );
	}

	/**
	 * @param int|\WP_Post $post_id
	 * @param bool         $include_current
	 * @param bool         $reverse
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

	/**
	 * @param array $args
	 *
	 * @return int|\WP_Error
	 */
	public static function insert( array $args = [] ) {
		$args['post_type'] = static::$post_type;

		return wp_insert_post( $args, true );
	}

	/**
	 * @return false|string
	 */
	public static function get_archive_link() {
		return get_post_type_archive_link( static::$post_type );
	}

	/**
	 * @return \WP_Post_Type|null
	 */
	public static function get_post_type_object() {
		return get_post_type_object( static::$post_type );
	}
}
