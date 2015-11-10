<?php

class css_bootstrap_layout {

	function init() {
		add_action( 'template_redirect', array( $this, 'grid_column' ) );
	}

	/**
	 * bootstrap grid system
	 */
	function grid_column() {
		if ( !( is_division() && is_page_top() ) ) {
			$this->contents_wrap_inner();
			$this->main_column();
			$this->side_column();
			add_action( 'wstd_get_sidebar', 'get_sidebar' );
		}
	}

	/**
	 * bootstrap grid system
	 * - .row
	 */
	function contents_wrap_inner() {
		add_action( 'wstd_contents_inner_wrapper_open', function() {
			echo '<div class="row">';
			echo "\n";
		} );
		add_action( 'wstd_contents_inner_wrapper_close', function() {
			echo "</div>\n";
		} );
	}

	/**
	 * bootstrap grid system
	 * main column - .col-x-n
	 */
	function main_column() {
		add_action( 'wstd_contents_top', function() {
			echo '<div class="col-md-9 col-sm-8">';
			echo "\n";
		}, 10 );
		add_action( 'wstd_contents_bottom', function() {
			echo "</div>\n";
		} );
	}

	/**
	 * bootstrap grid system
	 * side column - .col-x-n
	 */
	function side_column() {
		add_action( 'wstd_sidebar_open', function() {
			echo '<div class="col-md-3 col-sm-4">';
			echo "\n";
		}, 10 );
		add_action( 'wstd_sidebar_close', function() {
			echo "</div>\n";
		} );
	}

}
$bootstrap = new css_bootstrap_layout();
$bootstrap->init();
