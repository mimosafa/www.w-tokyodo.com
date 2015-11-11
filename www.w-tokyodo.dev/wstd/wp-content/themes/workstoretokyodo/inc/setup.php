<?php
/**
 * Theme Set Up Settings
 *
 * @since 0.0
 */

add_theme_support( 'title-tag' ); // Title Tag
add_theme_support( 'post-thumbnails' ); // Post Thumbnails
add_action( 'wp_enqueue_scripts', 'wstd_common_enqueue_scripts' ); // Styles & JavaScripts

/**
 * Remove Rel Links for Page
 *
 * @since 0.0
 */
add_action( 'template_redirect', function() {
	if ( is_page() ) {
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	}
} );

/**
 * Customize Gallery Style
 *
 * @since 0.0
 */
add_filter( 'gallery_style', function() {
	return '<div class="wstd-gallery">';
} );

/**
 * Styles & JavaScripts Enqueue
 *
 * @since 0.0
 */
function wstd_common_enqueue_scripts() {
	/**
	 * Register
	 */
	wstd_twitter_bootstrap( '3.3.5' );
	wstd_font_awesome( '4.4.0' );
	wstd_styles_scripts();
	/**
	 * Enqueue
	 */
	wp_enqueue_style( 'wstd' );
	wp_enqueue_style( 'tokyodo2014' );
	wp_enqueue_script( 'wstd' );
}

/**
 * Register Twitter Bootstrap
 *
 * @since 0.1
 * @see http://getbootstrap.com/getting-started/
 * @param string $ver # Twitter Bootstrap Version
 */
function wstd_twitter_bootstrap( $ver ) {
	$cssCDN = '//maxcdn.bootstrapcdn.com/bootstrap/%s/css/bootstrap.min.css';
	$jsCDN  = '//maxcdn.bootstrapcdn.com/bootstrap/%s/js/bootstrap.min.js';
	wp_register_style( 'bootstrap', sprintf( $cssCDN, esc_attr( $ver ) ), [], $ver );
	wp_register_script( 'bootstrap', sprintf( $jsCDN, esc_attr( $ver ) ), [ 'jquery' ], $ver, false );
}

/**
 * Register Font Awesome
 *
 * @since 0.1
 * @see http://fortawesome.github.io/Font-Awesome/get-started/
 * @param string $ver # Font Awesome Version
 */
function wstd_font_awesome( $ver ) {
	$cdn = '//maxcdn.bootstrapcdn.com/font-awesome/%s/css/font-awesome.min.css';
	wp_register_style( 'fontawesome', sprintf( $cdn, esc_attr( $ver ) ), [ 'bootstrap' ], $ver );
}

/**
 * Register Theme Styles & JavaScripts
 *
 * @since 0.1
 */
function wstd_styles_scripts() {
	// Stylesheets
	$styles = [
		// Theme Style
		[
			'wstd',
			get_stylesheet_uri(),
			[ 'fontawesome' ],
			date( 'YmdHis', filemtime( get_stylesheet_directory() . '/style.css' ) )
		],
		// Workstore Tokyo Do SVG Icon Style
		[
			'tokyodo2014',
			get_stylesheet_directory_uri() . '/css/tokyodo2014.css',
			[ 'wstd' ],
			date( 'YmdHis', filemtime( get_stylesheet_directory() . '/css/tokyodo2014.css' ) )
		]
	];
	foreach ( $styles as $style ) {
		call_user_func_array( 'wp_register_style', $style );
	}
	// JavaScripts
	$scripts = [
		// Theme Script
		[
			'wstd',
			get_stylesheet_directory_uri() . '/js/script.js',
			[ 'bootstrap' ],
			date( 'YmdHis', filemtime( get_stylesheet_directory() . '/js/script.js' ) ),
			true
		]
	];
	foreach ( $scripts as $script ) {
		call_user_func_array( 'wp_register_script', $script );
	}
}
