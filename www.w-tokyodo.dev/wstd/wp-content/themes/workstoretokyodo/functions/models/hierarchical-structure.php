<?php
/**
 * ページなど、階層構造を解読するクラス
 */
class hierarchical_structure {

	static function ancestors() {
		if ( is_page() ) {
			global $post;
			$anc = get_ancestors( $post->ID, 'page' );
		}
		if ( !empty( $anc ) )
			return $anc;
		return null;
	}

	static function is_page_top() {
		global $post;
		if ( is_page() && 0 === $post->post_parent )
			return true;
		return false;
	}

}

/**
 * 階層構造の中でも、特にトウキョウドゥの事業部に関するクラス
 */
class wstd_division {

	static $divisions = array( 'direct', 'neoponte', 'sharyobu' );

	static function get_division( $id = 0, $return = '' ) {
		if ( $anc = hierarchical_structure::ancestors() )
			$obj = get_post( $anc[count($anc) - 1] );
		else
			$obj = get_post( $id );
		if ( !in_array( $obj->post_name, self::$divisions ) )
			return null;
		if ( '' === $return )
			return $obj;
		else
			return $obj->$return;
	}

	static function is_division( $division = '' ) {
		if ( '' === $division )
			return self::get_division() ? true : false;
		elseif ( in_array( $division, self::$divisions ) )
			return ( $division === self::get_division( 0, 'post_name' ) );
		return false;
	}

}

/**
 * 固定ページの最上位であるかを調べるラッパー関数
 *
 * @uses class hierarchical_structure
 *
 * @return bool
 */
if ( !function_exists( 'is_page_top' ) ) {
	function is_page_top() {
		return hierarchical_structure::is_page_top();
	}
}

/**
 * 事業部情報を取得するラッパー関数
 *
 * @uses class wstd_division
 *
 * @param int $id post ID
 * @param string $return 'post_title', 'post_name', Default: '' ... return Object
 *
 * @return mixed Default: Object
 */
if ( !function_exists( 'get_division' ) ) {
	function get_division( $id = 0, $return = '' ) {
		return wstd_division::get_division( $id, $return );
	}
}

/**
 * 事業部ページか否かを調べるラッパー関数
 *
 * @uses class wstd_division
 *
 * @return bool
 */
if ( !function_exists( 'is_division' ) ) {
	function is_division( $division = '' ) {
		return wstd_division::is_division( $division );
	}
}

/**
 * 事業部ページである場合は、bodyにクラスを追加する
 */
function add_wstd_division_body_class( $classes ) {
	if ( $slug = wstd_division::get_division( 0, 'post_name' ) )
		$classes[] = 'division-' . $slug;
	if ( wstd_division::is_division() && hierarchical_structure::is_page_top() )
		$classes[] = 'division-top';
	return $classes;
}
add_filter( 'body_class', 'add_wstd_division_body_class' );