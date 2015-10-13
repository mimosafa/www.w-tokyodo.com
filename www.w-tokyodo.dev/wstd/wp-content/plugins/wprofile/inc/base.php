<?php
namespace WProfile;
abstract class Base extends Singleton {
	protected $order = 10;
	protected $opts;
	abstract protected function init();
	protected function __construct() {
		if ( method_exists( $this, 'define_options' ) ) {
			if ( ! $this->opts ) {
				$this->opts = \mimosafa\WP\Settings\Options::instance( 'wprofile_option' );
			}
			$this->define_options();
		}
		if ( is_admin() ) {
			add_action( '_wprofile_settings_page', [ $this, 'settings_page' ], $this->order );
		}
		$this->init();
	}
	public function settings_page( $page ) {
		return $page;
	}
}
