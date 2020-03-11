<?php

namespace Theme\Field\Rule;

use Iwf2b\Field\FieldInterface;
use Iwf2b\Field\FieldSet;
use Iwf2b\Field\Rule\Exception\MissingRequiredParamsException;
use Iwf2b\Field\Rule\RequiredIfRule;

class RequiredIfRuleTest extends \WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();

		\Mockery::close();
	}

	public function test_constructor_required_params() {
		$this->expectException( MissingRequiredParamsException::class );
		$this->expectExceptionMessage( 'The params "field" must be set for ' . RequiredIfRule::class );

		new RequiredIfRule();
	}

	public function test_constructor_fieldset_param_type() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The param "fieldset" must be "' . FieldSet::class . '" type' );

		new RequiredIfRule( [ 'fieldset' => 'dummy_value' ] );
	}

	public function test_constructor_field_param_type() {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The param "field" must be "string" or "' . FieldInterface::class . '" type' );

		new RequiredIfRule( [ 'field' => 10 ] );
	}

	public function test_constructor_set_field_name_with_string() {
		$this->expectException( \UnexpectedValueException::class );
		$this->expectExceptionMessage( "The 'fieldset' property must be required if the 'field' property is not instance of Iwf2b\Field\FieldInterface" );

		new RequiredIfRule( [ 'field' => 'field_name' ] );
	}

	public function test_do_validation_with_field_object() {
		$empty_field     = \Mockery::mock( FieldInterface::class );
		$non_empty_field = \Mockery::mock( FieldInterface::class );

		$empty_field->shouldReceive( 'get_value' )->andReturn( '' );
		$non_empty_field->shouldReceive( 'get_value' )->andReturn( 'field value' );

		// Check with empty value and empty field
		$rule_1 = new RequiredIfRule( [ 'field' => $empty_field ] );
		$this->assertTrue( $rule_1->validate() );

		// Check with value and empty field
		$rule_1->set_value( 'value' );
		$this->assertTrue( $rule_1->validate() );

		// Check with empty value and non empty field
		$rule_2 = new RequiredIfRule( [ 'field' => $non_empty_field ] );
		$this->assertFalse( $rule_2->validate() );

		// Check with value and non empty field
		$rule_2->set_value( 'value' );
		$this->assertTrue( $rule_2->validate() );
	}

	public function test_do_validation_with_field_name_and_fieldset() {
		$fieldset        = \Mockery::mock( FieldSet::class )->makePartial();
		$empty_field     = \Mockery::mock( FieldInterface::class );
		$non_empty_field = \Mockery::mock( FieldInterface::class );

		$fieldset->shouldReceive( 'offsetExists' )->andReturnUsing( function ( $index ) {
			return ( $index === 'empty_field' || $index === 'non_empty_field' );
		} );

		$fieldset->shouldReceive( 'offsetGet' )->andReturnUsing( function ( $index ) use ( $empty_field, $non_empty_field ) {
			if ( $index === 'empty_field' ) {
				return $empty_field;
			}

			if ( $index === 'non_empty_field' ) {
				return $non_empty_field;
			}

			return null;
		} );

		$empty_field->shouldReceive( 'get_value' )->andReturn( '' );
		$non_empty_field->shouldReceive( 'get_value' )->andReturn( 'field value' );

		// Check with empty value and empty field
		$rule_1 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => 'empty_field' ] );
		$this->assertTrue( $rule_1->validate() );

		// Check with value and empty field
		$rule_1->set_value( 'value' );
		$this->assertTrue( $rule_1->validate() );

		// Check with empty value and non empty field
		$rule_2 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => 'non_empty_field' ] );
		$this->assertFalse( $rule_2->validate() );

		// Check with value and non empty field
		$rule_2->set_value( 'value' );
		$this->assertTrue( $rule_2->validate() );

		// Check with empty value when expected value does not match
		$rule_3 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => 'non_empty_field', 'expected' => 'invalid value' ] );
		$this->assertTrue( $rule_3->validate() );

		// Check with empty value when expected value matches
		$rule_4 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => 'non_empty_field', 'expected' => 'field value' ] );
		$this->assertFalse( $rule_4->validate() );

		// Check with value when expected value matches
		$rule_4->set_value( 'value' );
		$this->assertTrue( $rule_4->validate() );

		// Check set unregistered field name
		$this->expectException( \UnexpectedValueException::class );
		$this->expectExceptionMessage( "The 'field' property must be the registered field or field name" );

		$rule_5 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => 'unregistered_field' ] );
		$rule_5->validate();
	}

	public function test_do_validation_strict() {
		$fieldset = \Mockery::mock( FieldSet::class )->makePartial();
		$field    = \Mockery::mock( FieldInterface::class );

		$field->shouldReceive( 'get_value' )->andReturn( 10 );

		// Check for not strict mode with the expected value type does not match the field value type
		$rule_1 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => $field, 'expected' => '10', 'strict' => false ] );
		$this->assertFalse( $rule_1->validate() );

		// Check for not strict mode with value
		$rule_1->set_value( 'test' );
		$this->assertTrue( $rule_1->validate() );

		// Check for strict mode with the expected value type does not match the field value type
		$rule_2 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => $field, 'expected' => '10', 'strict' => true ] );
		$this->assertTrue( $rule_2->validate() );

		// Check for strict mode with the expected value type matches the field value type
		$rule_3 = new RequiredIfRule( [ 'fieldset' => $fieldset, 'field' => $field, 'expected' => 10, 'strict' => true ] );
		$this->assertFalse( $rule_3->validate() );

		// Check for strict mode with value
		$rule_3->set_value( 'test' );
		$this->assertTrue( $rule_3->validate() );
	}
}