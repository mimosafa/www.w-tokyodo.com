<?php
namespace WSTD;

abstract class Common extends Singleton {

	protected $priority = 10;

	protected function __construct() {
		if ( method_exists( $this, 'define_options' ) )
			$this->define_options();
		add_action( '_workstoretokyodo_settings_page', [ $this, 'settings_page' ], $this->priority );
		$this->init();
	}

	abstract protected function init();

	public function settings_page( $page ) {
		return $page;
	}

}
