<?php
namespace WProfile;
use mimosafa\WP as WP;
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
			define( 'WPROFILE_MENU_TITLE', 'WProfile' );
		}
		if ( ! defined( 'WPROFILE_MENU_ICON' ) ) {
			define( 'WPROFILE_MENU_ICON', 'dashicons-id-alt' );
		}
		$page = new WP\Settings\Page( WPROFILE_MENU_ID, null, WPROFILE_MENU_TITLE );
		$page->icon_url( WPROFILE_MENU_ICON );
		apply_filters( '_wprofile_settings_page', $page )->done();
	}
}
