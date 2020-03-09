<?php

namespace Theme\Field\Rule;

use Iwf2b\Field\Field;
use Iwf2b\Field\FieldInterface;
use Iwf2b\Field\FieldSet;
use Iwf2b\Field\Rule\Exception\MissingRequiredParamsException;
use Iwf2b\Field\Rule\RetypeRule;

class RetypeRuleTest extends \WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();

		\Mockery::close();
	}

	public function test_constructor_required_params() {
		$this->expectException( MissingRequiredParamsException::class );
		$this->expectExceptionMessage( 'The params "fieldset", "field" must be set for ' . RetypeRule::class );

		new RetypeRule();
	}

	public function test_constructor_fieldset_param_type() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The param "fieldset" must be "' . FieldSet::class . '" type' );

		new RetypeRule( [ 'fieldset' => 'dummy_value' ] );
	}

	public function test_constructor_field_param_type() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The param "field" must be "string" or "' . FieldInterface::class . '" type' );

		new RetypeRule( [ 'field' => 10 ] );
	}

	public function test_do_validate_with_field_object() {
		$fieldset = \Mockery::mock( FieldSet::class )->makePartial();
		$field    = \Mockery::mock( FieldInterface::class );

		$fieldset->shouldReceive( 'offsetGet' )->andReturn( $field );
		$field->shouldReceive( 'get_value' )->andReturn( 'same value' );

		$rule = new RetypeRule( [ 'fieldset' => $fieldset, 'field' => $field ] );

		// Check same value
		$rule->set_value( 'same value' );
		$this->assertTrue( $rule->validate() );

		// Check not same value
		$rule->set_value( 'not same value' );
		$this->assertFalse( $rule->validate() );
	}

	public function test_do_validate_with_field_name() {
		$fieldset = \Mockery::mock( FieldSet::class )->makePartial();
		$field    = \Mockery::mock( FieldInterface::class );

		$fieldset->shouldReceive( 'offsetExists' )->andReturnUsing( function ( $index ) {
			return $index === 'test_field';
		} );

		$fieldset->shouldReceive( 'offsetGet' )->andReturnUsing( function ( $index ) use ( $field ) {
			return $index === 'test_field' ? $field : null;
		} );

		$field->shouldReceive( 'get_value' )->andReturn( 'same value' );

		// Check field exists
		$rule = new RetypeRule( [ 'fieldset' => $fieldset, 'field' => 'test_field' ] );
		$rule->set_value( 'same value' );

		$this->assertTrue( $rule->validate() );

		// Check field not exists
		$this->expectException( \UnexpectedValueException::class );
		$this->expectExceptionMessage( 'The field must be the registered field or field name.' );

		$rule = new RetypeRule( [ 'fieldset' => $fieldset, 'field' => 'test_field_2' ] );
		$rule->set_value( 'dummy value' );

		$this->assertTrue( $rule->validate() );
	}

	/**
	 * @depends test_do_validate_with_field_object
	 */
	public function test_do_validate_strict_value() {
		$fieldset = \Mockery::mock( FieldSet::class )->makePartial();
		$field    = \Mockery::mock( FieldInterface::class );

		$fieldset->shouldReceive( 'offsetGet' )->andReturn( $field );
		$field->shouldReceive( 'get_value' )->andReturn( 10 );

		$rule_1 = new RetypeRule( [ 'fieldset' => $fieldset, 'field' => $field, 'strict' => false ] );

		// Check not strict
		$rule_1->set_value( 10 );
		$this->assertTrue( $rule_1->validate() );

		$rule_1->set_value( '10' );
		$this->assertTrue( $rule_1->validate() );

		$rule_2 = new RetypeRule( [ 'fieldset' => $fieldset, 'field' => $field, 'strict' => true ] );

		// Check strict
		$rule_2->set_value( 10 );
		$this->assertTrue( $rule_2->validate() );

		$rule_2->set_value( '10' );
		$this->assertFalse( $rule_2->validate() );
	}
}