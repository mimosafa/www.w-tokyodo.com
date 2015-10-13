<?php
namespace COWP;

class Settings extends Base {

	protected $order = 9999;

	protected function define_options() {
		$this->org_options();
		$this->general_options();
	}

	private function org_options() {
		$this->opts
			->add( 'orgname' )
		;
	}

	private function general_options() {
		$this->opts
			->add( 'activate_profile', 'boolean' )
			->add( 'activate_division', 'boolean' )
		;
	}

	protected function init() {
		$actProf = $this->opts->get_activate_profile();
		$actDivi = $this->opts->get_activate_division();
		if ( $actProf || $actDivi ) {
			define( 'COWP_MENU_ID', 'cowp' );
		}
		if ( $actProf ) {
			Profile::getInstance();
		}
	}

	public function settings_page( $page ) {
		if ( COWP_MENU_ID === 'cowp' ) {
			$page->init( 'cowp-settings', 'General Settings', 'Settings' );
		} else if ( COWP_MENU_ID === 'cowp-settings' ) {
			$page->page_title( 'General Settings' );
		}
		$page
			->section( 'organization', 'Organization Settings' )
				->field( 'orgname', 'Organization Name' )
					->option( $this->opts->orgname, 'text' )
			->section( 'available-features', 'Available Features' )
				->field( 'activate_profile' )
					->option( $this->opts->activate_profile, 'checkbox' )
				->field( 'activate_division' )
					->option( $this->opts->activate_division, 'checkbox' )
		;
		return $page;
	}

}
