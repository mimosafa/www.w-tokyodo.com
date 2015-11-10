<?php
namespace WSTD;

class Compat {

	public static function init() {
		static $instance;
		$instance ? $instance : $instance = new self();
	}

	private function __construct() {
		//
	}

}
