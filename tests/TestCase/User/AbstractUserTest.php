<?php

namespace Iwf2b\Tests\TestCase\User;

use Iwf2b\User\AbstractUser;

class AbstractUserTest extends \WP_UnitTestCase {
	public function test_register() {
		TestUser::get_instance();

		$user_id = $this->factory->user->create( [
			'user_login' => 'test1',
			'user_email' => 'test1@test.com',
			'user_pass'  => 'pass',
			'role'       => 'test_role',
		] );

		$this->assertTrue( metadata_exists( 'user', $user_id, 'defined_var' ) );
		$this->assertFalse( metadata_exists( 'user', $user_id, 'undefined_var' ) );
	}

	public function test_get() {
		$user_id = $this->factory->user->create( [
			'user_login' => 'test1',
			'user_email' => 'test1@test.com',
			'user_pass'  => 'pass',
			'role'       => 'test_role',
		] );

		$this->assertEmpty( TestUser::get( 0 ) );

		$userdata = get_userdata( $user_id );

		$this->assertEquals( $userdata, TestUser::get( $user_id ) );
		$this->assertEquals( $userdata, TestUser::get( 'test1' ) );
		$this->assertEquals( $userdata, TestUser::get( 'test1@test.com' ) );
	}

	public function test_get_id() {
		$user_id = $this->factory->user->create( [
			'user_login' => 'test2',
			'user_email' => 'test2@test.com',
			'user_pass'  => 'pass',
			'role'       => 'test_role',
		] );

		$this->assertEquals( 0, TestUser::get_id( 1000 ) );
		$this->assertEquals( $user_id, TestUser::get_id( $user_id ) );
		$this->assertEquals( $user_id, TestUser::get_id( 'test2' ) );
		$this->assertEquals( $user_id, TestUser::get_id( 'test2@test.com' ) );
	}

	public function test_meta_operations_using_meta_key_constant() {
		TestUser2::get_instance();

		$user_id = $this->factory->user->create( [
			'user_login' => 'test3',
			'user_email' => 'test3@test.com',
			'user_pass'  => 'pass',
			'role'       => TestUser2::get_role(),
		] );

		$expected = 'test1234';
		TestUser2::MK_SCALAR_VAR( $user_id, $expected );

		$this->assertEquals( $expected, TestUser2::MK_SCALAR_VAR( $user_id ) );

		$expected = [ 1 => 'var1', 2 => 'var2' ];
		TestUser2::MK_ARRAY_VAR( $user_id, $expected );

		$this->assertEquals( $expected, TestUser2::MK_ARRAY_VAR( $user_id ) );

		TestUser2::clear_meta( $user_id );

		$this->assertEmpty( TestUser2::MK_SCALAR_VAR( $user_id ) );
		$this->assertEmpty( TestUser2::MK_ARRAY_VAR( $user_id ) );
	}
}

class TestUser extends AbstractUser {
	const MK_DEFINED_VAR = 'defined_var';

	protected $role = 'test_role';
}

class TestUser2 extends AbstractUser {
	const MK_SCALAR_VAR = 'scalar_var';
	const MK_ARRAY_VAR = 'array_Var';

	protected $role = 'test_role2';
}
