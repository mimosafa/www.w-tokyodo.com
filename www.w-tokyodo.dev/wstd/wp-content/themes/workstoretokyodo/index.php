<?php

get_header();

/**
 *
 */
do_action( 'wstd_contents_inner_wrapper_open' );

/**
 *
 */
do_action( 'wstd_contents_top' );

/**
 * WordPress Loop
 */
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();

		/**
		 *
		 */
		do_action( 'wstd_loop_open' );

		/**
		 *
		 */
		apply_filters( 'wstd_loop', $post );

		/**
		 *
		 */
		do_action( 'wstd_loop_close' );
	}
}

/**
 *
 */
do_action( 'wstd_contents_bottom' );

do_action( 'wstd_get_sidebar' );

/**
 *
 */
do_action( 'wstd_contents_inner_wrapper_close' );

get_footer();