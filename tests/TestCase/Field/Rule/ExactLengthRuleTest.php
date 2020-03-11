<?php

namespace Theme\Field\Rule;

use Iwf2b\Field\Rule\ExactLengthRule;

class ExactLengthRuleTest extends \WP_UnitTestCase {
	public function test_validate() {
		$rule = new ExactLengthRule( [ 'length' => 10 ] );

		// Check the strings
		$rule->set_value( 'abcdefghi' );
		$this->assertFalse( $rule->validate() );

		$rule->set_value( 'abcdefghij' );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( 'abcdefghijk' );
		$this->assertFalse( $rule->validate() );

		// Check the multi-byte strings
		$rule->set_value( 'あいうえおかきくけ' );
		$this->assertFalse( $rule->validate() );

		$rule->set_value( 'あいうえおかきくけこ' );
		$this->assertTrue( $rule->validate() );

		$rule->set_value( 'あいうえおかきくけこさ' );
		$this->assertFalse( $rule->validate() );
	}
}