<?php
namespace WSTD;
use mimosafa\WP as WP;

define( 'WSTD_DEVELOP_SERVER', 'www.w-tokyodo.dev' );
define( 'WSTD_PRODUCT_SERVER', 'www.w-tokyodo.com' );
define( 'WSTD_OPTGROUP', 'workstoretokyodo' );

class Bootstrap extends Singleton {

	protected function __construct() {
		$this->define_environment();
		$this->init();
		add_action( 'setup_theme', [ $this, 'init_settings_page' ] );
		$this->add_settings_page();
	}

	private function define_environment() {
		$server_name = filter_input( \INPUT_SERVER, 'SERVER_NAME' );
		if ( $server_name === WSTD_PRODUCT_SERVER ) {
			define( 'WSTD_CURRENT_ENV', 'product' );
		} else {
			define( 'WSTD_CURRENT_ENV', 'develop' );
		}
	}

	private function init() {
		GoogleAnalytics::getInstance();
		Theme_Settings::getInstance();
	}

	public function init_settings_page() {
		$page = new WP\Settings\Page( 'workstoretokyodo', null, 'WSTD' );
		$page = apply_filters( '_workstoretokyodo_settings_page', $page );
		$page->done();
	}

	private function add_settings_page() {
		/**
		 * Company Information Page
		 */
		add_filter( '_workstoretokyodo_settings_page', function( $page ) {
			return $page->init( 'about-company' );
		}, 0 );
		/**
		 * Divisions of Company
		 */
		add_filter( '_workstoretokyodo_settings_page', function( $page ) {
			return $page->init( 'divisions' );
		}, 100 );

		add_filter( '_workstoretokyodo_settings_page', function( $page ) {
			return $page->init( 'webmasters' );
		}, 200 );
	}

}
