<?php
/**
 * Workstore Tokyo Do Codes
 *
 * @since 0.1
 */

/**
 * Body Class
 *
 * @since 0.0
 */
add_filter( 'body_class', 'wstd_body_class' );
function wstd_body_class( $classes ) {
	if ( is_division() ) {
		$classes[] = 'division-' . get_division( null, 'post_name' );
		if ( is_page_top() ) {
			$classes[] = 'division-top';
		}
	}
	return $classes;
}

/**
 * Division Template
 *
 * @since 0.1
 */
add_filter( 'template_include', 'wstd_get_division_template' );
function wstd_get_division_template( $template ) {
	if ( is_division() ) {
		$templates = [];
		if ( is_page_top() ) {
			$templates[] = 'division-top.php';
		}
		$templates[] = 'division.php';
		if ( $located = locate_template( $templates ) ) {
			return $located;
		}
	}
	return $template;
}

/**
 * Functions
 *
 */

if ( ! function_exists( 'is_page_top' ) ) {
	/**
	 * Current Queried is Page & Top Page, OR Not
	 *
	 * @access public
	 * @since 0.0
	 * @return boolean
	 */
	function is_page_top() {
		if ( is_page() ) {
			$page = get_queried_object();
			return $page->post_parent === 0;
		}
		return false;
	}
}

/**
 * Workstore Tokyo Do Divisions Function
 *
 * << Divisions >>
 * - Direct
 * - Neoponte
 * - Sharyobu
 *
 * @since 0.0
 */

if ( ! function_exists( 'divisions' ) ) {
	/**
	 * Return Defined Divisions
	 *
	 * @access public
	 * @since 0.1
	 *
	 * @return array
	 */
	function divisions() {
		/**
		 * Defined Divisions
		 *
		 * @var array
		 */
		static $divisions = [
			'direct', 'neostall', 'neoponte', 'sharyobu'
		];
		return $divisions;
	}
}

if ( ! function_exists( 'get_division' ) ) {
	/**
	 * Get Current Queried Division
	 *
	 * @access public
	 * @since 0.0
	 *
	 * @uses divisions()
	 *
	 * @param  int|WP_Post $post   Optional
	 * @param  string      $return Optional
	 * @return WP_Post|mixed|null
	 */
	function get_division( $post = null, $return = null ) {
		if ( $post = get_post( $post ) ) {
			if ( $post->post_type === 'page' ) {
				/**
				 * Page
				 */
				if ( $post->post_parent > 0 ) {
					$anc = get_ancestors( $post->ID, 'page' );
					$post = get_post( $anc[count( $anc ) - 1] );
				}
				if ( ! in_array( $post->post_name, divisions(), true ) ) {
					return null;
				}
				if ( $return && $post->$return ) {
					return $post->$return;
				}
				return $post;
			}
		}
		return null;
	}
}

if ( ! function_exists( 'is_division' ) ) {
	/**
	 * @access public
	 * @since 0.0
	 *
	 * @uses divisions()
	 * @uses get_division()
	 */
	function is_division( $division = null ) {
		$division = filter_var( $division );
		if ( ! $division || ! in_array( $division, divisions(), true ) ) {
			return get_division() ? true : false;
		}
		return get_division( null, 'post_name' ) === $division;
	}
}
