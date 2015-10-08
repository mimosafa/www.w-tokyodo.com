<?php
namespace COWP;

class Settings extends Base {

	protected $order = 9999;

	protected function define_options() {
		$this->general_options();
	}

	private function general_options() {
		$this->opts
			->add( 'activate_profile', 'boolean' )
			->add( 'activate_division', 'boolean' )
		;
	}

	protected function init() {
		if ( $this->opts->get_org_name_local() && $this->opts->get_admin_ttl() ) {
			define( 'COWP_MENU_TITLE', esc_html( $this->opts->get_orgname() ) );
		}
	}

	public function settings_page( $page ) {
		$page
		->init( 'cowp-settings', 'General Settings', 'Settings' )
			->section( 'general' )
				->field( 'activate_profile' )
					->option( $this->opts->activate_profile, 'checkbox' )
				->field( 'activate_division' )
					->option( $this->opts->activate_division, 'checkbox' )
		;
		return $page;
	}

}
