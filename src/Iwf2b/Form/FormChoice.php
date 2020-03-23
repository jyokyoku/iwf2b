<?php

namespace Iwf2b\Form;

use Iwf2b\Arr;
use Iwf2b\Html\Html;
use Iwf2b\Util;

/**
 * Class FormChoice
 * @package Iwf2b\Form
 */
class FormChoice {
	/**
	 * @param $name
	 * @param array $choices
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function checkboxes( $name, array $choices, array $attrs = [] ) {
		$forms = new FormRendererCollection();

		// Extract wrapper attributes.
		$wrapper      = Arr::get( $attrs, 'wrapper', 'label' );
		$wrapper_attr = array_filter( (array) Arr::get( $attrs, 'wrapper_attr' ) );
		$between_text = Arr::get( $attrs, 'between_text' );
		unset( $attrs['wrapper'], $attrs['wrapper_attr'], $attrs['separator'] );

		$value_only   = $choices === array_values( $choices );
		$hidden_attrs = [ 'value' => '' ];

		if ( isset( $attrs['id'] ) ) {
			$hidden_attrs['id'] = $attrs['id'] . '-0';
		}

		$forms[] = Form::hidden( $name, $hidden_attrs ); // Add hidden input
		$count   = 1;

		foreach ( $choices as $choice_value => $choice_key ) {
			$_attrs = $attrs;

			if ( $value_only ) {
				$choice_value = $choice_key;
			}

			$_attrs['value'] = $choice_value;

			if ( isset( $attrs['id'] ) ) {
				$_attrs['id'] = $attrs['id'] . '-' . $count;
			}

			$forms[] = Form::checkbox( $name . '[]', $_attrs )
			               ->set_after_render( function ( &$html ) use ( $wrapper, $wrapper_attr, $between_text, $choice_key ) {
				               if ( $wrapper ) {
					               $html = Html::tag( $wrapper, $wrapper_attr, $html . $between_text . $choice_key, [ 'escape' => false ] );

				               } else {
					               $html = $html . $between_text . $choice_key;
				               }
			               } );

			$count ++;
		}

		$forms->set_before_render( function ( FormRendererCollection $forms ) {
			$values = array_filter( (array) $forms->get_value() );

			if ( ! Util::is_empty( $values ) ) {
				foreach ( $forms as $i => $form ) {
					if ( $i === 0 ) {
						continue; // Skip hidden input
					}

					$attrs = $form->get_attrs();

					if ( in_array( $attrs['value'], $values ) ) {
						$form->set_value( $attrs['value'] );
					}
				}
			}
		} );

		return $forms;
	}

	/**
	 * @param $name
	 * @param array $choices
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function radios( $name, array $choices, array $attrs = [] ) {
		$forms = new FormRendererCollection();

		// Extract wrapper attributes.
		$wrapper      = Arr::get( $attrs, 'wrapper', 'label' );
		$wrapper_attr = array_filter( (array) Arr::get( $attrs, 'wrapper_attr' ) );
		$between_text = Arr::get( $attrs, 'between_text' );
		unset( $attrs['wrapper'], $attrs['wrapper_attr'], $attrs['separator'] );

		$value_only   = $choices === array_values( $choices );
		$hidden_attrs = [ 'value' => '' ];

		if ( isset( $attrs['id'] ) ) {
			$hidden_attrs['id'] = $attrs['id'] . '-0';
		}

		$forms[] = Form::hidden( $name, $hidden_attrs ); // Add hidden input
		$count   = 1;

		foreach ( $choices as $choice_value => $choice_key ) {
			$_attrs = $attrs;

			if ( $value_only ) {
				$choice_value = $choice_key;
			}

			$_attrs['value'] = $choice_value;

			if ( isset( $attrs['id'] ) ) {
				$_attrs['id'] = $attrs['id'] . '-' . $count;
			}

			$forms[] = Form::radio( $name, $_attrs )
			               ->set_after_render( function ( &$html ) use ( $wrapper, $wrapper_attr, $between_text, $choice_key ) {
				               if ( $wrapper ) {
					               $html = Html::tag( $wrapper, $wrapper_attr, $html . $between_text . $choice_key, [ 'escape' => false ] );

				               } else {
					               $html = $html . $between_text . $choice_key;
				               }
			               } );

			$count ++;
		}

		$forms->set_before_render( function ( FormRendererCollection $forms ) {
			foreach ( $forms as $i => $form ) {
				if ( $i === 0 ) {
					continue;
				}

				$form->set_value( $forms->get_value() );
			}
		} );


		return $forms;
	}

	/**
	 * @param $name
	 * @param array $choices
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function select( $name, array $choices, array $attrs = [] ) {
		return ( new FormRenderer( 'select', $name ) )
			->set_attrs( $attrs )
			->set_before_render( function ( FormRenderer $form ) use ( $choices ) {
				$options    = [];
				$value_only = $choices === array_values( $choices );

				foreach ( $choices as $choice_value => $choice_key ) {
					if ( $value_only ) {
						$choice_value = $choice_key;
					}

					$option_attrs = [ 'value' => $choice_value ];

					if ( $form->get_value() == $choice_value ) {
						$option_attrs['selected'] = true;
					}

					$options[] = Html::tag( 'option', $option_attrs, $choice_key );
				}

				$form->set_content( implode( "\n", $options ) );
			} );
	}
}
