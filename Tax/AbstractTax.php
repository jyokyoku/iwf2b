<?php

namespace Iwf2b\Tax;

use Iwf2b\AbstractSingleton;

abstract class AbstractTax extends AbstractSingleton {
	protected static $object_type = '';

	protected static $taxonomy = '';

	protected static $args = [];

	protected static $find_args = [];

	protected static $builtin = false;

	protected function initialize() {
		add_action( 'init', [ $this, 'register_taxonomy' ] );
	}

	public function register_taxonomy() {
		if ( ! static::$builtin ) {
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
	 * @param int|string|array $term_id
	 *
	 * @return bool|\WP_Term
	 */
	public static function get( $term_id ) {
		$term_object = false;

		if ( $term_id instanceof \WP_Term ) {
			$term_object = $term_id;

		} else if ( is_numeric( $term_id ) ) {
			$term_object = get_term_by( 'id', (int) $term_id, static::$taxonomy );

		} else if ( is_string( $term_id ) ) {
			$term_object = get_term_by( 'slug', $term_id, static::$taxonomy );
		}

		if ( ! $term_object || $term_object->taxonomy !== static::$taxonomy ) {
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
		$args             = array_merge( static::$find_args, $args );
		$args['taxonomy'] = static::$taxonomy;

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
}