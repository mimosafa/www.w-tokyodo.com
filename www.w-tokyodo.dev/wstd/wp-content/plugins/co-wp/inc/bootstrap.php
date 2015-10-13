<?php
namespace COWP;
use mimosafa\WP as WP;

define( 'COWP_OPTGROUP', 'co_wp_option_group' );

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
		if ( ! defined( 'COWP_MENU_ID' ) ) {
			define( 'COWP_MENU_ID', 'cowp-settings' );
		}
		if ( ! defined( 'COWP_MENU_TITLE' ) ) {
			define( 'COWP_MENU_TITLE', 'CoWP' );
		}
		if ( ! defined( 'COWP_MENU_ICON' ) ) {
			define( 'COWP_MENU_ICON', 'dashicons-building' );
		}
		$page = new WP\Settings\Page( COWP_MENU_ID, null, COWP_MENU_TITLE );
		$page->icon_url( COWP_MENU_ICON );
		apply_filters( '_co_wp_settings_page', $page )->done();
	}

}
