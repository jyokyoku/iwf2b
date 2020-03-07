<?php

namespace Theme\Field\Rule;

use Iwf2b\Field\Data\FileData;
use Iwf2b\Field\Rule\AbstractRule;
use Iwf2b\Field\Rule\Exception\MissingRequiredParamsException;
use PHPUnit\Framework\Error\Notice;

class AbstractRuleTest extends \WP_UnitTestCase {
	public function tearDown() {
		parent::tearDown();

		\Mockery::close();
	}

	public function test_constructor_set_config_value() {
		$rule        = $this->get_mock_rule( 'TestRule' );
		$constructor = $this->get_object_method( $rule, '__construct' );

		$rule->dummy_var_1 = null;
		$rule->dummy_var_2 = null;

		$constructor( [
			'message'         => 'test message',
			'dummy_var_1'     => 'dummy content 1',
			'dummy_var_2'     => 'dummy content 2',
			'not_defined_var' => 'pass-through',
		] );

		$this->assertEquals( 'test message', $rule->get_message() );
		$this->assertEquals( 'dummy content 1', $rule->dummy_var_1 );
		$this->assertEquals( 'dummy content 2', $rule->dummy_var_2 );

		$this->expectException( Notice::class );
		$this->expectExceptionMessage( 'Undefined property: TestRule::$not_defined_var' );
		$rule->not_defined_var;
	}

	public function test_constructor_get_default_message() {
		$rule        = $this->get_mock_rule( 'TestRule' );
		$constructor = $this->get_object_method( $rule, '__construct' );

		// Check not namespaced rule
		$this->assertEquals( 'Test', $rule->get_message() );

		$constructor( [ 'message' => 'test message' ] );

		$this->assertEquals( 'test message', $rule->get_message() );

		// Check namespaced rule
		$rule = new TestNamespacedRule();

		$this->assertEquals( 'TestNamespaced', $rule->get_message() );
	}

	public function test_constructor_set_required_property() {
		$rule        = $this->get_mock_rule( 'TestRule' );
		$constructor = $this->get_object_method( $rule, '__construct' );

		$rule->dummy_var_1 = null;
		$rule->dummy_var_2 = null;

		$rule->shouldReceive( 'get_required_params' )->andReturn( [ 'dummy_var_1' ] );

		$this->expectException( MissingRequiredParamsException::class );
		$this->expectExceptionMessage( 'The params "dummy_var_1" must be set for TestRule' );

		$constructor( [
			'message'     => 'test message',
			'dummy_var_2' => 'dummy content 2',
		] );
	}

	public function test_constructor_set_default_param_property() {
		$rule        = $this->get_mock_rule( 'TestRule' );
		$constructor = $this->get_object_method( $rule, '__construct' );

		$rule->dummy_var_1 = null;
		$rule->dummy_var_2 = null;

		$rule->shouldReceive( 'get_default_param' )->andReturn( 'dummy_var_1' );

		$constructor( 'dummy content' );

		$this->assertEquals( 'dummy content', $rule->dummy_var_1 );

		// Check specified array
		$rule->dummy_var_1 = null;
		$constructor( [ 'dummy content' ] );

		$this->assertEmpty( $rule->dummy_var_1 );
	}

	public function test_constructor_set_param_types() {
		$rule = $this->get_mock_rule( 'TestRule' );

		$constructor = $this->get_object_method( $rule, '__construct' );

		$rule->dummy_var_1 = null;
		$rule->dummy_var_2 = null;
		$rule->dummy_var_3 = null;

		$rule->shouldReceive( 'get_param_types' )->andReturn( [
			'dummy_var_1'    => 'int', // check is_int
			'dummy_var_2'    => 'lower', // check ctype_lower
			'dummy_var_3'    => 'not_defined_type', // check must be error
			'no_defined_var' => 'alpha' // pass-through
		] );

		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'The param "dummy_var_3" must be "not_defined_type" type' );

		$constructor( [
			'dummy_var_1' => 10,
			'dummy_var_2' => 'lowerstrings',
			'dummy_var_3' => 'invalid value',
		] );
	}

	public function test_is_empty() {
		$rule = $this->get_mock_rule( 'TestRule' );

		$this->assertTrue( $rule->is_empty( null ) );
		$this->assertTrue( $rule->is_empty( false ) );
		$this->assertTrue( $rule->is_empty( '' ) );
		$this->assertTrue( $rule->is_empty( ' ' ) );
		$this->assertTrue( $rule->is_empty( [] ) );
		$this->assertTrue( $rule->is_empty( new FileData( '' ) ) );

		$this->assertFalse( $rule->is_empty( true ) );
		$this->assertFalse( $rule->is_empty( 0 ) );
		$this->assertFalse( $rule->is_empty( 'test' ) );
		$this->assertFalse( $rule->is_empty( [ '' ] ) );
		$this->assertFalse( $rule->is_empty( new FileData( '/dummy/data.txt' ) ) );
	}

	public function test_validate() {
		$rule = $this->get_mock_rule( 'TestRule' );

		$rule->shouldReceive( 'through_if_empty' )->andReturn( false );
		$rule->shouldReceive( 'do_validate' )->andReturn( false );

		$this->assertFalse( $rule->validate() );
	}

	public function test_validate_through_if_empty() {
		$rule = $this->get_mock_rule( 'TestRule' );

		$rule->shouldReceive( 'through_if_empty' )->andReturn( true );
		$rule->shouldReceive( 'do_validate' )->andReturn( false );

		$this->assertTrue( $rule->validate() );

		// Returns false if the value is not empty
		$rule->set_value( true );

		$this->assertFalse( $rule->validate() );
	}

	protected function get_mock_rule( $class_name ) {
		return \Mockery::namedMock( $class_name, AbstractRule::class )
		               ->makePartial()
		               ->shouldAllowMockingProtectedMethods();
	}

	protected function get_object_method( $object, $method_name ) {
		if ( ! is_object( $object ) ) {
			throw new \InvalidArgumentException( 'Can not get method of non object' );
		}

		$reflection_method = new \ReflectionMethod( $object, $method_name );

		return function () use ( $object, $reflection_method ) {
			return $reflection_method->invokeArgs( $object, func_get_args() );
		};
	}
}

class TestNamespacedRule extends AbstractRule {
	protected function do_validate() {
		return false;
	}
}