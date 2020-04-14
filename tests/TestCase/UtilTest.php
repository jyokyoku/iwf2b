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

	public function test_filter() {
		// test empty
		$this->assertEquals( 'input value', Util::filter( 'input value', [ 'empty' => 'this is empty' ] ) );
		$this->assertEquals( 'this is empty', Util::filter( '', [ 'empty' => 'this is empty' ] ) );
		$this->assertEquals( 'this is empty', Util::filter( 0, [ 'empty' => 'this is empty' ] ) );
		$this->assertEquals( true, Util::filter( true, [ 'empty' => 'this is empty' ] ) );
		$this->assertEquals( 'this is empty', Util::filter( false, [ 'empty' => 'this is empty' ] ) );
		$this->assertEquals( 'this is empty', Util::filter( [], [ 'empty' => 'this is empty' ] ) );

		// test nonempty
		$this->assertEquals( 'this is nonempty', Util::filter( 'input value', [ 'nonempty' => 'this is nonempty' ] ) );
		$this->assertEquals( '', Util::filter( '', [ 'nonempty' => 'this is nonempty' ] ) );
		$this->assertEquals( 0, Util::filter( 0, [ 'nonempty' => 'this is nonempty' ] ) );
		$this->assertEquals( 'this is nonempty', Util::filter( true, [ 'nonempty' => 'this is nonempty' ] ) );
		$this->assertEquals( false, Util::filter( false, [ 'nonempty' => 'this is nonempty' ] ) );
		$this->assertEquals( [], Util::filter( [], [ 'nonempty' => 'this is nonempty' ] ) );

		// test default
		$this->assertEquals( 'input value', Util::filter( 'input value', [ 'default' => 'default value' ] ) );
		$this->assertEquals( 'default value', Util::filter( '', [ 'default' => 'default value' ] ) );
		$this->assertEquals( 0, Util::filter( 0, [ 'default' => 'default value' ] ) );
		$this->assertEquals( true, Util::filter( true, [ 'default' => 'default value' ] ) );
		$this->assertEquals( 'default value', Util::filter( false, [ 'default' => 'default value' ] ) );
		$this->assertEquals( 'default value', Util::filter( [], [ 'default' => 'default value' ] ) );

		// test filters
		$this->assertEquals( 1, Util::filter( 0.5, [ 'filters' => [ 'round' ] ] ) );
		$this->assertEquals( 'ring', Util::filter( 'string', [ 'filters' => [ 'substr' => 2 ] ] ) );
		$this->assertEquals( 'ri', Util::filter( 'string', [ 'filters' => [ 'substr' => [ 2, 2 ] ] ] ) );
		$this->assertEquals( 'STR', Util::filter( 'string', [ 'filters' => [ 'strtoupper', 'substr' => [ 0, 3 ] ] ] ) );
		$this->assertEquals( 'rING', Util::filter( 'string', [ 'filters' => [ 'strtoupper', 'substr' => [ 2, 4 ], 'lcfirst' ] ] ) );

		// test prefix
		$this->assertEquals( 'pretest', Util::filter( 'test', [ 'prefix' => 'pre' ] ) );
		$this->assertEquals( 'pre0', Util::filter( 0, [ 'prefix' => 'pre' ] ) );
		$this->assertEquals( [ 'array value' ], Util::filter( [ 'array value' ], [ 'prefix' => 'pre' ] ) );
		$this->assertEquals( '', Util::filter( '', [ 'prefix' => 'pre' ] ) );
		$this->assertEquals( true, Util::filter( true, [ 'prefix' => 'pre' ] ) );

		// test suffix
		$this->assertEquals( 'testpost', Util::filter( 'test', [ 'suffix' => 'post' ] ) );
		$this->assertEquals( '0post', Util::filter( 0, [ 'suffix' => 'post' ] ) );
		$this->assertEquals( [ 'array value' ], Util::filter( [ 'array value' ], [ 'suffix' => 'post' ] ) );
		$this->assertEquals( '', Util::filter( '', [ 'suffix' => 'post' ] ) );
		$this->assertEquals( false, Util::filter( false, [ 'suffix' => 'post' ] ) );

		// test combination
		$this->assertEquals( 'empty value', Util::filter( '', [ 'empty' => 'empty value', 'default' => 'default value' ] ) );
		$this->assertEquals( 'nonempty value', Util::filter( 'input value', [ 'nonempty' => 'nonempty value', 'prefix' => 'pre', 'suffix' => 'post' ] ) );
		$this->assertEquals( 'default value', Util::filter( '', [ 'default' => 'default value', 'prefix' => 'pre', 'suffix' => 'post' ] ) );
		$this->assertEquals( 'preTRIpost', Util::filter( 'string', [ 'filters' => [ 'strtoupper', 'substr' => [ 1, 3 ] ], 'prefix' => 'pre', 'suffix' => 'post' ] ) );
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