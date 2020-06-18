<?php

namespace Iwf2b\Tests\TestCase\Form;

use Iwf2b\Form\Form;
use Iwf2b\Form\FormChoice;

class FormChoiceTest extends \WP_UnitTestCase {
	public function test_checkboxes() {
		$html = (string) FormChoice::checkboxes( 'test_1', [ 'value_1' => 'label_1', 'value_2' => 'label_2' ], [ 'id' => 'test_1' ] );
		$this->assertEquals( <<< EOF
<input id="test_1-0" name="test_1" type="hidden" value="">
<label><input data-label="label_1" id="test_1-1" name="test_1[]" type="checkbox" value="value_1">label_1</label>
<label><input data-label="label_2" id="test_1-2" name="test_1[]" type="checkbox" value="value_2">label_2</label>
EOF
			, $html );

		$html = (string) FormChoice::checkboxes( 'test_2', [ 'value_3', 'value_4' ], [ 'id' => 'test_2' ] );
		$this->assertEquals( <<< EOF
<input id="test_2-0" name="test_2" type="hidden" value="">
<label><input data-label="value_3" id="test_2-1" name="test_2[]" type="checkbox" value="value_3">value_3</label>
<label><input data-label="value_4" id="test_2-2" name="test_2[]" type="checkbox" value="value_4">value_4</label>
EOF
			, $html );

		$html = (string) FormChoice::checkboxes( 'test_3', [ 'value_5' => 'label_5', 'value_6' => 'label_6' ], [ 'id' => 'test_3' ] )->set_value( 'invalid_value' );
		$this->assertEquals( <<< EOF
<input id="test_3-0" name="test_3" type="hidden" value="">
<label><input data-label="label_5" id="test_3-1" name="test_3[]" type="checkbox" value="value_5">label_5</label>
<label><input data-label="label_6" id="test_3-2" name="test_3[]" type="checkbox" value="value_6">label_6</label>
EOF
			, $html );

		$html = (string) FormChoice::checkboxes( 'test_4', [ 'value_7' => 'label_7', 'value_8' => 'label_8' ], [ 'id' => 'test_4' ] )->set_value( 'value_7' );
		$this->assertEquals( <<< EOF
<input id="test_4-0" name="test_4" type="hidden" value="">
<label><input checked data-label="label_7" id="test_4-1" name="test_4[]" type="checkbox" value="value_7">label_7</label>
<label><input data-label="label_8" id="test_4-2" name="test_4[]" type="checkbox" value="value_8">label_8</label>
EOF
			, $html );

		$html = (string) FormChoice::checkboxes( 'test_5', [ 'value_9' => 'label_9', 'value_10' => 'label_10' ], [ 'id' => 'test_5' ] )->set_value( [ 'value_9', 'value_10' ] );
		$this->assertEquals( <<< EOF
<input id="test_5-0" name="test_5" type="hidden" value="">
<label><input checked data-label="label_9" id="test_5-1" name="test_5[]" type="checkbox" value="value_9">label_9</label>
<label><input checked data-label="label_10" id="test_5-2" name="test_5[]" type="checkbox" value="value_10">label_10</label>
EOF
			, $html );
	}

	public function test_checkboxes_custom_render() {
		$form = FormChoice::checkboxes( 'test_1', [ 'value_1' => 'label_1', 'value_2' => 'label_2' ], [ 'id' => 'test_1' ] );
		$html = $form->render( function ( $forms ) {
			$tags = [];

			foreach ( $forms as $form ) {
				$attrs = $form->get_attrs();

				if ( $attrs['type'] === 'checkbox' ) {
					$tags[] = '<label>' . Form::checkbox( $form->get_name(), $attrs ) . ' - ' . $attrs['data-label'] . '</label>';

				} else {
					$tags[] = (string) $form;
				}
			}

			return implode( "\n", $tags );
		} );

		$this->assertEquals( <<< EOF
<input id="test_1-0" name="test_1" type="hidden" value="">
<label><input data-label="label_1" id="test_1-1" name="test_1[]" type="checkbox" value="value_1"> - label_1</label>
<label><input data-label="label_2" id="test_1-2" name="test_1[]" type="checkbox" value="value_2"> - label_2</label>
EOF
			, $html );
	}

