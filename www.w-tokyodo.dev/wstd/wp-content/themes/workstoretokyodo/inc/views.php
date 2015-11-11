<?php
/**
 * Workstore Tokyo Do Theme Views
 *
 * @since 0.1
 */

/**
 * Header Area @header.php
 *
 * @since 0.0
 */

/**
 * Workstore Tokyo Do Logo's View
 *
 * @since 0.0
 */
function wstd_header_logo() {
	if ( is_division() ) {
		echo '<a href="' . home_url() . '" class="packOff">Workstore Tokyo Do</a>';
	} else {
		echo '<span>食を通じて「賑わい」と「笑顔」と「思い出」を作るプロフェッショナル!!</span>';
	}
}

/**
 * Manage Link URL for Contact Page
 *
 * @since 0.0
 */
function wstd_header_contact_uri() {
	$page = get_page_by_path( 'contact' );
	$uri  = get_permalink( $page );
	if ( is_division( 'direct' ) ) {
		$uri .= '#event';
	}
	else if ( is_division( 'sharyobu' ) ) {
		$uri .= '#car';
	}
	echo $uri;
}

/**
 * View of Site ID
 *
 * @since 0.0
 */
function wstd_header_site_id() {
	$string = '株式会社ワークストア・トウキョウドゥ';
	$href   = home_url();
	if ( $division = get_division() ) {
		$string = esc_html( $division->post_title );
		$href = get_permalink( $division );
	}
	if ( is_home() || ( is_division() && is_page_top() ) )	{
		echo '<h1 class="packOff">' . $string . '</h1>';
	} else {
		echo '<a href="' . $href . '" class="packOff">' . $string . '</a>';
	}
}

/**
 * Divisions Navigation Links
 *
 * @since 0.0
 */
function wstd_header_division_nav_lists() {
	$array = [
		'direct'   => '<li id="btn-tokyodo"%s>%sTokyo Do%s</li>',
		'neostall' => '<li id="btn-neostall"%s>%sネオ屋台村%s</li>',
		'neoponte' => '<li id="btn-neoponte"%s>%sネオポンテ%s</li>',
		'sharyobu' => '<li id="btn-sharyobu"%s>%s車両部%s</li>'
	];
	foreach ( $array as $key => $format ) {
		if ( !is_division( $key ) )
			printf( $format, '', '<a href="/' . $key . '">', '</a>' );
		else
			printf( $format, ' class="now-on-display"', '', '' );
		echo "\n";
	}
}
