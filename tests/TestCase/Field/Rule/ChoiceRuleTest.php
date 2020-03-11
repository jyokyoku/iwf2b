<?php

namespace Theme\Field\Rule;

use Iwf2b\Field\Rule\ChoiceRule;

class ChoiceRuleTest extends \WP_UnitTestCase {
	public function test_validate() {
		$rule = new ChoiceRule( [ 'choices' => [ 'choice_1', 'choice_2', 10 ] ] );

		$rule->set_value( 'choice_1' );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( 'choice_2' );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( '10' );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( 'invalid_choice' );
		$this->assertFalse( $rule->validate() );
	}

	public function test_validate_from_text() {
		$rule = new ChoiceRule( [ 'choices' => 'choice_1 choice_2, 10' ] );

		$rule->set_value( 'choice_1' );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( 'choice_2' );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( 10 );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( 'choice_1 choice_2, 10' );
		$this->assertFalse( $rule->validate() );
	}

	public function test_validate_strict() {
		$rule = new ChoiceRule( [ 'choices' => [ 10, 0.01 ], 'strict' => true ] );

		$rule->set_value( 10 );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( '10' );
		$this->assertFalse( $rule->validate() );

		$rule->set_value( 0.01 );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( '0.01' );
		$this->assertFalse( $rule->validate() );
	}

	public function test_validate_multiple() {
		$rule_1 = new ChoiceRule( [ 'choices' => [ 'choice', 10, 0.01 ], 'multiple' => true ] );

		// Check the multiple mode
		$rule_1->set_value( [ 'choice' ] );
		$this->assertTrue( $rule_1->validate() );

		$rule_1->set_value( [ 'choice', '10', '0.01' ] );
		$this->assertTrue( $rule_1->validate() );

		$rule_1->set_value( [ 'choice', 'invalid_choice' ] );
		$this->assertFalse( $rule_1->validate() );

		// Check the multiple and strict mode
		$rule_2 = new ChoiceRule( [ 'choices' => [ 'choice', 10, 0.01 ], 'multiple' => true, 'strict' => true ] );

		$rule_2->set_value( [ 'choice', 10, '0.01' ] );
		$this->assertFalse( $rule_2->validate() );

		$rule_2->set_value( [ 'choice', 10, 0.01 ] );
		$this->assertTrue( $rule_2->validate() );

		$this->expectException( \UnexpectedValueException::class );
		$this->expectExceptionMessage( 'The value must be an array.' );

		$rule_2->set_value( 'choice' );
		$rule_2->validate();
	}
}