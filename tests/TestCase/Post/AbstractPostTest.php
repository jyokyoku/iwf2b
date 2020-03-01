<?php

namespace Iwf2b\Tests\TestCase\Post;

use Iwf2b\Post\AbstractPost;
use org\bovigo\vfs\vfsStream;

class AbstractPostTest extends \WP_UnitTestCase {
	private $root;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
	}

	public function setUp() {
		parent::setUp();

		$this->root = vfsStream::setup();
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

	public function test_get_post_type_object() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [
			'supports'    => [ 'title', 'editor' ],
			'has_archive' => true,
		], true );

		$this->assertEquals( get_post_type_object( 'test_post' ), $post_class::get_post_type_object() );
	}

	public function test_get_thumbnail() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [
			'supports'    => [ 'title', 'editor' ],
			'has_archive' => true,
		], true );

		$post_id = $this->factory->post->create( [
			'post_type'  => 'test_post',
			'post_title' => 'Test',
		] );

		$thumbnail = $post_class::get_thumbnail( $post_id );

		$this->assertEmpty( $thumbnail );
	}

	public function test_get_thumbnail_from_featured_image() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [
			'supports'    => [ 'title', 'editor' ],
			'has_archive' => true,
		], true );

		$post_id = $this->factory->post->create( [
			'post_type'  => 'test_post',
			'post_title' => 'Test',
		] );

		$test_img_path = $this->create_virtual_image( 'test.jpg', 'jpg', [ 'width' => 200, 'height' => 150 ] );
		$attachment_id = $this->factory->attachment->create_upload_object( $test_img_path, $post_id );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Image alt text' );
		set_post_thumbnail( $post_id, $attachment_id );

		// Test featured image
		$thumbnail = $post_class::get_thumbnail( $post_id );

		$this->assertNotEmpty( $thumbnail['src'] );
		$this->assertEquals( 200, $thumbnail['width'] );
		$this->assertEquals( 150, $thumbnail['height'] );
		$this->assertEquals( 'Image alt text', $thumbnail['alt'] );

		// Test overwrite alt keyword
		$thumbnail = $post_class::get_thumbnail( $post_id, [ 'alt' => 'Overwrite alt text' ] );

		$this->assertNotEmpty( $thumbnail['src'] );
		$this->assertEquals( 200, $thumbnail['width'] );
		$this->assertEquals( 150, $thumbnail['height'] );
		$this->assertEquals( 'Overwrite alt text', $thumbnail['alt'] );
	}

	/**
	 * @depends test_get_thumbnail_from_featured_image
	 */
	public function test_get_thumbnail_from_dummy_image() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [
			'supports'    => [ 'title', 'editor' ],
			'has_archive' => true,
		], true );

		$post_id = $this->factory->post->create( [
			'post_type'  => 'test_post',
			'post_title' => 'Dummy image test',
		] );

		$test_img_path = $this->create_virtual_image( 'test.gif', 'gif', [ 'width' => 320, 'height' => 240 ] );

		// Test empty if featured image is empty
		$thumbnail = $post_class::get_thumbnail( $post_id );

		$this->assertEmpty( $thumbnail );

		// Test dummy image
		$thumbnail = $post_class::get_thumbnail( $post_id, [ 'dummy_image' => $test_img_path, 'alt' => '' ] );

		$this->assertNotEmpty( $thumbnail['src'] );
		$this->assertEquals( 320, $thumbnail['width'] );
		$this->assertEquals( 240, $thumbnail['height'] );
		$this->assertEquals( '', $thumbnail['alt'] );

		// Test overwrite alt keyword
		$thumbnail = $post_class::get_thumbnail( $post_id, [ 'dummy_image' => $test_img_path, 'alt' => 'Image alt text' ] );

		$this->assertNotEmpty( $thumbnail['src'] );
		$this->assertEquals( 320, $thumbnail['width'] );
		$this->assertEquals( 240, $thumbnail['height'] );
		$this->assertEquals( 'Image alt text', $thumbnail['alt'] );

		// Test the featured image have priority over the dummy image.
		$test_img_path_2 = $this->create_virtual_image( 'test_2.png', 'png', [ 'width' => 180, 'height' => 280 ] );
		$attachment_id = $this->factory->attachment->create_upload_object( $test_img_path_2, $post_id );
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Image alt text 2' );
		set_post_thumbnail( $post_id, $attachment_id );

		$thumbnail = $post_class::get_thumbnail( $post_id, [ 'dummy_image' => $test_img_path, 'alt' => '' ] );

		$this->assertNotEmpty( $thumbnail['src'] );
		$this->assertEquals( 180, $thumbnail['width'] );
		$this->assertEquals( 280, $thumbnail['height'] );
		$this->assertEquals( 'Image alt text 2', $thumbnail['alt'] );
	}

	public function test_get_thumbnail_from_first_image_in_post_content() {
		$post_class = $this->make_post_class( 'TestPost', 'test_post', [
			'supports'    => [ 'title', 'editor' ],
			'has_archive' => true,
		], true );

		$test_img_path = $this->create_virtual_image( 'test.png', 'png', [ 'width' => 280, 'height' => 190 ] );

		kses_remove_filters();  // Allow 'vfs' protocol to the post_content

		$post_id = $this->factory->post->create( [
			'post_type'    => 'test_post',
			'post_title'   => 'Post content image test',
			'post_content' => '<p><img src="' . $test_img_path . '" alt="Image alt text"></p>',
		] );

		// Test invalid keyword of post object
		$thumbnail = $post_class::get_thumbnail( $post_id, [ 'search_post_key' => 'dummy_keyword' ] );

		$this->assertEmpty( $thumbnail );

		// Test post_content
		$thumbnail = $post_class::get_thumbnail( $post_id, [ 'search_post_key' => 'post_content' ] );

		$this->assertNotEmpty( $thumbnail['src'] );
		$this->assertEquals( 280, $thumbnail['width'] );
		$this->assertEquals( 190, $thumbnail['height'] );
		$this->assertEquals( 'Image alt text', $thumbnail['alt'] );

		// Test overwrite alt keyword
		$thumbnail = $post_class::get_thumbnail( $post_id, [ 'search_post_key' => 'post_content', 'alt' => 'Overwrite alt text' ] );

		$this->assertNotEmpty( $thumbnail['src'] );
		$this->assertEquals( 280, $thumbnail['width'] );
		$this->assertEquals( 190, $thumbnail['height'] );
		$this->assertEquals( 'Overwrite alt text', $thumbnail['alt'] );
	}

	protected function set_static_property( $class, $prop, $value ) {
		$ref = new \ReflectionClass( $class );

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

	protected function create_virtual_image( $file_name, $type = 'jpg', array $args = [] ) {
		$width  = isset( $args['width'] ) && $args['width'] > 0 ? (int) $args['width'] : 100;
		$height = isset( $args['height'] ) && $args['height'] > 0 ? (int) $args['height'] : 100;

		$image = imagecreate( $width, $height );
		imagecolorallocate( $image, 255, 255, 255 );

		ob_start();

		switch ( strtolower( $type ) ) {
			case 'png':
				imagepng( $image );
				break;

			case 'gif':
				imagegif( $image );
				break;

			default:
				imagejpeg( $image );
				break;
		}

		$image_data = ob_get_clean();

		return vfsStream::newFile( $file_name )->at( $this->root )->setContent( $image_data )->url();
	}
}