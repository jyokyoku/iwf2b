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

	public function test_convert_case() {
		$this->assertEquals( 'pascal_case_to_snake_case', Text::convert_case( 'PascalCaseToSnakeCase', 'snake' ) );
		$this->assertEquals( 'camel_case_to_snake_case', Text::convert_case( 'camelCaseToSnakeCase', 'snake' ) );
		$this->assertEquals( 'kebab_case_to_snake_case', Text::convert_case( 'kebab-case-to-snake-case', 'snake' ) );
		$this->assertEquals( 'mixed_case_to_snake_case', Text::convert_case( 'Mixed__case--To_SnakeCase', 'snake' ) );

		$this->assertEquals( 'pascal-case-to-kebab-case', Text::convert_case( 'PascalCaseToKebabCase', 'kebab' ) );
		$this->assertEquals( 'camel-case-to-kebab-case', Text::convert_case( 'camelCaseToKebabCase', 'kebab' ) );
		$this->assertEquals( 'snake-case-to-kebab-case', Text::convert_case( 'snake_case_to_kebab_case', 'kebab' ) );
		$this->assertEquals( 'mixed-case-to-kebab-case', Text::convert_case( 'Mixed__case--To_KebabCase', 'kebab' ) );

		$this->assertEquals( 'CamelCaseToPascalCase', Text::convert_case( 'camelCaseToPascalCase', 'pascal' ) );
		$this->assertEquals( 'KebabCaseToPascalCase', Text::convert_case( 'kebab-case-to-pascal-case', 'pascal' ) );
		$this->assertEquals( 'SnakeCaseToPascalCase', Text::convert_case( 'snake_case_to_pascal_case', 'pascal' ) );
		$this->assertEquals( 'MixedCaseToPascalCase', Text::convert_case( 'Mixed__case--To_PascalCase-', 'pascal' ) );

		$this->assertEquals( 'pascalCaseToCamelCase', Text::convert_case( 'PascalCaseToCamelCase', 'camel' ) );
		$this->assertEquals( 'kebabCaseToCamelCase', Text::convert_case( 'kebab-case-to-camel-case', 'camel' ) );
		$this->assertEquals( 'snakeCaseToCamelCase', Text::convert_case( 'snake_case_to_camel_case', 'camel' ) );
		$this->assertEquals( 'mixedCaseToCamelCase', Text::convert_case( 'Mixed__case--To_CamelCase', 'camel' ) );
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