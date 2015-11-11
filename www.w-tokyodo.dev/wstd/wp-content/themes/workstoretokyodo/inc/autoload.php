<?php
/**
 * Register Class Loader
 *
 * @since 0.1
 */
spl_autoload_register( 'wstd_classloader' );

/**
 * Auto Loader
 *
 * @since 0.1
 */
function wstd_classloader( $class ) {
	$strings = explode( '\\', $class );
	$n = count( $strings );
	if ( count( $strings ) > 1 && $strings[0] === 'WSTD' ) {
		$path = TEMPLATEPATH . '/app/';
		$n--;
		for ( $i = 1; $i <= $n; $i++ ) {
			$path .= '/' . $strings[$i];
			if ( $i === $n ) {
				$path .= '.php';
			}
		}
		if ( is_readable( $path ) ) {
			require_once $path;
		}
	}
}