	public function test_radios() {
		$html = (string) FormChoice::radios( 'test_1', [ 'value_1' => 'label_1', 'value_2' => 'label_2' ], [ 'id' => 'test_1' ] );
		$this->assertEquals( <<< EOF
<input id="test_1-0" name="test_1" type="hidden" value="">
<label><input data-label="label_1" id="test_1-1" name="test_1" type="radio" value="value_1">label_1</label>
<label><input data-label="label_2" id="test_1-2" name="test_1" type="radio" value="value_2">label_2</label>
EOF
			, $html );

		$html = (string) FormChoice::radios( 'test_2', [ 'value_3', 'value_4' ], [ 'id' => 'test_2' ] );
		$this->assertEquals( <<< EOF
<input id="test_2-0" name="test_2" type="hidden" value="">
<label><input data-label="value_3" id="test_2-1" name="test_2" type="radio" value="value_3">value_3</label>
<label><input data-label="value_4" id="test_2-2" name="test_2" type="radio" value="value_4">value_4</label>
EOF
			, $html );

		$html = (string) FormChoice::radios( 'test_3', [ 'value_5' => 'label_5', 'value_6' => 'label_6' ], [ 'id' => 'test_3' ] )->set_value( 'invalid_value' );
		$this->assertEquals( <<< EOF
<input id="test_3-0" name="test_3" type="hidden" value="">
<label><input data-label="label_5" id="test_3-1" name="test_3" type="radio" value="value_5">label_5</label>
<label><input data-label="label_6" id="test_3-2" name="test_3" type="radio" value="value_6">label_6</label>
EOF
			, $html );

		$html = (string) FormChoice::radios( 'test_4', [ 'value_7' => 'label_7', 'value_8' => 'label_8' ], [ 'id' => 'test_4' ] )->set_value( 'value_7' );
		$this->assertEquals( <<< EOF
<input id="test_4-0" name="test_4" type="hidden" value="">
<label><input checked data-label="label_7" id="test_4-1" name="test_4" type="radio" value="value_7">label_7</label>
<label><input data-label="label_8" id="test_4-2" name="test_4" type="radio" value="value_8">label_8</label>
EOF
			, $html );

		$html = (string) FormChoice::radios( 'test_5', [ 'value_9' => 'label_9', 'value_10' => 'label_10' ], [ 'id' => 'test_5' ] )->set_value( [ 'value_9', 'value_10' ] );
		$this->assertEquals( <<< EOF
<input id="test_5-0" name="test_5" type="hidden" value="">
<label><input data-label="label_9" id="test_5-1" name="test_5" type="radio" value="value_9">label_9</label>
<label><input data-label="label_10" id="test_5-2" name="test_5" type="radio" value="value_10">label_10</label>
EOF
			, $html );
	}

	public function test_radios_custom_render() {
		$form = FormChoice::radios( 'test_1', [ 'value_1' => 'label_1', 'value_2' => 'label_2' ], [ 'id' => 'test_1' ] );
		$html = $form->render( function ( $forms ) {
			$tags = [];

			foreach ( $forms as $form ) {
				$attrs = $form->get_attrs();

				if ( $attrs['type'] === 'radio' ) {
					$tags[] = '<label>' . Form::radio( $form->get_name(), $attrs ) . ' - ' . $attrs['data-label'] . '</label>';

				} else {
					$tags[] = (string) $form;
				}
			}

			return implode( "\n", $tags );
		} );

		$this->assertEquals( <<< EOF
<input id="test_1-0" name="test_1" type="hidden" value="">
<label><input data-label="label_1" id="test_1-1" name="test_1" type="radio" value="value_1"> - label_1</label>
<label><input data-label="label_2" id="test_1-2" name="test_1" type="radio" value="value_2"> - label_2</label>
EOF
			, $html );
	}
}