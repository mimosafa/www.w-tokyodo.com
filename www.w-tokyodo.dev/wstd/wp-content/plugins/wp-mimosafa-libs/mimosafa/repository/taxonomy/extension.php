<?php
namespace mimosafa\WP\Repository\Taxonomy;

/**
 * WordPress Taxonomy Extensional Class
 *
 * @package WordPress
 * @subpackage WordPress Libraries by mimosafa
 *
 * @license GPLv2
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Extension {

	/**
	 * @var array
	 */
	private $submenu_pages = [];

	/**
	 * Initializer (Singleton)
	 *
	 * @access public
	 */
	public static function init() {
		static $instance;
		if ( ! $instance )
			$instance = new self();
	}

	/**
	 * Constructor
	 *
	 * @access private
	 */
	private function __construct() {
		add_action( 'registered_taxonomy', [ $this, 'ext_show_in_menu' ], 10, 3 );
	}

	public function ext_show_in_menu( $taxonomy, $object_type, $args ) {
		if ( ! $args['show_ui'] )
			return;
		if ( ! is_string( $args['show_in_menu'] ) || ! strpos( $args['show_in_menu'], '.php' ) )
			return;
		global $wp_taxonomies;
		$wp_taxonomies[$taxonomy]->show_in_menu = apply_filters( $taxonomy . '_show_in_menu', false, $args['show_in_menu'] );
		$this->submenu_pages[$taxonomy] = $args['show_in_menu'];
		static $action_added = false;
		if ( ! $action_added ) {
			add_action( 'admin_menu', [ $this, 'add_submenu_pages' ] );
			add_filter( 'parent_file', [ $this, 'parent_file' ] );
			$action_added = true;
		}
	}

	public function add_submenu_pages() {
		if ( ! $this->submenu_pages )
			return;
		foreach ( $this->submenu_pages as $taxonomy => $page ) {
			$tax = get_taxonomy( $taxonomy );
			add_submenu_page(
				$page,
				apply_filters( $taxonomy . '_submenu_page_title', $tax->labels->name, $tax, $page ),
				apply_filters( $taxonomy . '_submenu_menu_title', $tax->labels->name, $tax, $page ),
				apply_filters( $taxonomy . '_submenu_capability', $tax->cap->assign_terms, $tax, $page ),
				'edit-tags.php?taxonomy=' . $taxonomy
			);
		}
	}

	public function parent_file( $parent_file ) {
		global $taxnow;
		if ( isset( $this->submenu_pages[$taxnow] ) ) {
			if ( ! filter_input( \INPUT_GET, 'post_type' ) )
				$parent_file = $this->submenu_pages[$taxnow];
		}
		return $parent_file;
	}

}
