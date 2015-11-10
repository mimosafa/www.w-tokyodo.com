<?php
/**
 * Google Analytics
 *
 * @since 0.0
 */
function wstd_gacode() {
	if ( ! is_user_logged_in() ) {
		echo <<<EOF
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-5722525-1', 'w-tokyodo.com');
  ga('send', 'pageview');

</script>
EOF;
	}
}
add_action( 'wp_footer', 'wstd_gacode' );
