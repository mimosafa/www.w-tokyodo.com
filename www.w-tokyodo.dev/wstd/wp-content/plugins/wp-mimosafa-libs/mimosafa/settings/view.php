<?php
namespace mimosafa\WP\Settings;

class View {

	public static function instance() {
		static $instance;
		return $instance ?: $instance = new self();
	}

	public function page_callback() {
		//
	}

}
