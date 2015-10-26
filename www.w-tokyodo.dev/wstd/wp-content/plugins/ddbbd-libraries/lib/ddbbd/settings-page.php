<?php
namespace DDBBD;

/**
 * WordPress Settings API interface class
 *
 * @package WordPress
 * @subpackage Dana Don-Boom-Boom-Doo
 *
 * @license GPLv2
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Settings_Page {

	/**
	 * Object hash
	 *
	 * @var string
	 */
	private $hash;

	/**
	 * Top level page
	 *
	 * @var string
	 */
	private $toplevel;

	/**
	 * Structure of settings page
	 *
	 * @var array
	 */
	private $pages = [];

	/**
	 * Argument caches
	 *
	 * @var array
	 */
	private static $cache = [];

	/**
	 * Arguments of '_add_settings' method
	 * 
	 * @var array
	 */
	private $sections = [];
	private $fields   = [];
	private $settings = [];

	/**
	 * Arguments of callback functions
	 *
	 * @var array
	 */
	private static $arguments = [];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  string $page       Optional
	 * @param  string $page_title Optional
	 * @param  string $menu_title Optional
	 */
	public function __construct( $page = null, $page_title = null, $menu_title = null ) {
		$this->hash = spl_object_hash( $this );
		self::$cache[$this->hash] = [
			'page'    => [],
			'section' => [],
			'field'   => []
		];
		$this->init( $page, $page_title, $menu_title );
	}

	/**
	 * Initialize class properties & Set page
	 *
	 * @access public
	 *
	 * @param  string $page       Optional
	 * @param  string $page_title Optional
	 * @param  string $menu_title Optional
	 * @return DDBBD\Settings_Page
	 */
	public function init( $page = null, $page_title = null, $menu_title = null ) {
		$this->_init_page();
		$cache =& $this->getCache( 'page' );
		if ( ! $page = filter_var( $page ) ) {
			if ( ! $this->toplevel )
				$page = 'options-general.php';
		}
		if ( $page ) {
			$cache = [ 'page' => $page ];
			if ( ! $this->toplevel )
				$this->toplevel = $page;
			if ( $page_title )
				$this->title( $page_title );
			if ( $menu_title )
				$this->menu_title( $menu_title );
		}
		return $this;
	}

	/**
	 * Set section
	 *
	 * @access public
	 *
	 * @param  string $section_id
	 * @param  string $section_title Optional. if blank, string made from section_id. if want to hide set empty string ''.
	 * @return DDBBD\Settings_Page
	 */
	public function section( $section_id, $section_title = null ) {
		$this->_init_section();
		if ( ! $section_id = filter_var( $section_id ) )
			return;
		$cache =& $this->getCache( 'section' );
		$cache = [ 'id' => $section_id ];
		if ( is_string( $section_title ) )
			$cache['title'] = $section_title;
		return $this;
	}

	/**
	 * Set field
	 *
	 * @access public
	 *
	 * @param  string $field_id
	 * @param  string $field_title Optional.
	 * @return DDBBD\Settings_Page
	 */
	public function field( $field_id, $field_title = null ) {
		$this->_init_field();
		if ( ! $field_id = filter_var( $field_id ) )
			return;
		$cache =& $this->getCache( 'field' );
		$cache = [ 'id' => $field_id ];
		if ( $field_title = filter_var( $field_title ) )
			$cache['title'] = $field_title;
		return $this;
	}

	/**
	 * Set option
	 *
	 * @access public
	 *
	 * @param  string   $option
	 * @param  callable $callback Optional.
	 * @param  array    $arguments Optional.
	 * @return DDBBD\Settings_Page
	 */
	public function option( $option, $callback = null, $sanitize = null, $arguments = [] ) {
		if ( ! $option = filter_var( $option ) )
			return;
		$cache =& $this->getCache();
		if ( ! $cache['field'] )
			return;
		$cache['field']['option'] = $option;
		if ( $callback )
			$this->callback( $callback, $sanitize, $arguments );
		else if ( $sanitize )
			$this->sanitize( $sanitize, $arguments );
		if ( ! isset( $cache['page']['has_option_field'] ) )
			$cache['page']['has_option_fields'] = true;
		return $this;
	}

	/**
	 * Set callback for option form
	 *
	 * @access public
	 *
	 * @param  string|callable $callback
	 * @param  array           $arguments Optional.
	 * @return DDBBD\Settings_Page
	 */
	public function callback( $callback, $sanitize = null, $arguments = [] ) {
		if ( is_string( $callback ) && method_exists( __CLASS__, $callback ) )
			$callable = [ &$this, $callback ];
		else if ( is_callable( $callback ) )
			$callable = $callback;
		if ( ! isset( $callable ) )
			return;
		if ( ! $cache =& $this->getCache( 'field' ) )
			return;
		if ( ! isset( $cache['option'] ) )
			return;
		$cache['callback'] = $callable;
		if ( $sanitize )
			$this->sanitize( $sanitize, $arguments );
		else
			$this->misc( $arguments, 'field' );
		return $this;
	}

	/**
	 * Set sanitize callback for option
	 *
	 * @access public
	 *
	 * @param  callable $sanitize
	 * @param  array    $arguments Optional.
	 * @return DDBBD\Settings_Page
	 */
	public function sanitize( callable $sanitize, $arguments = [] ) {
		if ( ! $cache =& $this->getCache( 'field' ) )
			return;
		if ( ! isset( $cache['option'] ) )
			return;
		$cache['sanitize'] = $sanitize;
		if ( $arguments )
			$this->misc( $arguments, 'field' );
		return $this;
	}

	/**
	 * Set other argument
	 *
	 * @access public
	 *
	 * @param  array $args
	 * @return DDBBD\Settings_Page
	 */
	public function misc( Array $args, $key = null ) {
		if ( ! $key || ! in_array( $key, [ 'page', 'section', 'field' ] ) )
			$cache =& $this->getCurrentCache();
		else
			$cache =& $this->getCache( $key );
		if ( ! $cache )
			return;
		foreach ( (array) $args as $key => $val ) {
			if ( ! array_key_exists( $key, $cache ) )
				$cache[$key] = $val;
		}
		return $this;
	}

	/**
	 * Set title
	 *
	 * @access public
	 *
	 * @param  string $title
	 * @return DDBBD\Settings_Page
	 */
	public function title( $title ) {
		if ( ! $title = filter_var( $title ) )
			return;
		if ( ! $cache =& $this->getCurrentCache() )
			return;
		$cache['title'] = $title;
		return $this;
	}

	/**
	 * Set menu title
	 *
	 * @access public
	 *
	 * @param  string $menu_title
	 * @return DDBBD\Settings_Page
	 */
	public function menu_title( $menu_title ) {
		if ( ! $menu_title = filter_var( $menu_title ) )
			return;
		if ( ! $cache =& $this->getCache( 'page' ) )
			return;
		$cache['menu_title'] = $menu_title;
		return $this;
	}

	/**
	 * Set capability
	 *
	 * @access public
	 *
	 * @param  string $capability
	 * @return DDBBD\Settings_Page
	 */
	public function capability( $capability ) {
		if ( ! $capability = filter_var( $capability ) )
			return;
		if ( ! $cache =& $this->getCache( 'page' ) )
			return;
		$cache['capability'] = $capability;
		return $this;
	}

	/**
	 * Set icon url
	 *
	 * @param  string $icon_url
	 * @return DDBBD\Settings_Page
	 */
	public function icon_url( $icon_url ) {
		if ( ! $icon_url = filter_var( $icon_url ) )
			return;
		if ( ! $cache =& $this->getCache( 'page' ) )
			return;
		$cache['icon_url'] = $icon_url;
		return $this;
	}
	/**
	 * Set position in admin menu
	 *
	 * @param  integer $position
	 * @return DDBBD\Settings_Page
	 */
	public function position( $position ) {
		if ( ! $position = filter_var( $position, \FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] ) )
			return;
		if ( ! $cache =& $this->getCache( 'page' ) )
			return;
		$cache['position'] = $position;
		return $this;
	}
	/**
	 * Set description text
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @return DDBBD\Settings_Page
	 */
	public function description( $text ) {
		if ( ! $text = filter_var( $text ) )
			return;
		if ( ! $cache =& $this->getCurrentCache() )
			return;
		$format = '<p class="description">%s</p>';
		if ( ! array_key_exists( 'description', $cache ) ) {
			$cache['description'] = sprintf( $format, $text );
		} else {
			$format = "\n{$format}";
			$cache['description'] .= sprintf( $format, $text );
		}
		return $this;
	}
	/**
	 * Set html contents
	 *
	 * @access public
	 *
	 * @param  string $html
	 * @param  boolean $wrap_div Optional. if true wrap $html by 'div'
	 * @return DDBBD\Settings_Page
	 */
	public function html( $html, $wrap_div = false ) {
		if ( ! $html = filter_var( $html ) )
			return;
		if ( ! $cache =& $this->getCurrentCache() )
			return;
		$format = $wrap_div ? '<div>%s</div>' : '%s';
		if ( ! array_key_exists( 'html', $cache ) ) {
			$cache['html'] = sprintf( $format, $html );
		} else {
			$format = "\n{$format}";
			$cache['html'] .= sprintf( $format, $html );
		}
		return $this;
	}

	/**
	 * @access public
	 *
	 * @return (void)
	 */
	public function done() {
		$this->init();
		if ( $this->pages ) {
			add_action( 'admin_menu', [ &$this, '_add_pages' ] );
			add_action( 'admin_init', [ &$this, '_add_settings' ] );
		}
	}

	/**
	 * Initialize static cache $page
	 *
	 * @access private
	 */
	private function _init_page() {
		$this->_init_section();
		$cache =& $this->getCache( 'page' );
		if ( ! empty( $cache ) )
			$this->pages[] = $cache;
		$cache = [];
	}

	/**
	 * Initialize static cache $section
	 *
	 * @access private
	 */
	private function _init_section() {
		$this->_init_field();
		$cache =& $this->getCache();
		if ( ! empty( $cache['section'] ) ) {
			if ( $cache['page'] ) {
				if ( ! array_key_exists( 'sections', $cache['page'] ) )
					$cache['page']['sections'] = [];
				$cache['page']['sections'][] = $cache['section'];
			}
		}
		$cache['section'] = [];
	}

	/**
	 * Initialize static cache $field
	 *
	 * @access private
	 */
	private function _init_field() {
		$cache =& $this->getCache();
		if ( ! empty( $cache['field'] ) ) {
			if ( $cache['section'] ) {
				if ( ! array_key_exists( 'fields', $cache['section'] ) )
					$cache['section']['fields'] = [];
				$cache['section']['fields'][] = $cache['field'];
			} else if ( $cache['page'] ) {
				if ( ! array_key_exists( 'fields', $cache['page'] ) )
					$cache['page']['fields'] = [];
				$cache['page']['fields'][] = $cache['field'];
			}
		}
		$cache['field'] = [];
	}

	/**
	 * Add pages
	 *
	 * @access private
	 */
	public function _add_pages() {
		if ( ! doing_action( 'admin_menu' ) || ! $this->pages )
			return;
		foreach ( $this->pages as $page ) {
			$this->_add_page( $page );
		}
		do_action( __NAMESPACE__ . '_added_pages' );
	}

	/**
	 * Add page
	 *
	 * @access private
	 *
	 * @global array $admin_page_hook
	 *
	 * @param  array $page_arg
	 * @return void
	 */
	private function _add_page( Array $page_arg ) {
		//
	}

	/**
	 * Setting sections & fields method
	 *
	 * @access private
	 */
	public function _add_settings() {
		//
	}

	/**
	 * Get caches of $this
	 *
	 * @access private
	 *
	 * @return array &$return
	 */
	private function &getCache( $key = null ) {
		if ( ! $key || ! in_array( $key, [ 'page', 'section', 'field' ] ) )
			return self::$cache[$this->hash];
		return self::$cache[$this->hash][$key];
	}

	/**
	 * Get end of caches
	 *
	 * @access private
	 *
	 * @return array|false &$return
	 */
	private function &getCurrentCache() {
		static $falseVal = false;
		$cache =& $this->getCache();
		if ( $cache['field'] )
			return $cache['field'];
		else if ( $cache['section'] )
			return $cache['section'];
		else if ( $cache['page'] )
			return $cache['page'];
		return $falseVal;
	}

}
