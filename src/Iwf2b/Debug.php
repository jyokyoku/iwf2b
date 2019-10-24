<?php

namespace Iwf2b;

/**
 * Class Debug
 * @package Iwf2b
 */
class Debug {
	/**
	 * @param $_args
	 */
	public static function dump( $_args ) {
		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 );

		foreach ( $backtrace as $stack => $trace ) {
			if ( isset( $trace['file'] ) ) {
				if ( strpos( $trace['file'], __DIR__ . '/helpers.php' ) !== false ) {
					$callee = $backtrace[ $stack + 1 ];

				} else {
					$callee = $trace;
				}

				break;
			}
		}

		$arguments = func_get_args();

		echo '<div style="text-align: left !important; font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px; position: relative; z-index: 999999;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">' . $callee['file'] . ' @ line: ' . $callee['line'] . '</h1>';
		echo '<pre style="overflow:auto;font-size:100%;">';

		$count = count( $arguments );

		for ( $i = 1; $i <= $count; $i ++ ) {
			echo '<strong>Variable #' . $i . ':</strong>' . PHP_EOL;
			var_dump( $arguments[ $i - 1 ] );
			echo PHP_EOL . PHP_EOL;
		}

		echo "</pre>";
		echo "</div>";
	}
}