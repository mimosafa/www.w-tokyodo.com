<?php
namespace WSTD;
use mimosafa\WP\Settings as Settings;

class GoogleAnalytics extends Common {

	protected $priority = 290;
	private $opts;
	private $uaID;

	protected function define_options() {
		$this->opts = Settings\Options::instance( WSTD_OPTGROUP );
		$this->opts->add( 'ga_ua_code' );
	}

	public function settings_page( $page ) {
		$page->section( 'webmaster' )->field( 'google_anaytics' )->option( $this->opts->ga_ua_code, 'text' );
		return $page;
	}

	protected function init() {
		if ( WSTD_CURRENT_ENV === 'product' && $this->uaID = $this->opts->get_ga_ua_code() ) {
			add_action( 'wp_footer', [ $this, 'codes' ] );
		}
	}

	public function codes() {
		if ( is_user_logged_in() ) {
			return;
		}
		echo <<<EOF
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', '{$this->uaID}', 'auto');
  ga('send', 'pageview');
</script>
EOF;
	}

}
