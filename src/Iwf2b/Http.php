<?php

namespace Iwf2b;

/**
 * Class Http
 * @package Iwf2b
 */
class Http {
	/**
	 * @var array
	 */
	protected static $request_detector = [
		'get'     => [ 'env' => 'REQUEST_METHOD', 'value' => 'GET' ],
		'post'    => [ 'env' => 'REQUEST_METHOD', 'value' => 'POST' ],
		'put'     => [ 'env' => 'REQUEST_METHOD', 'value' => 'PUT' ],
		'patch'   => [ 'env' => 'REQUEST_METHOD', 'value' => 'PATCH' ],
		'delete'  => [ 'env' => 'REQUEST_METHOD', 'value' => 'DELETE' ],
		'head'    => [ 'env' => 'REQUEST_METHOD', 'value' => 'HEAD' ],
		'options' => [ 'env' => 'REQUEST_METHOD', 'value' => 'OPTIONS' ],
		'ssl'     => [ 'env' => 'HTTPS', 'options' => [ 1, 'on' ] ],
		'ajax'    => [ 'env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest' ],
		'flash'   => [ 'env' => 'HTTP_USER_AGENT', 'pattern' => '/^(Shockwave|Adobe) Flash/' ],
	];

	/**
	 * Check current request type
	 *
	 * @param $type
	 *
	 * @return bool
	 */
	public static function request_is( $type ) {
		if ( is_array( $type ) ) {
			$result = array_map( [ get_called_class(), 'is' ], $type );

			return count( array_filter( $result ) ) > 0;
		}

		$type = strtolower( $type );

		if ( ! isset( static::$request_detector[ $type ] ) ) {
			return false;
		}

		$params = static::$request_detector[ $type ];

		if ( isset( $params['env'] ) ) {
			if ( isset( $params['value'] ) ) {
				return Arr::get( $_SERVER, $params['env'] ) == $params['value'];
			}

			if ( isset( $params['pattern'] ) ) {
				return (bool) preg_match( $params['pattern'], Arr::get( $_SERVER, $params['env'] ) );
			}

			if ( isset( $params['options'] ) ) {
				$pattern = '/' . implode( '|', $params['options'] ) . '/i';

				return (bool) preg_match( $pattern, Arr::get( $_SERVER, $params['env'] ) );
			}
		}

		return false;
	}

	/**
	 * Detect client ip address
	 *
	 * @param bool $proxy If true ignore proxy ip addresses
	 *
	 * @return string
	 */
	public static function get_ip( $proxy = false ) {
		if ( $proxy && Arr::get( $_SERVER, 'HTTP_X_FORWARDED_FOR' ) ) {
			$addresses = explode( ',', Arr::get( $_SERVER, 'HTTP_X_FORWARDED_FOR' ) );
			$ip        = end( $addresses );

		} elseif ( $proxy && Arr::get( $_SERVER, 'HTTP_CLIENT_IP' ) ) {
			$ip = Arr::get( $_SERVER, 'HTTP_CLIENT_IP' );

		} else {
			$ip = Arr::get( $_SERVER, 'REMOTE_ADDR' );
		}

		return trim( $ip );
	}

	/**
	 * @param array $auth_list
	 * @param string $realm
	 * @param string $failed_text
	 *
	 * @return mixed
	 */
	public static function basic_auth( array $auth_list, $realm = 'Restricted Area', $failed_text = 'Authentication Failed.' ) {
		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $auth_list[ $_SERVER['PHP_AUTH_USER'] ] ) ) {
			if ( $auth_list[ $_SERVER['PHP_AUTH_USER'] ] == $_SERVER['PHP_AUTH_PW'] ) {
				return $_SERVER['PHP_AUTH_USER'];
			}
		}

		header( 'WWW-Authenticate: Basic realm="' . $realm . '"' );
		header( 'HTTP/1.0 401 Unauthorized' );

		exit( $failed_text );
	}
}
