<?php

namespace Iwf2b\Tests\TestCase;

use Iwf2b\Text;
class TextTest extends \WP_UnitTestCase {
	public function test_stringify() {
		$this->assertEquals( '', Text::stringify( null ) );
		$this->assertEquals( '0', Text::stringify( 0 ) );
		$this->assertEquals( 'true', Text::stringify( true ) );
		$this->assertEquals( 'false', Text::stringify( false ) );
		$this->assertEquals( 'test', Text::stringify( [ 'test' ] ) );
		$this->assertEquals( 'test, test_2', Text::stringify( [ 'key' => 'test', 'key_2' => 'test_2' ] ) );
		$this->assertEquals( 'true, test, false, , 0, test_3', Text::stringify( [ true, 'key' => 'test', [ false, null, 0, 'test_3' ] ] ) );
		$this->assertEquals( 'toString results', Text::stringify( new TestToStringClass() ) );
		$this->assertEquals( '(Object)', Text::stringify( new TestNoToStringClass() ) );
		$this->assertEquals( '10, test, sub_test, false, true', Text::stringify( new TestIteratorClass( [ 10, [ 'key' => 'test', [ 'sub_key' => 'sub_test' ] ], false, true ] ) ) );
	}

	public function test_stringify_glue() {
		$this->assertEquals( 'test%%%test_2%%%test_3', Text::stringify( [ 'key' => 'test', 'key_2' => 'test_2', 'key_3' => 'test_3' ], '%%%' ) );
	}
}

class TestToStringClass {
	public function __toString() {
		return 'toString results';
	}
}

class TestNoToStringClass {
}

class TestIteratorClass implements \IteratorAggregate {
	protected $values = [];

	public function __construct( array $values ) {
		$this->values = $values;
	}

	public function getIterator() {
		return new \ArrayIterator( $this->values );
	}
}