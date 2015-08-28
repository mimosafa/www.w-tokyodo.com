<?php

add_shortcode( 'pt_works', 'show_works' );
function show_works( $atts ) {
	$args = array( 'post_type' => 'event', 'numberposts' => -1, 'order' => 'ASC' );
	$term_works = array( 'management', 'collaboration', 'store', 'official-bar' );
	extract( shortcode_atts( array(
		'work' => ''
	), $atts ) );
	if ( in_array( $work, $term_works ) ) {
		$args['taxonomy'] = 'works';
		$args['term'] = $work;
	}
	$works = get_posts( $args );
	$str = '';
	foreach ( $works as $work ) {
		$str .= esc_html( $work->post_title );
		$str .= ' / ';
	}
	$str = substr( $str, 0, -3 );
	$str .= ' and more...';
	return $str;
}

/**
 * admin bar を消す
 */
/*
function kill_admin_bar() {
    add_filter( 'show_admin_bar', '__return_false', 1000 );
    if ( class_exists( 'crazy_bone' ) ) {
        function deregister() {
            wp_deregister_script( 'wp-pointer' );
            wp_deregister_style( 'wp-pointer' );
        }
        add_action( 'wp_enqueue_scripts', 'deregister', 9999 );
    }
}
add_action( 'init', 'kill_admin_bar' );
*/