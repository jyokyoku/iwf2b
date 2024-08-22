<?php

namespace Iwf2b\Tests\TestCase;

use Iwf2b\AbstractOption;

class OptionTest extends \WP_UnitTestCase {
	public function test_operations_using_constant() {
		$expected = 'test1234';
		ConcreteOption::ON_SCALAR_VAR( $expected );

		$this->assertEquals( $expected, ConcreteOption::ON_SCALAR_VAR() );

		$expected = [ 1 => 'var1', 2 => 'var2' ];
		ConcreteOption::ON_ARRAY_VAR( $expected );

		$this->assertEquals( $expected, ConcreteOption::ON_ARRAY_VAR() );

		ConcreteOption::clear();

		$this->assertEmpty( ConcreteOption::ON_SCALAR_VAR() );
		$this->assertEmpty( ConcreteOption::ON_ARRAY_VAR() );
	}
}

class ConcreteOption extends AbstractOption {
	const ON_SCALAR_VAR = 'scalar_var';
	const ON_ARRAY_VAR = 'array_var';
}
