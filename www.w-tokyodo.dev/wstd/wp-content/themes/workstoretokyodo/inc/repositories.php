<?php
/**
 * Post Types & Taxonomies
 *
 * @since 0.0
 */
add_action( 'init', 'wstd_repogitories' );
function wstd_repogitories() {
	/**
	 * Post Type: Event
	 */
	register_post_type( 'event', array(
		'labels' => array( 'name' => 'イベント実績' ),
		'public' => true,
		'has_archive'   => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-smiley',
		'taxonomies'    => array( 'works' ),
		'register_meta_box_cb' => 'event_meta_box',
	) );

	/**
	 * Post Type: Works
	 */
	register_taxonomy( 'works', array( 'event', 'attachment' ), array(
		'labels' => array( 'name' => '業務種別' ),
		'public' => true
	) );
	register_taxonomy_for_object_type( 'works', 'attachment' );

	/**
	 * Support Except for Page
	 */
	add_post_type_support( 'page', 'excerpt' );
}
