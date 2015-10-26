<?php
namespace WSTD;

class Theme_Settings extends Common {

	protected function init() {
		add_action( 'after_setup_theme', [ $this, 'theme_settings' ] );
	}

	public function theme_settings() {
		$this->immutable_theme_settings();
		$this->mutable_theme_settings();
	}

	public function immutable_theme_settings() {
		add_theme_support( 'post-thumbnails' );
		$this->disable_emoji();
		add_action( 'template_redirect', function() {
			if ( is_page() ) {
				remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
			}
		} );
	}

	public function mutable_theme_settings() {
		add_post_type_support( 'page', 'excerpt' );
	}

	/**
	 * @see http://wordpress.stackexchange.com/questions/185577/disable-emojicons-introduced-with-wp-4-2
	 */
	private function disable_emoji() {
		add_action( 'init', function() {
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
			remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
			remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
			add_filter( 'tiny_mce_plugins', function( $plugins ) {
				return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : [];
			} );
		} );
	}

}
