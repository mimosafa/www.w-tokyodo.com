<?php
namespace WProfile;
use mimosafa\WP as WP;

/**
 * Settings Bootstrap Class
 *
 * @package WordPress
 * @subpackage WProfile
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Settings extends Base {

	/**
	 * Submenu Order: Settings located Bottom
	 *
	 * @var int
	 */
	protected $order = 9999;

	protected function init() {
		$this->define_options();
		$org_profile = $this->opts->get_activate_org_profile();
		if ( $org_profile ) {
			define( 'WPROFILE_MENU_ID', 'wprofile' );
		}
		if ( $org_profile ) {
			Organization::getInstance();
		}
	}

	protected function define_options() {
		$this->org_options();
		$this->extension_options();
	}
	private function org_options() {
		$this->opts
			->add( 'activate_org_profile', 'boolean' )
			->add( 'org_name' )
			->add( 'org_abbr' )
		;
	}
	private function extension_options() {
		$this->opts
			->add( 'activate_extension', 'boolean' )
		;
	}
	public function settings_page( $page ) {
		if ( WPROFILE_MENU_ID === 'wprofile' ) {
			$page->page( 'wprofile-settings' )->menu_title( __( 'Settings', 'wprofile' ) );
		}
		$page
		->title( __( 'WProfile General Settings', 'wprofile' ) )
			->section( 'org-settings', __( 'Organization Settings', 'wprofile' ) )
				->field( 'org-profile', __( 'Enable Organization Profile' ) )
					->option( 'activate_org_profile', 'checkbox' )
					->sanitize( 'esc_html' )
		;
		if ( $this->opts->get_activate_org_profile() ) {
			$page
			->field( 'org-name', __( 'Organization Name', 'wprofile' ) )
				->option( 'org_name', 'text', 'esc_html' )
				->attr_size( 80 )
			->field( 'org-abbr', __( 'Organization Abbreviation' ) )
				->option( 'org_abbr', 'text', 'esc_html' )
				->attr_size( 30 )
			;
		}
		$page
		->section( 'extend-personal-profile', __( 'Extend Personal Profile', 'wprofile' ) )
			->field( 'activate-extension' )
				->option( 'activate_extension', 'checkbox' )
		;
		return $page;
	}
}
