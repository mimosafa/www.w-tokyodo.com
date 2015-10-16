<?php
namespace WProfile;
use mimosafa\WP as WP;

/**
 * WProfile Plugin Bootstrap Class
 *
 * @package WordPress
 * @subpackage WProfile
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Bootstrap extends Singleton {
	protected function __construct() {
		$this->init();
		if ( is_admin() ) {
			add_action( 'setup_theme', [ $this, 'init_settings_page' ] );
		}
	}
	private function init() {
		Settings::getInstance();
	}
	public function init_settings_page() {
		if ( ! defined( 'WPROFILE_MENU_ID' ) ) {
			define( 'WPROFILE_MENU_ID', 'wprofile-settings' );
		}
		if ( ! defined( 'WPROFILE_MENU_TITLE' ) ) {
			define( 'WPROFILE_MENU_TITLE', __( 'WProfile', 'wprofile' ) );
		}
		if ( ! defined( 'WPROFILE_MENU_ICON' ) ) {
			define( 'WPROFILE_MENU_ICON', 'dashicons-id-alt' );
		}
		$pageInstance = new WP\Settings\Page();
		$pageInstance->page( WPROFILE_MENU_ID, null, WPROFILE_MENU_TITLE );
		$pageInstance->icon_url( WPROFILE_MENU_ICON );
		apply_filters( '_wprofile_settings_page', $pageInstance )->init();
	}
}
