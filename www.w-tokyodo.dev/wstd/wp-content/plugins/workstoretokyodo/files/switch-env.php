<?php
/**
 * Switch Environment
 */

class WSTD_Switch_ENV {

	private $env_develop = 'www.w-tokyodo.dev';
	# private $env_staging = 'www.w-tokyodo.stg';
	private $env_product = 'www.w-tokyodo.com';

	/**
	 * @var string
	 */
	private $env;

	/**
	 * @var string
	 */
	private $uaID = 'UA-5722525-1';

	/**
	 * Constructor
	 *
	 */
	public function __construct() {
		$server_name = filter_input( INPUT_SERVER, 'SERVER_NAME' );
		if ( $server_name === $this->env_product )
			$this->env = 'product';
		/*
		elseif ( $server_name === $this->env_staging )
			$this->env = 'staging';
		*/
		elseif ( $server_name === $this->env_develop )
			$this->env = 'develop';

		$this->init();
	}

	private function init() {
		$method = 'init_' . $this->env;
		if ( method_exists( __CLASS__, $method ) )
			$this->$method();
	}

	/**
	 * Initialize Product Environment
	 */
	private function init_product() {
		add_action( 'wp_footer', array( &$this, 'ga' ) );
	}

	/**
	 * Print Google Analytics Tracking CODE
	 *
	 * @access private
	 * @return void
	 */
	public function ga() {
		if ( is_user_logged_in() )
			return;
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

new WSTD_Switch_ENV;
