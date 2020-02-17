<?php

namespace Iwf2b\Tests\TestCase\Form;

use Iwf2b\Form\Form;

class FormTest extends \WP_UnitTestCase {
	public function test_checkbox_base() {
		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="checkbox">', $html );

		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'checked' => true ] );
		$this->assertEquals( '<input checked class="form_class" id="form_id" name="test" type="checkbox">', $html );
	}

	/**
	 * @depends test_checkbox_base
	 */
	public function test_checkbox_set_value_attr() {
		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => 'test_value' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="checkbox" value="test_value">', $html );
	}

	/**
	 * @depends test_checkbox_base
	 */
	public function test_checkbox_set_empty_value_attr() {
		// Set empty value '0'
		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '0' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="checkbox" value="0">', $html );

		// Set empty value ''
		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="checkbox">', $html );
	}

	/**
	 * @depends test_checkbox_set_value_attr
	 */
	public function test_checkbox_set_value_attr_and_set_same_value() {
		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => 'test_value' ] )->set_value( 'test_value' );
		$this->assertEquals( '<input checked class="form_class" id="form_id" name="test" type="checkbox" value="test_value">', $html );
	}

	/**
	 * @depends test_checkbox_set_value_attr
	 */
	public function test_checkbox_set_empty_value_attr_and_set_same_value() {
		// Set empty value '0'
		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '0' ] )->set_value( '0' );
		$this->assertEquals( '<input checked class="form_class" id="form_id" name="test" type="checkbox" value="0">', $html );

		// Set empty value ''
		$html = (string) Form::checkbox( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '' ] )->set_value( '' );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="checkbox">', $html );
	}

	public function test_radio_base() {
		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="radio">', $html );

		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'checked' => true ] );
		$this->assertEquals( '<input checked class="form_class" id="form_id" name="test" type="radio">', $html );
	}

	/**
	 * @depends test_radio_base
	 */
	public function test_radio_set_value_attr() {
		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => 'test_value' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="radio" value="test_value">', $html );
	}

	/**
	 * @depends test_radio_base
	 */
	public function test_radio_set_empty_value_attr() {
		// Set empty value '0'
		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '0' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="radio" value="0">', $html );

		// Set empty value ''
		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '' ] );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="radio">', $html );
	}

	/**
	 * @depends test_radio_set_empty_value_attr
	 */
	public function test_radio_set_value_attr_and_set_same_value() {
		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => 'test_value' ] )->set_value( 'test_value' );
		$this->assertEquals( '<input checked class="form_class" id="form_id" name="test" type="radio" value="test_value">', $html );
	}

	/**
	 * @depends test_radio_set_empty_value_attr
	 */
	public function test_radio_set_empty_attr_value_and_set_same_value() {
		// Set empty value '0'
		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '0' ] )->set_value( '0' );
		$this->assertEquals( '<input checked class="form_class" id="form_id" name="test" type="radio" value="0">', $html );

		// Set empty value ''
		$html = (string) Form::radio( 'test', [ 'id' => 'form_id', 'class' => 'form_class', 'value' => '' ] )->set_value( '' );
		$this->assertEquals( '<input class="form_class" id="form_id" name="test" type="radio">', $html );
	}
}