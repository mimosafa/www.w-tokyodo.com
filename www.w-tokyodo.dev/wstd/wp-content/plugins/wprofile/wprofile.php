<?php
/**
 * Plugin Name: WProfile
 * Author: Toshimichi Mimoto
 */
define( 'WPROFILE_FILE', __FILE__ );
define( 'WPROFILE_BASENAME', plugin_basename( WPROFILE_FILE ) );

if ( _wprofile_plugin_requirements() ) {
	add_action( 'plugins_loaded', '_wprofile_plugin_init' );
}

/**
 * Check Requirements for WProfile Plugin
 *
 * @access private
 * @return boolean
 */
function _wprofile_plugin_requirements() {
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
		$e->add( 'error', sprintf( $err_msg_format_php_ver, esc_html( $current_php_ver ), WPROFILE_BASENAME, $required_php_ver ) );
	}
	if ( version_compare( $current_wp_ver, $required_wp_ver, '<' ) ) {
		$err_msg_format_wp_ver = __( '<p>WordPress version %1$s does not meet the requirements to activate <code>%2$s</code>. %3$s or higher will be required.</p>', 'wprofile' );
		$e->add( 'error', sprintf( $err_msg_format_wp_ver, esc_html( $current_wp_ver ), WPROFILE_BASENAME, $required_wp_ver ) );
	}
	if ( $e->get_error_code() ) {
		global $_wprofile_version_error_messages;
		$_wprofile_version_error_messages = $e->get_error_messages();
		add_action( 'admin_notices', '_wprofile_plugin_requirements_error' );
		return false;
	}
	return true;
}

/**
 * Show Error Message and Deactivate Plugin
 *
 * @access private
 */
function _wprofile_plugin_requirements_error() {
	global $_wprofile_version_error_messages;
	foreach ( $_wprofile_version_error_messages as $msg ) {
		echo "<div class=\"message error notice is-dismissible\">\n\t{$msg}\n</div>\n";
	}
	deactivate_plugins( WPROFILE_BASENAME, true );
	unset( $_wprofile_version_error_messages );
}

/**
 * Initialize WProfile Plugin
 *
 * @access private
 */
function _wprofile_plugin_init() {
	if ( class_exists( 'mimosafa\\ClassLoader' ) ) {
		$param = array(
			'hyphenate_classname' => true
		);
		mimosafa\ClassLoader::register( 'WProfile', dirname( WPROFILE_FILE ) . '/inc', $param );
		WProfile\Bootstrap::getInstance();
	}
}
