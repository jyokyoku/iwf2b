<?php

namespace Iwf2b;

class Debug {
	/**
	 * @param $_args
	 */
	public static function dump( $_args ) {
		$args      = func_get_args();
		$backtrace = debug_backtrace();

		if ( strpos( $backtrace[0]['file'], __FILE__ ) !== false ) {
			$callee = $backtrace[1];

		} else {
			$callee = $backtrace[0];
		}

		echo '<div style="text-align: left !important; font-size: 13px;background: #EEE !important; border:1px solid #666; color: #000 !important; padding:10px; position: relative; z-index: 999999;">';
		echo '<h1 style="border-bottom: 1px solid #CCC; padding: 0 0 5px 0; margin: 0 0 5px 0; font: bold 120% sans-serif;">' . $callee['file'] . ' @ line: ' . $callee['line'] . '</h1>';
		echo '<pre style="overflow:auto;font-size:100%;">';

		for ( $i = 1, $max = count( $args ); $i <= $max; $i ++ ) {
			echo '<strong>Variable #' . $i . ':</strong>' . PHP_EOL;
			var_dump( $args[ $i - 1 ] );
			echo PHP_EOL . PHP_EOL;
		}

		echo "</pre>";
		echo "</div>";
	}
}