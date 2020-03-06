<?php

namespace Iwf2b\Tests\TestCase;

use Iwf2b\Util;

class UtilTest extends \WP_UnitTestCase {
	public function test_is_empty() {
		$this->assertTrue( Util::is_empty( null ) );
		$this->assertTrue( Util::is_empty( false ) );
		$this->assertTrue( Util::is_empty( '' ) );
		$this->assertTrue( Util::is_empty( ' ', true ) );
		$this->assertTrue( Util::is_empty( [] ) );
		$this->assertTrue( Util::is_empty( new TestCountableZero() ) );

		$this->assertFalse( Util::is_empty( 0 ) );
		$this->assertFalse( Util::is_empty( true ) );
		$this->assertFalse( Util::is_empty( 'test' ) );
		$this->assertFalse( Util::is_empty( ' ', false ) );
		$this->assertFalse( Util::is_empty( [ '' ] ) );
		$this->assertFalse( Util::is_empty( new TestCountableNonZero() ) );
	}

	public function test_parse_phpdoc_tags_for_class() {
		$tags = Util::parse_phpdoc_tags( new \ReflectionClass( TestPHPDoc::class ) );

		$this->assertEquals( [
			'tag'       => 'tag content',
			'tag-2'     => 'second tag content',
			'thirdTag:' => 'third tag content',
		], $tags );

		// Check search args
		$tags = Util::parse_phpdoc_tags( new \ReflectionClass( TestPHPDoc::class ), [ 'tag-2', 'undefined-tag' ] );

		$this->assertEquals( [
			'tag-2' => 'second tag content',
		], $tags );

		$tags = Util::parse_phpdoc_tags( new \ReflectionClass( TestPHPDoc::class ), 'tag' );

		$this->assertEquals( [
			'tag' => 'tag content',
		], $tags );

		$tags = Util::parse_phpdoc_tags( new \ReflectionClass( TestPHPDoc::class ), 'undefined-tag' );

		$this->assertEmpty( $tags );
	}

	public function test_parse_phpdoc_tags_for_method() {
		$tags = Util::parse_phpdoc_tags( new \ReflectionMethod( TestPHPDoc::class, 'method' ) );

		$this->assertEquals( [
			'tag'       => 'tag content',
			'tag-2'     => 'second tag content',
			'thirdTag:' => 'third tag content',
		], $tags );
	}

	public function test_parse_phpdoc_tags_for_property() {
		$tags = Util::parse_phpdoc_tags( new \ReflectionProperty( TestPHPDoc::class, 'var' ) );

		$this->assertEquals( [
			'tag'       => 'tag content',
			'tag-2'     => 'second tag content',
			'thirdTag:' => 'third tag content',
		], $tags );

		// Check short doc comment
		$tags = Util::parse_phpdoc_tags( new \ReflectionProperty( TestPHPDoc::class, 'short_comment_var' ) );

		$this->assertEquals( [
			'tag' => 'tag content',
		], $tags );
	}
}

/**
 * @tag tag content
 * @tag-2 second tag content
 * @thirdTag: third tag content
 */
class TestPHPDoc {
	/**
	 * @tag tag content
	 * @tag-2 second tag content
	 * @thirdTag: third tag content
	 */
	public $var;

	/** @tag tag content */
	public $short_comment_var;

	/**
	 * @tag tag content
	 * @tag-2 second tag content
	 * @thirdTag: third tag content
	 */
	public function method() {
	}
}

class TestCountableZero implements \Countable {
	public function count() {
		return 0;
	}
}

class TestCountableNonZero implements \Countable {
	public function count() {
		return 1;
	}
}