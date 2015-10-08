<?php
namespace mimosafa\WP\Repository;

abstract class Regulation {

	protected static $reserved;

	/**
	 * @access protected
	 *
	 * @param  mixed   $var
	 * @param  boolean $default
	 * @return boolean
	 */
	protected static function bool_value( $var, $default = false ) {
		$bool = filter_var( $var, \FILTER_VALIDATE_INT, \FILTER_NULL_ON_FAILURE );
		return isset( $bool ) ? $bool : (bool) $default;
	}

	/**
	 * @access public
	 *
	 * @param  mixed $var
	 * @return string|null
	 */
	public static function sanitized_key( $var ) {
		if ( ! $var || is_array( $var ) || is_object( $var ) )
			return null;
		return $var && $var === sanitize_key( $var ) ? $var : null;
	}

}
