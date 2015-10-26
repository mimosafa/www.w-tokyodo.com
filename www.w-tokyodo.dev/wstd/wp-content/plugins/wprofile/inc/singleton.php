<?php
namespace WProfile;

abstract class Singleton {
	public static function getInstance() {
		static $instance;
		return $instance ?: $instance = new static();
	}
	public function __clone() {}
	public function __wakeup() {}
}
