<?php

namespace Iwf2b\Form;

/**
 * Class Form
 * @package Iwf2b\Form
 */
class Form {
	/**
	 * @param $name
	 * @param $type
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function input( $name, $type, array $attrs = [] ) {
		$attrs['type'] = $type;

		return ( new FormRenderer( 'input', $name ) )
			->set_attrs( $attrs )
			->set_before_render( function ( FormRenderer $form ) {
				if ( $form->get_value() ) {
					$attrs          = $form->get_attrs();
					$attrs['value'] = $form->get_value();
					$form->set_attrs( $attrs );
				}
			} );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function text( $name, array $attrs = [] ) {
		return static::input( $name, 'text', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function password( $name, array $attrs = [] ) {
		return static::input( $name, 'password', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function number( $name, array $attrs = [] ) {
		return static::input( $name, 'number', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function range( $name, array $attrs = [] ) {
		return static::input( $name, 'range', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function search( $name, array $attrs = [] ) {
		return static::input( $name, 'search', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function email( $name, array $attrs = [] ) {
		return static::input( $name, 'email', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function url( $name, array $attrs = [] ) {
		return static::input( $name, 'url', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function tel( $name, array $attrs = [] ) {
		return static::input( $name, 'tel', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function date( $name, array $attrs = [] ) {
		return static::input( $name, 'date', $attrs )->set_before_render( function ( FormRenderer $form ) {
			if ( $form->get_value() ) {
				if ( $form->get_value() instanceof \DateTime ) {
					$attrs          = $form->get_attrs();
					$attrs['value'] = $form->get_value()->format( 'Y-m-d' );
					$form->set_attrs( $attrs );
				}
			}
		} );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function time( $name, array $attrs = [] ) {
		return static::input( $name, 'time', $attrs )->set_before_render( function ( FormRenderer $form ) {
			if ( $form->get_value() ) {
				if ( $form->get_value() instanceof \DateTime ) {
					$attrs          = $form->get_attrs();
					$attrs['value'] = $form->get_value()->format( \DateTime::RFC3339 );

					$form->set_attrs( $attrs );
				}
			}
		} );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function week( $name, array $attrs = [] ) {
		return static::input( $name, 'week', $attrs )->set_before_render( function ( FormRenderer $form ) {
			if ( $form->get_value() ) {
				if ( $form->get_value() instanceof \DateTime ) {
					$attrs          = $form->get_attrs();
					$attrs['value'] = $form->get_value()->format( 'Y-\WW' );
					$form->set_attrs( $attrs );
				}
			}
		} );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function hidden( $name, array $attrs = [] ) {
		return static::input( $name, 'hidden', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function file( $name, array $attrs = [] ) {
		return static::input( $name, 'file', $attrs );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function textarea( $name, array $attrs = [] ) {
		return ( new FormRenderer( 'textarea', $name ) )
			->set_attrs( $attrs )
			->set_before_render( function ( FormRenderer $form ) {
				if ( $form->get_value() ) {
					$form->set_content( $form->get_value() );
				}
			} );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function button( $name, array $attrs = [] ) {
		return ( new FormRenderer( 'button', $name ) )
			->set_attrs( $attrs )
			->set_before_render( function ( FormRenderer $form ) {
				if ( $form->get_value() ) {
					$attrs          = $form->get_attrs();
					$attrs['value'] = $form->get_value();
					$form->set_attrs( $attrs );
				}
			} );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function radio( $name, array $attrs = [] ) {
		return static::input( $name, 'radio', $attrs )->set_before_render( function ( FormRenderer $form ) {
			$attrs = $form->get_attrs();

			if ( $form->get_value() ) {
				$attrs['value'] = $form->get_value();

			} else {
				$attrs['value'] = '';
			}

			$form->set_attrs( $attrs );
		} );
	}

	/**
	 * @param $name
	 * @param array $attrs
	 *
	 * @return FormRendererInterface
	 */
	public static function checkbox( $name, array $attrs = [] ) {
		return static::input( $name, 'checkbox', $attrs )->set_before_render( function ( FormRenderer $form ) {
			$attrs = $form->get_attrs();

			if ( $form->get_value() ) {
				$attrs['value'] = $form->get_value();

			} else {
				$attrs['value'] = '';
			}

			$form->set_attrs( $attrs );
		} );
	}
}
