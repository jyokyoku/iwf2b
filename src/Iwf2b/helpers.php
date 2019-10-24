<?php
use Iwf2b\Log;
use Iwf2b\Thumb;

if ( ! function_exists( 'iwf_dump' ) ) {
	/**
	 * @param mixed $_args
	 */
	function iwf_dump( $_args ) {
		call_user_func_array( 'Iwf2b\Debug::dump', func_get_args() );
	}
}

if ( ! function_exists( 'iwf_log' ) ) {
	/**
	 * @return Log
	 */
	function iwf_log() {
		return Log::get_instance();
	}
}

if ( ! function_exists( 'iwf_thumb' ) ) {
	/**
	 * @param string $src
	 * @param int $width
	 * @param int $height
	 * @param array $args
	 *
	 * @return string
	 */
	function iwf_thumb( $src, $width = null, $height = null, array $args = [] ) {
		return Thumb::get_endpoint_url( $src, $width, $height );
	}
}