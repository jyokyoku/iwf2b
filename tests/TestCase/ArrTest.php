<?php

namespace Iwf2b\Tests\TestCase;

use Iwf2b\Arr;

class ArrTest extends \WP_UnitTestCase {
	public function array_provider() {
		return [
			'base_set' => [
				[
					'key1' => [
						'value3',
						'value4',
						'key3' => [
							'value7',
							'value8',
						],
						'key4' => 'value9',
					],
					'value1',
					'value2',
					'key2' => [
						'value5',
						'value6',
					],
				],
			],
		];
	}

	/**
	 * @dataProvider array_provider
	 */
	public function test_get( $array ) {
		$this->assertEquals( [
			'value3',
			'value4',
			'key3' => [
				'value7',
				'value8',
			],
			'key4' => 'value9',
		], Arr::get( $array, 'key1' ) );

		$this->assertEquals( 'value1', Arr::get( $array, '0' ) );

		$this->assertEquals( [
			'value7',
			'value8',
		], Arr::get( $array, 'key1.key3' ) );

		$this->assertEquals( 'value8', Arr::get( $array, 'key1.key3.1' ) );

		$this->assertNull( Arr::get( $array, 'key1.invalid_key' ) );

		$this->assertEquals( 'default_value', Arr::get( $array, 'key1.invalid_key', 'default_value' ) );
	}

	/**
	 * @dataProvider array_provider
	 */
	public function test_get_array( $array ) {
		$this->assertEquals( [
			'key1' => [
				'value3',
				'value4',
				'key3' => [
					'value7',
					'value8',
				],
				'key4' => 'value9',
			],
			'value1',
		], Arr::get( $array, [ 'key1', '0' ] ) );

		$this->assertEquals( [
			'key1.0'    => 'value3',
			'key1.key3' => [
				'value7',
				'value8',
			],
		], Arr::get( $array, [ 'key1.0', 'key1.key3' ] ) );

		$this->assertEquals( [
			'key1.1'      => 'value4',
			'invalid_key' => null,
		], Arr::get( $array, [ 'key1.1', 'invalid_key' ] ) );

		$this->assertEquals( [
			'key1.1'      => 'value4',
			'invalid_key' => 'default_value',
			'key1.key3.0' => 'value7',
		], Arr::get( $array, [ 'key1.1', 'invalid_key' => 'default_value', 'key1.key3.0' ] ) );
	}

	/**
	 * @dataProvider array_provider
	 */
	public function test_set( $array ) {
		$array1 = $array;
		Arr::set( $array1, 'key5', 'value10' );

		$this->assertEquals( [
			'key1' => [
				'value3',
				'value4',
				'key3' => [
					'value7',
					'value8',
				],
				'key4' => 'value9',
			],
			'value1',
			'value2',
			'key2' => [
				'value5',
				'value6',
			],
			'key5' => 'value10',
		], $array1 );

		$array2 = $array;
		Arr::set( $array2, 'key2.key5', 'value10' );

		$this->assertEquals( [
			'key1' => [
				'value3',
				'value4',
				'key3' => [
					'value7',
					'value8',
				],
				'key4' => 'value9',
			],
			'value1',
			'value2',
			'key2' => [
				'value5',
				'value6',
				'key5' => 'value10',
			],
		], $array2 );

		$array3 = $array;
		Arr::set( $array3, 'key1.1', 'value10' );

		$this->assertEquals( [
			'key1' => [
				'value3',
				'value10',
				'key3' => [
					'value7',
					'value8',
				],
				'key4' => 'value9',
			],
			'value1',
			'value2',
			'key2' => [
				'value5',
				'value6',
			],
		], $array3 );

		$array4 = $array;
		Arr::set( $array4, 'key1.key3.0', [
			'value10',
			'value11',
		] );

		$this->assertEquals( [
			'key1' => [
				'value3',
				'value4',
				'key3' => [
					[
						'value10',
						'value11',
					],
					'value8',
				],
				'key4' => 'value9',
			],
			'value1',
			'value2',
			'key2' => [
				'value5',
				'value6',
			],
		], $array4 );

		$array5 = $array;
		Arr::set( $array5, 'key1.key4.1', [
			'value10',
			'value11',
		] );

		$this->assertEquals( [
			'key1' => [
				'value3',
				'value4',
				'key3' => [
					'value7',
					'value8',
				],
				'key4' => [
					1 => [
						'value10',
						'value11',
					],
				],
			],
			'value1',
			'value2',
			'key2' => [
				'value5',
				'value6',
			],
		], $array5 );
	}

	/**
	 * @dataProvider array_provider
	 */
	public function test_set_array( $array ) {
		$array1 = $array;
		Arr::set( $array1, [ 'key5' => 'value10', 'key1.key3.0' => 'value11' ] );

		$this->assertEquals( [
			'key1' => [
				'value3',
				'value4',
				'key3' => [
					'value11',
					'value8',
				],
				'key4' => 'value9',
			],
			'value1',
			'value2',
			'key2' => [
				'value5',
				'value6',
			],
			'key5' => 'value10',
		], $array1 );

		$array2 = $array;
		Arr::set( $array2, [
			'key1.key4.1' => [
				'value10',
				'value11',
			],
			'key2.0.key5' => 'value12',
			'value13',
		] );

		$this->assertEquals( [
			'key1' => [
				'value3',
				'value4',
				'key3' => [
					'value7',
					'value8',
				],
				'key4' => [
					1 => [
						'value10',
						'value11',
					]
				],
			],
			'value13',
			'value2',
			'key2' => [
				[ 'key5' => 'value12' ],
				'value6',
			],
		], $array2 );
	}
}