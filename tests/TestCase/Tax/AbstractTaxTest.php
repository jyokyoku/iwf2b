<?php

namespace Iwf2b\Tests\TestCase\Tax;

use Iwf2b\Tax\AbstractTax;

class AbstractTaxTest extends \WP_UnitTestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		TestTax::get_instance()->register_taxonomy();
	}

	public function test_get() {
		$term_id = $this->factory->term->create( [
			'taxonomy' => 'test_tax',
			'name'     => 'test_term',
			'slug'     => 'test_term_slug',
		] );

		$this->assertEmpty( TestTax::get( 0 ) );

		$term_data = get_term( $term_id );

		$this->assertEquals( $term_data, TestTax::get( $term_id ) );
		$this->assertEquals( $term_data, TestTax::get( 'test_term' ) );
		$this->assertEquals( $term_data, TestTax::get( 'test_term_slug' ) );
	}

	public function test_get_id() {
		$term_id = $this->factory->term->create( [
			'taxonomy' => 'test_tax',
			'name'     => 'test_term2',
			'slug'     => 'test_term_slug2',
		] );

		$this->assertEquals( 0, TestTax::get_id( 1000 ) );
		$this->assertEquals( $term_id, TestTax::get_id( $term_id ) );
		$this->assertEquals( $term_id, TestTax::get_id( 'test_term2' ) );
		$this->assertEquals( $term_id, TestTax::get_id( 'test_term_slug2' ) );
	}
}

class TestTax extends AbstractTax {
	protected static $taxonomy = 'test_tax';
}