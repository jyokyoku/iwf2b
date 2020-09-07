<?php

namespace Iwf2b\Tests\TestCase\User;

use Iwf2b\User\AbstractUser;

class AbstractUserTest extends \WP_UnitTestCase {
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
}

class TestUser extends AbstractUser {
	protected static $role = 'test_role';
}