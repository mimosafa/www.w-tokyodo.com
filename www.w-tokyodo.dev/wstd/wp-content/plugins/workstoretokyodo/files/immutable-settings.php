<?php
/**
 * Immutable Settings & Hacks
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

	/**
	 * Disable Emoji
	 * @see http://wordpress.stackexchange.com/questions/185577/disable-emojicons-introduced-with-wp-4-2
	 */
	add_action( 'init', function() {
		// all actions related to emojis
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );

		// filter to remove TinyMCE emojis
		add_filter( 'tiny_mce_plugins', function( $plugins ) {
			if ( is_array( $plugins ) )
				return array_diff( $plugins, array( 'wpemoji' ) );
			else
				return array();
		} );
	} );

}
