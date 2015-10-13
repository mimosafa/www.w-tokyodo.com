<?php
namespace WProfile;
class Organization extends Base {
	protected $order = 50;
	protected function init() {}
	protected function define_options() {}
	public function settings_page( $page ) {
		$page
			->init( 'org-profile', __( 'Organization Profile', 'wprofile' ) )
		;
		return $page;
	}
}
