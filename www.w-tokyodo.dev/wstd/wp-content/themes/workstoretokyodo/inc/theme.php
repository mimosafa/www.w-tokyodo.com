<?php
namespace WSTD;

class Theme {

	/**
	 * CDN Styles & Scripts Version
	 *
	 * @var array
	 */
	private $ver = [
		'bootstrap'    => '3.3.5',
		'font-awesome' => '4.4.0',
	];

	public static function init() {
		static $instance;
		$instance ? $instance : $instance = new self();
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
	}

	/**
	 * Enqueue Styles & Scripts
	 *
	 * @access public
	 */
	public function scripts() {
		$this->twitter_bootstrap();
		$this->font_awesome();
		wp_enqueue_style(
			'wstd',
			get_stylesheet_uri(),
			[ 'font-awesone' ],
			date( 'YmdHis', filemtime( get_stylesheet_directory() . '/style.css' ) )
		);
		wp_enqueue_style(
			'tokyodo2014',
			get_stylesheet_directory_uri() . '/css/tokyodo2014.css',
			[ 'wstd' ],
			date( 'YmdHis', filemtime( get_stylesheet_directory() . '/css/tokyodo2014.css' ) )
		);
		wp_enqueue_script(
			'wstd',
			get_stylesheet_directory_uri() . '/js/script.js',
			[ 'bootstrap' ],
			date( 'YmdHis', filemtime( get_stylesheet_directory() . '/js/script.js' ) ),
			true
		);
	}

	/**
	 * CDN Styles & Scripts
	 *
	 * - Twitter Bootstrap
	 * - Font Awesome
	 *
	 * @access private
	 */
	private function twitter_bootstrap() {
		$ver = $this->ver['bootstrap'];
		wp_register_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/' . $ver . '/css/bootstrap.min.css', [], $ver );
		wp_register_script( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/' . $ver . '/js/bootstrap.min.js', [ 'jquery' ], $ver, true );
	}
	private function font_awesome() {
		$ver = $this->ver['font-awesome'];
		wp_enqueue_style( 'font-awesone', '//maxcdn.bootstrapcdn.com/font-awesome/' . $ver . '/css/font-awesome.min.css', [ 'bootstrap' ], $ver );
	}

}
