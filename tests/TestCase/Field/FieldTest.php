<?php

namespace Iwf2b\Tests\TestCase\Field;

use Iwf2b\Field\Field;

class FieldTest extends \WP_UnitTestCase {
	public function test_set_errors() {
		$field = new Field( 'test', 'Test' );
		$field->set_error( 'Error has occurred.' );

		$errors_prop = new \ReflectionProperty( Field::class, 'errors' );
		$errors_prop->setAccessible( true );

		$this->assertEquals( [ 'Error has occurred.' ], $errors_prop->getValue( $field ) );

		$field->set_error( 'Second error has occurred.' );

		$this->assertEquals( [ 'Error has occurred.', 'Second error has occurred.' ], $errors_prop->getValue( $field ) );
	}

	/**
	 * @depends test_set_errors
	 */
	public function test_clear_errors() {
		$field = new Field( 'test', 'Test' );

		$errors_prop = new \ReflectionProperty( Field::class, 'errors' );
		$errors_prop->setAccessible( true );

		$field->set_error( 'Error has occurred.' );
		$field->clear_errors();

		$this->assertEmpty( $errors_prop->getValue( $field ) );
	}
}