<?php

/**
 *
 */
function open_loop() {
	echo '<section ';
	post_class();
	echo '>';
	echo "\n";
}
function close_loop() {
	echo '</section>';
	echo "\n";
}
add_action( 'wstd_loop_open', 'open_loop' );
add_action( 'wstd_loop_close', 'close_loop' );

function wstd_loop( $post ) {
	get_template_part( 'loop' );
}
add_filter( 'wstd_loop', 'wstd_loop' );

/*
function wstd_title( $title, $id ) {
	// 'is_main_query'が効かないな～～～～
	global $wp_the_query;
	if ( $id !== $wp_the_query->queried_object_id )
		return $title;
	if ( is_division() && is_page_top() )
		return '';
	return '<h2>' . $title . '</h2>';
}
add_filter( 'the_title', 'wstd_title', 99, 2 );
*/
