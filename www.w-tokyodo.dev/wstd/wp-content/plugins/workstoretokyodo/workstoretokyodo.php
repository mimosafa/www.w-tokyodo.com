<?php
/*
Plugin Name: Workstore Tokyo Do Core Plugin
Author: Toshimichi Mimoto
*/
add_action( 'plugins_loaded', '_init_workstoretokyodo_plugin' );
function _init_workstoretokyodo_plugin() {
	if ( class_exists( 'mimosafa\\ClassLoader' ) ) {
		$params = [
			'hyphenate_classname' => true,
			'hyphenate_namespace' => true
		];
		mimosafa\ClassLoader::register( 'WSTD', __DIR__ . '/inc', $params );
		WSTD\Bootstrap::getInstance();
	}
}
