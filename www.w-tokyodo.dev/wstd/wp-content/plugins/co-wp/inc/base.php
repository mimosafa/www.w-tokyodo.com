<?php
namespace COWP;

abstract class Base extends Singleton {

	protected $order = 10;
	protected $opts;

	protected function __construct() {
		if ( method_exists( $this, 'define_options' ) ) {
			if ( ! $this->opts ) {
				$this->opts = \mimosafa\WP\Settings\Options::instance( COWP_OPTGROUP );
			}
			$this->define_options();
		}
		if ( is_admin() ) {
			add_action( '_co_wp_settings_page', [ $this, 'settings_page' ], $this->order );
		}
		$this->init();
	}

	abstract protected function init();

	public function settings_page( $page ) {
		return $page;
	}

}
