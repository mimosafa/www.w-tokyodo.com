<?php
/*
Plugin Name: Workstore Tokyo Do Immutable Settings
Author: Toshimichi Mimoto
*/

add_action( 'after_setup_theme', 'wstd_immutable_settings' );

function wstd_immutable_settings() {

	/**
	 * Post Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );

	/**
	 * remove action - wp_head
	 */
	add_action( 'template_redirect', function() {
		if ( is_page() ) {
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
		}
	} );

	/**
	 * 固定ページに抜粋
	 */
	add_post_type_support( 'page', 'excerpt' );

}
