<?php

namespace Iwf2b\Tax;

use Iwf2b\AbstractSingleton;
use Iwf2b\Arr;
use Iwf2b\Util;

/**
 * Class AbstractTax
 * @package Iwf2b\Tax
 */
abstract class AbstractTax extends AbstractSingleton {
	/**
	 * Assoc object slug
	 *
	 * @var string
	 */
	protected static $object_type = '';

	/**
	 * Taxonomy slug
	 *
	 * @var string
	 */
	protected static $taxonomy = '';

	/**
	 * Params for registration
	 *
	 * @var array
	 */
	protected static $args = [];

	/**
	 * Search conditions
	 *
	 * @var array
	 */
	protected static $find_args = [];

	/**
	 * Builtin taxonomy
	 *
	 * @var bool
	 */
	protected static $builtin = false;

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		if ( ! static::$taxonomy ) {
			throw new \RuntimeException( sprintf( 'The variable "%s::$taxonomy" must be not empty.', get_class( $this ) ) );
		}

		add_action( 'init', [ $this, 'register_taxonomy' ] );
	}

	/**
	 * Register taxonomy
	 */
	public function register_taxonomy() {
		if ( ! static::$builtin ) {
			if ( ! empty( $args['label'] ) && empty( $args['labels'] ) ) {
				static::$args['labels'] = [
					'name'                       => static::$args['label'],
					'singular_name'              => static::$args['label'],
					'search_items'               => sprintf( _x( 'Search %s', 'tax', 'iwf2b' ), static::$args['label'] ),
					'popular_items'              => sprintf( __( 'Popular %s', 'iwf2b' ), static::$args['label'] ),
					'all_items'                  => sprintf( _x( 'All %s', 'tax', 'iwf2b' ), static::$args['label'] ),
					'parent_item'                => sprintf( __( 'Parent %s', 'iwf2b' ), static::$args['label'] ),
					'parent_item_colon'          => sprintf( _x( 'Parent %s:', 'tax', 'iwf2b' ), static::$args['label'] ),
					'edit_item'                  => sprintf( _x( 'Edit %s', 'tax', 'iwf2b' ), static::$args['label'] ),
					'view_item'                  => sprintf( _x( 'View %s', 'tax', 'iwf2b' ), static::$args['label'] ),
					'update_item'                => sprintf( __( 'Update %s', 'iwf2b' ), static::$args['label'] ),
					'add_new_item'               => sprintf( _x( 'Add New %s', 'tax', 'iwf2b' ), static::$args['label'] ),
					'new_item_name'              => sprintf( __( 'New %s Name', 'iwf2b' ), static::$args['label'] ),
					'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'iwf2b' ), static::$args['label'] ),
					'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'iwf2b' ), static::$args['label'] ),
					'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'iwf2b' ), static::$args['label'] ),
					'not_found'                  => sprintf( _x( 'No %s found.', 'tax', 'iwf2b' ), static::$args['label'] ),
					'no_terms'                   => sprintf( __( 'No %s', 'iwf2b' ), static::$args['label'] ),
					'items_list_navigation'      => sprintf( _x( '%s list navigation', 'tax', 'iwf2b' ), static::$args['label'] ),
					'items_list'                 => sprintf( _x( '%s list', 'tax', 'iwf2b' ), static::$args['label'] ),
					'back_to_items'              => sprintf( __( '&larr; Back to %s', 'iwf2b' ), static::$args['label'] ),
				];
			}

			register_taxonomy( static::$taxonomy, static::$object_type, static::$args );
		}
	}

	/**
	 * @return string
	 */
	public static function get_slug() {
		return static::$taxonomy;
	}

	/**
	 * @return string
	 */
	public static function get_object_type() {
		return static::$object_type;
	}

	/**
	 * @param $term_id
	 *
	 * @return bool
	 */
	public static function is_valid( $term_id ) {
		return static::get( $term_id ) ? true : false;
	}

	/**
	 * @param int|string|\WP_Term $term_id
	 *
	 * @return bool|\WP_Term
	 */
	public static function get( $term_id ) {
		$term_object = false;

		if ( $term_id instanceof \WP_Term ) {
			$term_object = $term_id;

		} else if ( static::$taxonomy ) {
			if ( is_numeric( $term_id ) ) {
				$term_object = get_term_by( 'id', (int) $term_id, static::$taxonomy );

			} else if ( is_string( $term_id ) ) {
				$term_object = get_term_by( 'slug', $term_id, static::$taxonomy );
			}
		}

		if ( ! $term_object || ( static::$taxonomy && $term_object->taxonomy !== static::$taxonomy ) ) {
			return false;
		}

		return $term_object;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function create_args( array $args = [] ) {
		$args = array_merge( static::$find_args, $args );

		if ( static::$taxonomy ) {
			$args['taxonomy'] = static::$taxonomy;
		}

		return $args;
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_Term[]
	 */
	public static function get_terms( array $args = [] ) {
		$terms = get_terms( static::create_args( $args ) );

		if ( is_wp_error( $terms ) ) {
			return [];
		}

		return $terms;
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_Term|null
	 */
	public static function get_term( array $args = [] ) {
		$terms = static::get_terms( $args );

		if ( $terms ) {
			return reset( $terms );
		}

		return null;
	}

	/**
	 * @param array $args
	 *
	 * @return \WP_Term_Query
	 */
	public static function get_query( array $args = [] ) {
		return new \WP_Term_Query( static::create_args( $args ) );
	}

	/**
	 * @param $term_id
	 * @param bool $include_current
	 * @param bool $reverse
	 *
	 * @return array
	 */
	public static function get_parents( $term_id, $include_current = false, $reverse = false ) {
		$term = static::get( $term_id );

		if ( ! $term ) {
			return [];
		}

		$tree = $include_current ? [ $term ] : [];

		if ( $term->parent ) {
			$tmp_term = $term;

			while ( $tmp_term->parent ) {
				$tmp_term = get_term( (int) $tmp_term->parent, $tmp_term->taxonomy );

				if ( ! $tmp_term || is_wp_error( $tmp_term ) ) {
					break;
				}

				$tree[] = $tmp_term;
			}
		}

		return $reverse ? $tree : array_reverse( $tree );
	}

	/**
	 * @param int|string|\WP_Term $term_id
	 * @param string $key
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public static function get_meta( $term_id, $key, $args = [] ) {
		$term = static::get( $term_id );

		if ( ! $term ) {
			return null;
		}

		if ( ! is_array( $args ) ) {
			$args = [ 'd' => $args ];
		}

		$acf_options = Arr::merge_intersect_key( [
			'noautop' => false,
			'raw'     => false,
		], (array) Arr::get( $args, 'acf', [] ) );

		unset( $args['acf'] );

		if ( $acf_options['noautop'] ) {
			remove_filter( 'acf_the_content', 'wpautop' );
		}

		$value = function_exists( 'get_field' ) ? get_field( $key, $term, ! $acf_options['raw'] ) : get_term_meta( $term->term_id, $key, true );

		if ( $acf_options['noautop'] ) {
			add_filter( 'acf_the_content', 'wpautop' );
		}

		return Util::filter( $value, $args );
	}

	/**
	 * @param string $name
	 * @param array $args
	 *
	 * @return array|\WP_Error
	 */
	public static function insert( $name, array $args = [] ) {
		return wp_insert_term( $name, static::$taxonomy, $args );
	}
}