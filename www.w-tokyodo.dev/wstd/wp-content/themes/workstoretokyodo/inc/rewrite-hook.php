<?php
/**
 * Hook for Rewrites Managable - "wstd_rewrites_managable"
 *
 * @since 0.1
 */

/**
 * Theme Enabled
 *
 * @since 0.1
 */
add_action( 'after_setup_theme', function() {
	if ( ! get_option( 'workstoretokyodo_theme_activated' ) ) {
		update_option( 'workstoretokyodo_theme_activated', 1 );
		do_action( 'wstd_rewrites_managable' ); // Action Hook !
		flush_rewrite_rules();
	}
} );

/**
 * Flushed Rewrite Rules
 *
 * @since 0.1
 */
add_action( 'delete_option', function( $option ) {
	if ( $option === 'rewrite_rules' && get_option( 'workstoretokyodo_theme_activated' ) ) {
		do_action( 'wstd_rewrites_managable' ); // Action Hook !
	}
} );

/**
 * Theme Disabled - Reset(Flush) Rwrite Rules
 *
 * @since 0.1
 * @see https://firegoby.jp/archives/5309 # Thx!!!!
 */
add_action( 'switch_theme', function() {
	delete_option( 'workstoretokyodo_theme_activated' );
	flush_rewrite_rules();
} );
