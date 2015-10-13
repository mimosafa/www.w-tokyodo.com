<?php
namespace COWP;

class Profile extends Base {

	protected $order = 100;

	protected function define_options() {
		//
	}

	public function settings_page( $page ) {
		$page->init( 'cowp-profile', 'Profile' );
		if ( $orgname = $this->opts->get_orgname() ) {
			$page->page_title( sprintf( 'Profile of %s', esc_html( $orgname ) ) );
		}
		$page
		->menu_title( 'Profile' )
			->section( 'profile', 'Profile' )
		;
		return $page;
	}

	protected function init() {}

}
