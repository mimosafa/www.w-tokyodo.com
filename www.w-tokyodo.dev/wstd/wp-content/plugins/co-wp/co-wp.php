<?php
/**
 * Plugin Name: Co-WP
 * Author: Toshimichi Mimoto
 * Text Domain: cowp
 * Domain Path: /languages
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
define( 'COWP_FILE', __FILE__ );
define( 'COWP_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Initialize Co-WP Plugin
 */
function _init_co_wp_plugin() {
	if ( class_exists( 'mimosafa\\ClassLoader' ) ) {
		$params = [
			'hyphenate_classname' => true,
			'hyphenate_namespace' => true
		];
		mimosafa\ClassLoader::register( 'COWP', __DIR__ . '/inc', $params );
		COWP\Bootstrap::getInstance();
	}
}
add_action( 'plugins_loaded', '_init_co_wp_plugin' );
