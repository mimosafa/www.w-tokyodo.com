<?php
/*
Plugin Name: WordPress Libraries by mimosafa
Plugin Uri: https://github.com/mimosafa/wp-mimosafa-libs
Description: Extension Libraries for WordPress
Author: Toshimichi Mimoto
Version: 0.0.1
Author URI: http://mimosafa.me
*/
define( 'WP_MIMOSAFA_LIBS_BASENAME', plugin_basename( __FILE__ ) );

if ( _wp_mimosafa_libs_plugin_requirements() ) {
	require_once dirname( __FILE__ ) . '/bootstrap.php';
}

/**
 * Check Requirements
 *
 * @access private
 * @return boolean
 */
function _wp_mimosafa_libs_plugin_requirements() {
	/**
	 * Required PHP / WordPress Version.
	 */
	$required_php_ver = '5.4';
	$required_wp_ver  = '4.0';

	/**
	 * Current Environment PHP / WordPress Version.
	 */
	$current_php_ver = PHP_VERSION;
	$current_wp_ver  = $GLOBALS['wp_version'];

	$e = new WP_Error();

	if ( version_compare( $current_php_ver, $required_php_ver, '<' ) ) {
		$err_msg_format_php_ver = __( '<p>PHP version %1$s does not meet the requirements to activate <code>%2$s</code>. %3$s or higher will be required.</p>', 'wprofile' );
		$e->add( 'error', sprintf( $err_msg_format_php_ver, esc_html( $current_php_ver ), WP_MIMOSAFA_LIBS_BASENAME, $required_php_ver ) );
	}
	if ( version_compare( $current_wp_ver, $required_wp_ver, '<' ) ) {
		$err_msg_format_wp_ver = __( '<p>WordPress version %1$s does not meet the requirements to activate <code>%2$s</code>. %3$s or higher will be required.</p>', 'wprofile' );
		$e->add( 'error', sprintf( $err_msg_format_wp_ver, esc_html( $current_wp_ver ), WP_MIMOSAFA_LIBS_BASENAME, $required_wp_ver ) );
	}
	if ( $e->get_error_code() ) {
		global $_wp_mimosafa_libs_version_error_messages;
		$_wp_mimosafa_libs_version_error_messages = $e->get_error_messages();
		add_action( 'admin_notices', '_wp_mimosafa_libs_plugin_requirements_error' );
		return false;
	}
	return true;
}

/**
 * Show Error Message and Deactivate Plugin
 *
 * @access private
 */
function _wp_mimosafa_libs_plugin_requirements_error() {
	global $_wp_mimosafa_libs_version_error_messages;
	foreach ( $_wp_mimosafa_libs_version_error_messages as $msg ) {
		echo "<div class=\"message error notice is-dismissible\">\n\t{$msg}\n</div>\n";
	}
	deactivate_plugins( WP_MIMOSAFA_LIBS_BASENAME, true );
	unset( $_wp_mimosafa_libs_version_error_messages );
}
