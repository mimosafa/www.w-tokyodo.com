<?php
/**
 * Short Codes
 *
 * @since 0.1
 */

/**
 * Show Posts Short Code
 * - Code: 'pt_work'
 * - Post Type: Event
 * - Taxonomy: Works
 *
 * @since 0.0
 */
function wstd_show_works( $atts ) {
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
add_shortcode( 'pt_works', 'wstd_show_works' );
