<?php
use Iwf2b\Log;

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