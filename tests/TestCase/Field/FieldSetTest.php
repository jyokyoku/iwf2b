<?php

namespace Iwf2b\Tests\TestCase\Field;

use Iwf2b\Field\Field;
use Iwf2b\Field\FieldSet;

class FieldSetTest extends \WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();

		$instances = new \ReflectionProperty( FieldSet::class, 'instances' );
		$instances->setAccessible( true );
		$instances->setValue( [] );
	}

	public function test_get_instance() {
		$fieldset = FieldSet::get_instance( 'test_fieldset' );

		$this->assertInstanceOf( FieldSet::class, $fieldset );
	}

	/**
	 * @depends test_get_instance
	 */
	public function test_get_instance_returns_same_object() {
		$fieldset  = FieldSet::get_instance( 'test_fieldset' );
		$fieldset2 = FieldSet::get_instance( 'test_fieldset' );

		$this->assertSame( $fieldset, $fieldset2 );
	}

	/**
	 * @depends test_get_instance
	 */
	public function test_add_field() {
		$fieldset = FieldSet::get_instance( 'test_fieldset' );
		$fieldset->add_field( new Field( 'test_field', 'test_field_label' ) );

		$this->assertNotEmpty( $fieldset );
		$this->assertTrue( isset( $fieldset['test_field'] ) );
		$this->assertInstanceOf( Field::class, $fieldset['test_field'] );
	}

	public function test_exists() {
		$this->assertFalse( FieldSet::exists( 'test_fieldset' ) );

		FieldSet::get_instance( 'test_fieldset' );

		$this->assertTrue( FieldSet::exists( 'test_fieldset' ) );
	}

	/**
	 * @depends test_add_field
	 * @depends test_get_instance
	 */
	public function test_destroy() {
		$fieldset = FieldSet::get_instance( 'test_fieldset' );
		$fieldset->add_field( new Field( 'test_field', 'test_field_label' ) );

		FieldSet::destroy( 'test_fieldset' );

		$fieldset2 = FieldSet::get_instance( 'test_fieldset' );

		$this->assertNotSame( $fieldset, $fieldset2 );
		$this->assertEmpty( $fieldset );
	}

	/**
	 * @depends test_add_field
	 * @depends test_get_instance
	 */
	public function test_destroy_with_object() {
		$fieldset = FieldSet::get_instance( 'test_fieldset' );
		$fieldset->add_field( new Field( 'test_field', 'test_field_label' ) );

		FieldSet::destroy( $fieldset );

		$fieldset2 = FieldSet::get_instance( 'test_fieldset' );

		$this->assertNotSame( $fieldset, $fieldset2 );
		$this->assertEmpty( $fieldset );
	}

	/**
	 * @depends test_add_field
	 */
	public function test_destroy_not_registered_instance_name() {
		FieldSet::get_instance( 'test_fieldset' );

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Specified 'test_fieldset_2' does not exists in the instance list." );

		FieldSet::destroy( 'test_fieldset_2' );
	}

	/**
	 * @depends test_destroy_with_object
	 */
	public function test_destroy_not_registered_instance_object() {
		$fieldset = FieldSet::get_instance( 'test_fieldset' );
		FieldSet::destroy( $fieldset );

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( "Specified instance does not exists in the instance list." );

		FieldSet::destroy( $fieldset );
	}

}