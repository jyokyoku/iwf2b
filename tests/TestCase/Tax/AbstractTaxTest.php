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

		RegisterTextTax4::get_instance();

		$term_id = $this->factory->term->create( [
			'taxonomy' => 'register_test_tax_4',
			'name'     => 'test_term',
			'slug'     => 'test_term_slug',
		] );

		$this->assertTrue( metadata_exists( 'term', $term_id, 'defined_var' ) );
		$this->assertFalse( metadata_exists( 'term', $term_id, 'undefined_var' ) );
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

	public function test_meta_operations_using_meta_key_constant() {
		RegisterTextTax5::get_instance()->register_taxonomy();

		$term_id = $this->factory->term->create( [
			'taxonomy' => RegisterTextTax5::get_taxonomy(),
			'name'     => 'test_term',
			'slug'     => 'test_term_slug',
		] );

		$expected = 'test1234';
		RegisterTextTax5::MK_SCALAR_VAR( $term_id, $expected );

		$this->assertEquals( $expected, RegisterTextTax5::MK_SCALAR_VAR( $term_id ) );

		$expected = [ 1 => 'var1', 2 => 'var2' ];
		RegisterTextTax5::MK_ARRAY_VAR( $term_id, $expected );

		$this->assertEquals( $expected, RegisterTextTax5::MK_ARRAY_VAR( $term_id ) );

		RegisterTextTax5::clear_meta( $term_id );

		$this->assertEmpty( RegisterTextTax5::MK_SCALAR_VAR( $term_id ) );
		$this->assertEmpty( RegisterTextTax5::MK_ARRAY_VAR( $term_id ) );
	}

	public function test_meta_operations_using_meta_key_constant_with_term_object() {
		RegisterTextTax5::get_instance()->register_taxonomy();

		$term_id     = $this->factory->term->create( [
			'taxonomy' => RegisterTextTax5::get_taxonomy(),
			'name'     => 'test_term',
			'slug'     => 'test_term_slug',
		] );
		$term_object = get_term( $term_id );

		$expected = 'test1234';
		RegisterTextTax5::MK_SCALAR_VAR( $term_object, $expected );

		$this->assertEquals( $expected, RegisterTextTax5::MK_SCALAR_VAR( $term_object ) );

		$expected = [ 1 => 'var1', 2 => 'var2' ];
		RegisterTextTax5::MK_ARRAY_VAR( $term_object, $expected );

		$this->assertEquals( $expected, RegisterTextTax5::MK_ARRAY_VAR( $term_object ) );

		RegisterTextTax5::clear_meta( $term_object );

		$this->assertEmpty( RegisterTextTax5::MK_SCALAR_VAR( $term_object ) );
		$this->assertEmpty( RegisterTextTax5::MK_ARRAY_VAR( $term_object ) );
	}
}

class TestTax extends AbstractTax {
	protected $taxonomy = 'test_tax';
}

class RegisterTextTax1 extends AbstractTax {
	protected $taxonomy = 'register_test_tax_1';

	protected $object_type = 'dummy_post';
}

class RegisterTextTax2 extends AbstractTax {
	protected $taxonomy = 'register_test_tax_2';

	protected $object_type = TestPost::class;
}

class RegisterTextTax3 extends AbstractTax {
	protected $taxonomy = 'register_test_tax_3';

	protected $object_type = DummyObject::class;
}

class RegisterTextTax4 extends AbstractTax {
	const MK_DEFINED_VAR = 'defined_var';

	protected $taxonomy = 'register_test_tax_4';
}

class RegisterTextTax5 extends AbstractTax {
	const MK_SCALAR_VAR = 'scalar_var';
	const MK_ARRAY_VAR = 'array_Var';

	protected $taxonomy = 'register_test_tax_5';
}

class TestPost extends AbstractPost {
	protected $post_type = 'test_post';
}

class DummyObject {
}
