<?php
namespace WProfile;

/**
 * Abstract Class for WProfile Bootstrap Classes
 *
 * @package WordPress
 * @subpackage WProfile
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
abstract class Base extends Singleton {

	/**
	 * Order for Constructing Settings Page
	 *
	 * @var int
	 */
	protected $order = 10;

	/**
	 * Options instance
	 *
	 * @var mimosafa\WP\Settings\Options
	 */
	protected $opts;

	protected function __construct() {
		if ( ! $this->opts ) {
			$this->opts = \mimosafa\WP\Settings\Options::instance( 'wprofile_option' );
		}
		$this->init();
		if ( is_admin() ) {
			add_action( '_wprofile_settings_page', [ $this, 'settings_page' ], $this->order );
		}
	}
	abstract protected function init();
	public function settings_page( $page ) {
		return $page;
	}
}
