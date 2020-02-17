<?php

namespace Iwf2b\Tests\TestCase\Post;

use Iwf2b\Post\AbstractPost;

class AbstractPostTest extends \WP_UnitTestCase {
	protected function set_static_property( $class, $prop, $value ) {
		$ref  = new \ReflectionClass( $class );

		$prop = $ref->getProperty( $prop );
		$prop->setAccessible( true );
		$prop->setValue( $value );
	}

	protected function make_post_class( $class, $post_type, array $args = [], $register = false ) {
		$post_class = \Mockery::namedMock( $class, AbstractPost::class );
		$post_class->makePartial();

		$this->set_static_property( $post_class, 'post_type', $post_type );

		if ( $args ) {
			$this->set_static_property( $post_class, 'args', $args );
		}

		if ( $register ) {
			$post_class->register_post_type();
		}

		return $post_class;
	}

	public function tearDown() {
		parent::tearDown();

		\Mockery::close();
	}

	public function test_get() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [], true );

		$post_id = $this->factory->post->create( [
			'post_type'  => 'test_post',
			'post_title' => 'Test',
		] );

		$this->assertEmpty( $post_class::get( 0 ) );
		$this->assertEquals( $post_class::get( $post_id ), get_post( $post_id ) );
	}

	public function test_is_valid() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [], true );

		$post_id = $this->factory->post->create( [
			'post_type'  => 'test_post',
			'post_title' => 'Test',
		] );

		$this->assertFalse( $post_class::is_valid( 0 ) );
		$this->assertTrue( $post_class::is_valid( $post_id ) );
	}

	public function test_get_archive_link() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [
			'supports'    => [ 'title', 'editor' ],
			'has_archive' => true,
		], true );

		$this->assertEquals( get_post_type_archive_link( 'test_post' ), $post_class::get_archive_link() );
	}

	public function get_get_object() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [
			'supports'    => [ 'title', 'editor' ],
			'has_archive' => true,
		], true );

		$this->assertEquals( get_post_type_object( 'test_post' ), $post_class::get_get_object() );
	}
}