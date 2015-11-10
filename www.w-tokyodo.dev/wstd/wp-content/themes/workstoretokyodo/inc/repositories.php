<?php
/**
 * Post Types & Taxonomies
 *
 * @since 0.0
 */

/**
 * カスタム投稿タイプ - event-works
 */
add_action( 'init', function() {
	/**
	 * イベントデータ
	 */
	register_post_type( 'event', array(
		'labels' => array( 'name' => 'イベント実績' ),
		'public' => true,
		'has_archive' => true,
		'menu_position' => 5,
		'menu_icon' => 'dashicons-smiley',
		'taxonomies' => array( 'works' )
	) );
	/**
	 * 業務種別
	 */
	register_taxonomy( 'works', array( 'event', 'attachment' ), array(
		'labels' => array( 'name' => '業務種別' ),
		'public' => true
	) );
} );
