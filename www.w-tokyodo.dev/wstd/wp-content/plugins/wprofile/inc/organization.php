<?php
namespace WProfile;
class Organization extends Base {
	protected $order = 50;
	protected function init() {}
	public function settings_page( $page ) {
		/*
		$page
			->page( 'org-profile', __( 'Organization Profile', 'wprofile' ) )
		;
		*/
		return $page;
	}
}
