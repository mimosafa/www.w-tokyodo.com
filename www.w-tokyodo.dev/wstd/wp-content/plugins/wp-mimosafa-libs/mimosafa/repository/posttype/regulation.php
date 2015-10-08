<?php
namespace mimosafa\WP\Repository\PostType;

use \mimosafa\WP\Misc as WP;
use \mimosafa\WP\Repository as Repo;

class Regulation extends Repo\Regulation {

	public static $post_arguments = [
		//
	];

	public static function arguments_walker( &$arg, $key ) {
		if ( method_exists( __CLASS__, ( $method = $key . '_filter' ) ) )
			$arg = self::$method( $arg );
		else
			$arg = null;
	}

	public static function validate_name( $var ) {
		if ( ! self::sanitized_key( $var ) )
			return false;
		return true;
	}

	//

	public static function label_filter( $var ) {
		return filter_var( $var, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	}
	public static function labels_filter( $var ) {
		//
	}
	public static function description_filter( $var ) {
		return filter_var( $var, \FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	}
	public static function public_filter( $var ) {
		return self::bool_value( $var );
	}
	public static function exclude_from_search_filter( $var ) {
		return self::bool_value( $var, true );
	}
	public static function publicly_queryable_filter( $var ) {
		return $this->public_filter( $var );
	}
	public static function show_ui_filter( $var ) {
		return $this->public_filter( $var );
	}
	public static function show_in_nav_menus_filter( $var ) {
		return $this->public_filter( $var );
	}
	public static function show_in_menu_filter( $var ) {
		$regexp = [ 'regexp' => '/\A[a-z\-]+\.php\?[a-z_\-]+=[a-z_\-]+\z|\A[a-z\-]+\.php\z/' ];
		if ( $menu = filter_var( $var, \FILTER_VALIDATE_REGEXP, [ 'options' => $regexp ] ) )
			return $menu;
		else
			return $this->show_ui_filter( $var );
	}
	public static function show_in_admin_bar_filter( $var ) {
		return $this->show_ui_filter( $var );
	}
	public static function menu_position_filter( $var ) {
		return filter_var( $var, \FILTER_VALIDATE_INT, [ 'options' => [ 'default' => 25 ] ] );
	}
	public static function menu_icon_filter( $var ) {
		if ( $icon = WP\Dashicons::exists( $var ) )
			return $icon;
		if ( $icon_url = filter_var( $var, \FILTER_VALIDATE_URL ) ) {
			return $icon_url;
		}
		return WP\Dashicons::default_icon( 'post_type' );
	}
	public static function capability_type_filter( $var ) {
		if ( $cap = filter_var( $var, \FILTER_CALLBACK, [ 'options' => __CLASS__ . '::sanitized_key' ] ) )
			return $cap;
		if ( is_array( $var )
			&& count( $var ) === 2
			&& $var === array_values( $var )
			&& $var[0] !== $var[1]
			&& self::sanitized_key( $var[0] )
			&& self::sanitized_key( $var[1] )
		)
			return $var;
		return 'post';
	}
	public static function capabilities_filter( $var ) {
		//
	}
	public static function map_meta_cap_filter( $var ) {
		//
	}
	public static function hierarchical_filter( $var ) {
		return self::bool_value( $var );
	}
	public static function supports_filter( $var ) {
		//
	}
	public static function register_meta_box_cb_filter( $var ) {
		//
	}
	public static function taxonomies_filter( $var ) {
		//
	}
	public static function has_archive_filter( $var ) {
		//
	}
	public static function rewrite_filter( $var ) {
		//
	}
	public static function query_var_filter( $var ) {
		//
	}
	public static function can_export_filter( $var ) {
		return self::bool_value( $var, true );
	}

}
