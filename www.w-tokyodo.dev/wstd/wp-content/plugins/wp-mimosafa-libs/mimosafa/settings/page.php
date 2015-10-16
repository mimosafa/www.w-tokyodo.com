<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Settings API interface class
 *
 * @access public
 * @uses mimosafa\WP\Settings\Controller
 *
 * @package WordPress
 * @subpackage WordPress Libraries by mimosafa
 *
 * @license GPLv2
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class Page {

	/**
	 * Object hash
	 *
	 * @var string
	 */
	private $hash;

	/**
	 * Argument caches
	 *
	 * @var array
	 */
	private $cache = [
		'page'    => [],
		'section' => [],
		'field'   => []
	];

	/**
	 * @var mimosafa\WP\Settings\Controller
	 */
	private $controller;

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @param  string $page       Optional
	 * @param  string $page_title Optional
	 * @param  string $menu_title Optional
	 */
	public function __construct() {
		$this->hash = spl_object_hash( $this );
		$this->controller = new Controller( $this->hash );
		if ( $args = func_get_args() ) {
			//
		}
	}

	private function init_array( Array $args ) {
		static $hierarchy = 0;
		//
	}

	/**
	 * Initialize class properties & Set page
	 *
	 * @access public
	 *
	 * @uses   mimosafa\WP\Settings\Controller::defineToplevelPage()
	 *
	 * @param  string $page
	 * @param  string $page_title Optional
	 * @param  string $menu_title Optional
	 * @return mimosafa\WP\Settings\Page
	 */
	public function page( $page, $page_title = null, $menu_title = null ) {
		if ( $page = filter_var( $page ) ) {
			$this->_init_page();
			$cache =& $this->getCache( 'page' );
			$cache = [ 'page' => $page ];
			$this->controller->defineToplevelPage( $page, $this->hash );
			if ( $page_title ) {
				$this->title( $page_title );
			}
			if ( $menu_title ) {
				$this->menu_title( $menu_title );
			}
		}
		return $this;
	}

	/**
	 * Add Post Type List Page as Subpage
	 *
	 * @todo
	 *
	 * @access public
	 *
	 * @param  string $post_type
	 * @param  string $menu_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function post_type( $post_type, $menu_title = null ) {
		if ( ! $this->toplevel || $post_type = filter_var( $post_type ) ) {
			return;
		}
		$this->_init_page();
		//
		return $this;
	}

	/**
	 * Add Taxonomy List Page as Subpage
	 *
	 * @todo
	 *
	 * @access public
	 *
	 * @param  string $taxonomy
	 * @param  string $menu_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function taxonomy( $taxonomy, $menu_title = null ) {
		if ( ! $this->toplevel || $taxonomy = filter_var( $taxonomy ) ) {
			return;
		}
		$this->_init_page();
		//
		return $this;
	}

	/**
	 * Set section
	 *
	 * @access public
	 *
	 * @param  string $section
	 * @param  string $title Optional. if blank, string made from section_id. if want to hide set empty string ''.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function section( $section, $title = null ) {
		if ( $section = filter_var( $section ) ) {
			$this->_init_section();
			$cache =& $this->getCache( 'section' );
			$cache = [ 'id' => $section ];
			if ( is_string( $title ) ) {
				$cache['title'] = $title;
			}
		}
		return $this;
	}

	/**
	 * Set field
	 *
	 * @access public
	 *
	 * @param  string $field
	 * @param  string $title Optional.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function field( $field, $title = null ) {
		if ( $field = filter_var( $field ) ) {
			$this->_init_field();
			$cache =& $this->getCache( 'field' );
			$cache = [ 'id' => $field, 'label_for' => $field ];
			if ( $title = filter_var( $title ) ) {
				$cache['title'] = $title;
			}
		}
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
	 * @return mimosafa\WP\Settings\Page
	 */
	public function option( $option, $callback = null, $sanitize = null, $arguments = [] ) {
		if ( ! $option = filter_var( $option ) ) {
			return;
		}
		$cache =& $this->getCache();
		if ( ! $cache['field'] ) {
			return;
		}
		$cache['field']['option'] = $option;
		if ( $callback ) {
			$this->callback( $callback, $sanitize, $arguments );
		} else if ( $sanitize ) {
			$this->sanitize( $sanitize, $arguments );
		}
		if ( ! isset( $cache['page']['has_option_field'] ) ) {
			$cache['page']['has_option_fields'] = true;
		}
		return $this;
	}

	/**
	 * Set callback for option form
	 *
	 * @access public
	 *
	 * @param  string|callable $callback
	 * @param  array           $args     Optional.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function callback( $callback, $sanitize = null, $args = [] ) {
		if ( is_string( $callback ) && method_exists( $this->controller, 'callback_' . $callback ) ) {
			$callable = [ $this->controller, 'callback_' . $callback ];
			$option_required = true;
		} else if ( is_callable( $callback ) ) {
			$callable = $callback;
			$option_required = false;
		}
		if ( ! isset( $callable ) || ! $cache =& $this->getCache( 'field' ) ) {
			return;
		}
		if ( $option_required && ! isset( $cache['option'] ) ) {
			return;
		}
		$cache['callback'] = $callable;
		if ( $sanitize ) {
			$this->sanitize( $sanitize, $args );
		} else if ( $args && is_array( $args ) ) {
			$this->misc( $args, 'field' );
		}
		return $this;
	}

	/**
	 * Set sanitize callback for option
	 *
	 * @access public
	 *
	 * @param  callable $sanitize
	 * @param  array    $args     Optional.
	 * @return mimosafa\WP\Settings\Page
	 */
	public function sanitize( callable $sanitize, $args = [] ) {
		if ( ! $cache =& $this->getCache( 'field' ) ) {
			return;
		}
		if ( ! isset( $cache['option'] ) ) {
			return;
		}
		$cache['sanitize'] = $sanitize;
		if ( $args && is_array( $args ) ) {
			$this->misc( $args, 'field' );
		}
		return $this;
	}

	/**
	 * @access public
	 */
	public function __call( $name, $params ) {
		if ( substr( $name, 0, 5 ) === 'attr_' ) {
			return $this->misc( [ $name => $params[0] ] );
		}
	}

	/**
	 * Set other argument
	 *
	 * @access public
	 *
	 * @param  array $args
	 * @return mimosafa\WP\Settings\Page
	 */
	public function misc( Array $args, $key = null ) {
		if ( ! $key || ! in_array( $key, [ 'page', 'section', 'field' ] ) ) {
			$cache =& $this->getCurrentCache();
		} else {
			$cache =& $this->getCache( $key );
		}
		if ( ! $cache ) {
			return;
		}
		foreach ( $args as $key => $val ) {
			if ( ! array_key_exists( $key, $cache ) ) {
				$cache[$key] = $val;
			}
		}
		return $this;
	}

	/**
	 * Set Page Title
	 *
	 * @access public
	 *
	 * @param  string $page_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function title( $title ) {
		if ( ! $title = filter_var( $title ) ) {
			return;
		}
		if ( ! $cache =& $this->getCurrentCache() ) {
			return;
		}
		$cache['title'] = $title;
		return $this;
	}

	/**
	 * Set Menu Title
	 *
	 * @access public
	 *
	 * @param  string $menu_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function menu_title( $menu_title ) {
		if ( ! $menu_title = filter_var( $menu_title ) ) {
			return;
		}
		if ( ! $cache =& $this->getCache( 'page' ) ) {
			return;
		}
		$cache['menu_title'] = $menu_title;
		return $this;
	}

	/**
	 * Set capability
	 *
	 * @access public
	 *
	 * @param  string $capability
	 * @return mimosafa\WP\Settings\Page
	 */
	public function capability( $capability ) {
		if ( ! $capability = filter_var( $capability ) ) {
			return;
		}
		if ( ! $cache =& $this->getCache( 'page' ) ) {
			return;
		}
		$cache['capability'] = $capability;
		return $this;
	}

	/**
	 * Set icon url
	 *
	 * @param  string $icon_url
	 * @return mimosafa\WP\Settings\Page
	 */
	public function icon_url( $icon_url ) {
		if ( ! $icon_url = filter_var( $icon_url ) ) {
			return;
		}
		if ( ! $cache =& $this->getCache( 'page' ) ) {
			return;
		}
		$cache['icon_url'] = $icon_url;
		return $this;
	}

	/**
	 * Set position in admin menu
	 *
	 * @param  integer $position
	 * @return mimosafa\WP\Settings\Page
	 */
	public function position( $position ) {
		if ( ! $position = filter_var( $position, \FILTER_VALIDATE_INT, [ 'options' => [ 'min_range' => 1 ] ] ) ) {
			return;
		}
		if ( ! $cache =& $this->getCache( 'page' ) ) {
			return;
		}
		$cache['position'] = $position;
		return $this;
	}

	public function provision( callable $func ) {
		if ( ! $cache =& $this->getCache( 'page' ) ) {
			return;
		}
		$cache['provision'] = $func;
		return $this;
	}

	/**
	 * Set description text
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @return mimosafa\WP\Settings\Page
	 */
	public function description( $text ) {
		if ( ! $text = filter_var( $text ) ) {
			return;
		}
		if ( ! $cache =& $this->getCurrentCache() ) {
			return;
		}
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
	 * @return mimosafa\WP\Settings\Page
	 */
	public function html( $html, $wrap_div = false ) {
		if ( ! $html = filter_var( $html ) ) {
			return;
		}
		if ( ! $cache =& $this->getCurrentCache() ) {
			return;
		}
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
	 * @param  string  $path
	 * @param  array   $args
	 * @param  boolean $wrap_div Optional. if true wrap $html by 'div'
	 * @return mimosafa\WP\Settings\Page
	 */
	public function file( $path, $args = [], $wrap = false ) {
		if ( ( ! $realPath = realpath( $path ) ) || $realPath != $path ) {
			return;
		}
		if ( ! $cache =& $this->getCache( 'page' ) ) {
			return;
		}
		$cache['callback'] = [ &$this, 'include_file' ];
		$cache['file_path'] = $realPath;
		$cache['include_file_args'] = $args;
		$cache['wrap_included_file'] = filter_var( $wrap, \FILTER_VALIDATE_BOOLEAN );
		return $this;
	}

	/**
	 * Set submit button
	 *
	 * @todo
	 *
	 * @access public
	 *
	 * @param  string $text
	 * @return mimosafa\WP\Settings\Page
	 */
	public function submit_button( $text ) {
		//
		return $this;
	}

	/**
	 * @access public
	 *
	 * @return (void)
	 */
	public function init() {
		$this->_init_page();
		$this->controller->init();
	}

	/**
	 * Initialize static cache $page
	 *
	 * @access private
	 * @uses   mimosafa\WP\Settings\Controller::addPageArgs()
	 */
	private function _init_page() {
		$this->_init_section();
		$cache =& $this->getCache( 'page' );
		if ( ! empty( $cache ) ) {
			$this->controller->addPageArgs( $cache, $this->hash );
			#$this->pages[] = $cache;
		}
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
				if ( ! array_key_exists( 'sections', $cache['page'] ) ) {
					$cache['page']['sections'] = [];
				}
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
				if ( ! array_key_exists( 'fields', $cache['section'] ) ) {
					$cache['section']['fields'] = [];
				}
				$cache['section']['fields'][] = $cache['field'];
			}
		}
		$cache['field'] = [];
	}

	/**
	 * Get caches of $this
	 *
	 * @access private
	 *
	 * @return &array
	 */
	private function &getCache( $key = null ) {
		if ( ! $key || ! in_array( $key, [ 'page', 'section', 'field' ] ) ) {
			return $this->cache;
		}
		return $this->cache[$key];
	}

	/**
	 * Get end of caches
	 *
	 * @access private
	 *
	 * @return &array|&boolean
	 */
	private function &getCurrentCache() {
		static $falseVal = false;
		$cache =& $this->getCache();
		if ( $cache['field'] ) {
			return $cache['field'];
		} else if ( $cache['section'] ) {
			return $cache['section'];
		} else if ( $cache['page'] ) {
			return $cache['page'];
		}
		return $falseVal;
	}

}
