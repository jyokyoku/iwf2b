<?php

namespace Iwf2b\Tests\TestCase\Tax;

use Iwf2b\Post\AbstractPost;
use Iwf2b\Tax\AbstractTax;

class AbstractTaxTest extends \WP_UnitTestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		TestTax::get_instance()->register_taxonomy();
	}

	public function test_register() {
		global $wp_taxonomies;

		RegisterTextTax1::get_instance()->register_taxonomy();

		$this->assertTrue( isset( $wp_taxonomies['register_test_tax_1'] ) );
		$this->assertTrue( in_array( 'dummy_post', $wp_taxonomies['register_test_tax_1']->object_type ) );

		RegisterTextTax2::get_instance()->register_taxonomy();

		$this->assertTrue( isset( $wp_taxonomies['register_test_tax_2'] ) );
		$this->assertTrue( in_array( 'test_post', $wp_taxonomies['register_test_tax_2']->object_type ) );

		$this->expectException( \InvalidArgumentException::class );
		RegisterTextTax3::get_instance()->register_taxonomy();
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

class RegisterTextTax1 extends AbstractTax {
	protected static $taxonomy = 'register_test_tax_1';

	protected static $object_type = 'dummy_post';
}

class RegisterTextTax2 extends AbstractTax {
	protected static $taxonomy = 'register_test_tax_2';

	protected static $object_type = TestPost::class;
}

class RegisterTextTax3 extends AbstractTax {
	protected static $taxonomy = 'register_test_tax_3';

	protected static $object_type = DummyObject::class;
}

class TestPost extends AbstractPost {
	protected static $post_type = 'test_post';
}

class DummyObject {
}
