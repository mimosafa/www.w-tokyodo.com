<?php
/**
 * Redirects
 *
 * @since 0.0
 */

/**
 * 301: Company Info Page
 * www.w-tokyodo.com/company => www.w-tokyodo.com#company
 *
 * @since 0.0
 */
function wstd_301_redirect() {
	if ( '/company' === $_SERVER['REQUEST_URI'] ) {
		wp_redirect( home_url() . '#company', '301' );
		exit();
	}
}
add_action( 'init', 'wstd_301_redirect' );

/**
 * 302: Sharyobu & Direct Contents
 *
 * @since 0.0
 */
function wstd_302_redirect() {
	if ( preg_match( '/^\/sharyobu\/+./', $_SERVER['REQUEST_URI'] ) ) {
		wp_redirect( get_permalink( get_page_by_path( 'sharyobu' )->ID ), '302' );
		exit();
	}
	if ( preg_match( '/^\/direct\/+./', $_SERVER['REQUEST_URI'] ) ) {
		wp_redirect( get_permalink( get_page_by_path( 'direct' )->ID ), '302' );
		exit();
	}
}
add_action( 'init', 'wstd_302_redirect' );
add_filter( '404_template', 'wstd_302_redirect' );
