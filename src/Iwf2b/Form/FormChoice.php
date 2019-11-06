<?php

namespace Iwf2b\Form;

use Iwf2b\Arr;
use Iwf2b\Html\Html;

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

		$value_only = $choices === array_values( $choices );
		$forms[]    = Form::hidden( $name ); // Add hidden input

		foreach ( $choices as $choice_value => $choice_key ) {
			if ( $value_only ) {
				$choice_value = $choice_key;
			}

			$forms[] = Form::checkbox( $name . '[]', $attrs )
			               ->set_value( $choice_value )
			               ->set_after_render( function ( &$html ) use ( $wrapper, $wrapper_attr, $between_text, $choice_key ) {
				               if ( $wrapper ) {
					               $html = Html::tag( $wrapper, $wrapper_attr, $html . $between_text . $choice_key, [ 'escape' => false ] );

				               } else {
					               $html = $html . $between_text . $choice_key;
				               }
			               } );
		}

		$forms->set_before_render( function ( FormRendererCollection $forms ) {
			if ( $forms->get_value() ) {
				$values = array_filter( (array) $forms->get_value() );

				foreach ( $forms as $form ) {
					$attrs            = $form->get_attrs();
					$attrs['checked'] = in_array( $form->get_value(), $values );
					$form->set_attrs( $attrs );
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

		$value_only = $choices === array_values( $choices );
		$forms[]    = Form::hidden( $name ); // Add hidden input

		foreach ( $choices as $choice_value => $choice_key ) {
			if ( $value_only ) {
				$choice_value = $choice_key;
			}

			$forms[] = Form::radio( $name, $attrs )
			               ->set_value( $choice_value )
			               ->set_after_render( function ( &$html ) use ( $wrapper, $wrapper_attr, $between_text, $choice_key ) {
				               if ( $wrapper ) {
					               $html = Html::tag( $wrapper, $wrapper_attr, $html . $between_text . $choice_key, [ 'escape' => false ] );

				               } else {
					               $html = $html . $between_text . $choice_key;
				               }
			               } );
		}

		$forms->set_before_render( function ( FormRendererCollection $forms ) {
			if ( $forms->get_value() ) {
				foreach ( $forms as $form ) {
					$attrs            = $form->get_attrs();
					$attrs['checked'] = $form->get_value() == $forms->get_value();
					$form->set_attrs( $attrs );
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
