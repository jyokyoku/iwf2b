<?php

namespace Iwf2b\Tax;

use Iwf2b\AbstractSingleton;
use Iwf2b\DefineMetaTrait;
use Iwf2b\Post\AbstractPost;

/**
 * Class AbstractTax
 *
 * @package Iwf2b\Tax
 */
abstract class AbstractTax extends AbstractSingleton {
	use DefineMetaTrait;

	/**
	 * Taxonomy slug
	 *
	 * @var string
	 */
	protected $taxonomy;

	/**
	 * Assoc object slug
	 *
	 * @var string
	 */
	protected $object_type = '';

	/**
	 * Params for registration
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Search conditions
	 *
	 * @var array
	 */
	protected $find_args = [];

	/**
	 * Builtin taxonomy
	 *
	 * @var bool
	 */
	protected $builtin = false;

	/**
	 * Insert the default metas when term added
	 *
	 * @var bool
	 */
	protected $insert_default_meta = true;

	/**
	 * {@inheritdoc}
	 */
	protected function initialize() {
		if ( ! $this->taxonomy ) {
			throw new \RuntimeException( sprintf( 'The variable "%s::$taxonomy" must be not empty.', get_class( $this ) ) );
		}

		add_action( 'init', [ $this, 'register_taxonomy' ] );
		add_action( 'create_term', [ $this, 'insert_default_meta' ], 1, 3 );
	}

	/**
	 * Register taxonomy
	 */
	public function register_taxonomy() {
		if ( ! $this->builtin ) {
			$object_types = array_values( (array) $this->object_type );

			foreach ( $object_types as $i => $object_type ) {
				if ( class_exists( $object_type ) ) {
					if ( ! is_subclass_of( $object_type, AbstractPost::class ) ) {
						throw new \InvalidArgumentException( sprintf( 'The variable "%s::$object_type" must be child class of AbstractPost.', get_class( $this ) ) );
					}

					$object_types[ $i ] = call_user_func( [ $object_type, 'get_slug' ] );
				}
			}

			if ( ! empty( $this->args['label'] ) && empty( $this->args['labels'] ) ) {
				$this->args['labels'] = [
					'name'                       => $this->args['label'],
					'singular_name'              => $this->args['label'],
					'search_items'               => sprintf( _x( 'Search %s', 'tax', 'iwf2b' ), $this->args['label'] ),
					'popular_items'              => sprintf( __( 'Popular %s', 'iwf2b' ), $this->args['label'] ),
					'all_items'                  => sprintf( _x( 'All %s', 'tax', 'iwf2b' ), $this->args['label'] ),
					'parent_item'                => sprintf( __( 'Parent %s', 'iwf2b' ), $this->args['label'] ),
					'parent_item_colon'          => sprintf( _x( 'Parent %s:', 'tax', 'iwf2b' ), $this->args['label'] ),
					'edit_item'                  => sprintf( _x( 'Edit %s', 'tax', 'iwf2b' ), $this->args['label'] ),
					'view_item'                  => sprintf( _x( 'View %s', 'tax', 'iwf2b' ), $this->args['label'] ),
					'update_item'                => sprintf( __( 'Update %s', 'iwf2b' ), $this->args['label'] ),
					'add_new_item'               => sprintf( _x( 'Add New %s', 'tax', 'iwf2b' ), $this->args['label'] ),
					'new_item_name'              => sprintf( __( 'New %s Name', 'iwf2b' ), $this->args['label'] ),
					'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'iwf2b' ), $this->args['label'] ),
					'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'iwf2b' ), $this->args['label'] ),
					'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'iwf2b' ), $this->args['label'] ),
					'not_found'                  => sprintf( _x( 'No %s found.', 'tax', 'iwf2b' ), $this->args['label'] ),
					'no_terms'                   => sprintf( __( 'No %s', 'iwf2b' ), $this->args['label'] ),
					'items_list_navigation'      => sprintf( _x( '%s list navigation', 'tax', 'iwf2b' ), $this->args['label'] ),
					'items_list'                 => sprintf( _x( '%s list', 'tax', 'iwf2b' ), $this->args['label'] ),
					'back_to_items'              => sprintf( __( '&larr; Back to %s', 'iwf2b' ), $this->args['label'] ),
				];
			}

			register_taxonomy( $this->taxonomy, $object_types, $this->args );
		}
	}

	/**
	 * @param int $term_id
	 * @param int $tt_id
	 * @param string $taxonomy
	 */
	public function insert_default_meta( $term_id, $tt_id, $taxonomy ) {
		if ( ! $this->insert_default_meta || $taxonomy !== static::get_slug() ) {
			return;
		}

		foreach ( $this->get_meta_defines() as $meta_key ) {
			if ( ! metadata_exists( 'term', $term_id, $meta_key ) ) {
				add_term_meta( $term_id, $meta_key, '' );
			}
		}
	}

	/**
	 * @return string
	 */
	public static function get_taxonomy() {
		return static::get_instance()->taxonomy;
	}

	/**
	 * Alias of static::get_taxonomy()
	 *
	 * @return string
	 */
	public static function get_slug() {
		return static::get_taxonomy();
	}

	/**
	 * @return string
	 */
	public static function get_object_type() {
		return static::get_instance()->object_type;
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
		$self        = static::get_instance();
		$term_object = false;

		if ( $term_id instanceof \WP_Term ) {
			$term_object = $term_id;

		} elseif ( is_numeric( $term_id ) && preg_match( '/^[0-9]+?$/', $term_id ) ) {
			$term_object = get_term( (int) $term_id );

		} elseif ( is_string( $term_id ) ) {
			$term_object = get_term_by( 'slug', $term_id, $self->taxonomy );

			if ( ! $term_object ) {
				$term_object = get_term_by( 'name', $term_id, $self->taxonomy );
			}
		}

		if ( is_wp_error( $term_object ) || ! $term_object || ( $self->taxonomy && $term_object->taxonomy !== $self->taxonomy ) ) {
			return false;
		}

		return $term_object;
	}

	/**
	 * @param $term_id
	 *
	 * @return int
	 */
	public static function get_id( $term_id ) {
		$term = static::get( $term_id );

		if ( ! $term ) {
			return 0;
		}

		return $term->term_id;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public static function create_args( array $args = [] ) {
		$self = static::get_instance();
		$args = array_merge( $self->find_args, $args );

		if ( $self->taxonomy ) {
			$args['taxonomy'] = $self->taxonomy;
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
	 * @param      $term_id
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
	 * @param string $name
	 * @param array $args
	 *
	 * @return array|\WP_Error
	 */
	public static function insert( $name, array $args = [] ) {
		return wp_insert_term( $name, static::get_instance()->taxonomy, $args );
	}

	/**
	 * @param array $args
	 * @param string $default
	 *
	 * @return string
	 */
	public static function get_term_link( array $args = [], $default = '' ) {
		$args['hide_empty'] = false;

		$term = static::get_term( $args );

		if ( $term ) {
			return get_term_link( $term );
		}

		return $default;
	}
}
