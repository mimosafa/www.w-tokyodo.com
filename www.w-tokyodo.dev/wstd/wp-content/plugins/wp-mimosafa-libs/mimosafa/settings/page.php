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
	 * @var array
	 */
	private $sections = [];
	private $fields   = [];
	private $settings = [];

	private static $view;

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
		# $this->hash = spl_object_hash( $this );
		self::$view = self::$view ?: View::instance();
		add_action( 'admin_menu', [ $this, '_init' ] );
	}

	/**
	 * Initialize class properties & Set page
	 *
	 * @access public
	 *
	 * @uses   mimosafa\WP\Settings\Controller::defineToplevelPage()
	 *
	 * @param  string $page
	 * @param  string $title      Optional
	 * @param  string $menu_title Optional
	 * @return mimosafa\WP\Settings\Page
	 */
	public function page( $page, $title = null, $menu_title = null ) {
		if ( $page = filter_var( $page ) ) {
			$this->_init_page();
			$cache =& $this->getCache( 'page' );
			$cache = [ 'page' => $page ];
			if ( ! isset( $this->toplevel ) ) {
				$this->toplevel = $page;
			}
			if ( $title ) {
				$this->title( $title );
			}
			if ( $menu_title ) {
				$this->menu_title( $menu_title );
			}
		}
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
		if ( $page = $this->getCache( 'page' ) ) {
			if ( $section = filter_var( $section ) ) {
				$this->_init_section();
				$cache =& $this->getCache( 'section' );
				$cache = [ 'id' => $page['page'] . '-' . $section ];
				if ( is_string( $title ) ) {
					$cache['title'] = $title;
				}
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
		if ( $this->getCache( 'section' ) ) {
			if ( $field = filter_var( $field ) ) {
				$this->_init_field();
				$cache =& $this->getCache( 'field' );
				$cache = [ 'id' => $field, 'label_for' => $field ];
				if ( $title = filter_var( $title ) ) {
					$cache['title'] = $title;
				}
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
	public function option( $option, $callback = null, $sanitize = null ) {
		$cache =& $this->getCache();
		if ( $cache['field'] ) {
			if ( $option = filter_var( $option ) ) {
				$cache['field']['option'] = $option;
				if ( $callback ) {
					$this->callback( $callback, $sanitize );
				} else if ( $sanitize ) {
					$this->sanitize( $sanitize );
				}
				if ( ! isset( $cache['page']['has_option_field'] ) ) {
					$cache['page']['has_option_fields'] = true;
				}
			}
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
	public function callback( $callback, $sanitize = null ) {
		if ( $cache =& $this->getCurrentCache() ) {
			$option_required = false;
			if ( $cache === $this->getCache( 'field' )
				&& is_string( $callback )
				&& method_exists( self::$view, 'field_callback_' . $callback ) )
			{
				$callback = [ self::$view, 'field_callback_' . $callback ];
				$option_required = true;
			}
			if ( is_callable( $callback ) && ( ! $option_required || isset( $cache['option'] ) ) ) {
				$cache['callback'] = $callback;
				if ( $sanitize ) {
					$this->sanitize( $sanitize );
				}
			}
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
	public function sanitize( Callable $sanitize ) {
		/*
		if ( ! $cache =& $this->getCache( 'field' ) ) {
			return;
		}
		if ( ! isset( $cache['option'] ) ) {
			return;
		}
		$cache['sanitize'] = $sanitize;
		*/
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
		if ( $cache =& $this->getCurrentCache() ) {
			if ( $title = filter_var( $title ) ) {
				$cache['title'] = $title;
			}
		}
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
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $menu_title = filter_var( $menu_title ) ) {
				$cache['menu_title'] = $menu_title;
			}
		}
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
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $capability = filter_var( $capability ) ) {
				$cache['capability'] = $capability;
			}
		}
		return $this;
	}

	/**
	 * Set icon url
	 *
	 * @param  string $icon_url
	 * @return mimosafa\WP\Settings\Page
	 */
	public function icon_url( $icon_url ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $icon_url = filter_var( $icon_url ) ) {
				$cache['icon_url'] = $icon_url;
			}
		}
		return $this;
	}

	/**
	 * Set position in admin menu
	 *
	 * @param  integer $position
	 * @return mimosafa\WP\Settings\Page
	 */
	public function position( $position ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $position = absint( $position ) ) {
				$cache['position'] = $position;
			}
		}
		return $this;
	}

	public function provision( Callable $func ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			$cache['provision'] = $func;
		}
		return $this;
	}

	public function content( $content, $place = null ) {
		if ( $cache =& $this->getCache( 'field' ) ) {
			$fieldCached = true;
		} else if ( $cache =& $this->getCurrentCache() ) {
			$fieldCached = false;
		} else {
			return $this;
		}
		if ( $content = filter_var( $content ) ) {
			if ( $fieldCached && $place && in_array( $place, [ 'before', 'after' ], true ) ) {
				$tar = 'content_' . $place;
			} else {
				$tar = 'content';
			}
			if ( isset( $cache[$tar] ) ) {
				$cache[$tar] = $cache[$tar] . "\n" . $content;
			} else {
				$cache[$tar] = $content;
			}
		}
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
	public function description( $text, $place = null ) {
		if ( $text = filter_var( $text ) ) {
			$this->content( sprintf( '<p class="description">%s</p>', $text ), $place );
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
	public function file( $path, $args = [] ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $path == realpath( $path ) ) {
				$cache['file_path'] = $path;
				$cache['include_file_args'] = (array) $args;
			}
		}
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
	 * @access private
	 *
	 * @return (void)
	 */
	private function _init() {
		$this->_init_page();
		$this->_add_pages();
		add_action( 'admin_init', [ $this, '_add_settings' ] );
	}

	/**
	 * Initialize static cache $page
	 *
	 * @access private
	 * @uses   mimosafa\WP\Settings\Controller::addPageArgs()
	 */
	private function _init_page() {
		$this->_init_section();
		if ( $cache =& $this->getCache( 'page' ) ) {
			$this->pages[] = $cache;
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
		if ( $cache['section'] ) {
			if ( ! isset( $cache['page']['sections'] ) ) {
				$cache['page']['sections'] = [];
			}
			$cache['page']['sections'][] = $cache['section'];
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
		if ( $cache['field'] ) {
			if ( ! isset( $cache['section']['fields'] ) ) {
				$cache['section']['fields'] = [];
			}
			$cache['section']['fields'][] = $cache['field'];
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

	/**
	 * Add pages
	 *
	 * @access private
	 */
	public function _add_pages() {
		foreach ( $this->pages as $page ) {
			$this->_add_page( $page );
		}
		do_action( $this->toplevel . '_added_pages' );
	}

	/**
	 * Add page
	 *
	 * @access private
	 *
	 * @global array $admin_page_hook
	 *
	 * @param  array $page_args
	 * @return void
	 */
	private function _add_page( Array $args ) {
		global $menu, $admin_page_hooks;
		extract( $page_args, \EXTR_SKIP );
		if ( array_key_exists( $page, $admin_page_hooks ) ) {
			/**
			 * Avoiding Duplicated Page
			 */
			return;
		}
		if ( ! isset( $title ) ) {
			$title = __( ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $page ) ) ) );
		}
		if ( ! isset( $menu_title ) ) {
			$menu_title = $title;
		}
		if ( ! isset( $capability ) ) {
			$capability = 'manage_options';
		}
		if ( ! isset( $callback ) ) {
			if ( $page === $this->toplevel && count( $this->pages ) > 1 ) {
				$callback = '';
				add_action( $this->toplevel . '_added_pages', function() {
					/**
					 * No Page Body (Remove Self from Submenus)
					 */
					remove_submenu_page( $this->toplevel, $this->toplevel );
				} );
			}
			else {
				$callback = [ self::$view, 'page_callback' ];
			}
		} else {
			unset( $page_args['callback'] ); // Optimize vars
		}
		if ( $page === $this->toplevel ) {
			if ( ! isset( $icon_url ) ) {
				$icon_url = '';
			}
			if ( ! isset( $position ) ) {
				$position = null;
			}
			/**
			 * Add as top level page
			 */
			$load_page = add_menu_page( $title, $menu_title, $capability, $page, $callback, $icon_url, $position );
		} else {
			/**
			 * Add as sub page
			 */
			$load_page = add_submenu_page( $this->toplevel, $title, $menu_title, $capability, $page, $callback );
		}
		if ( isset( $provision ) ) {
			add_action( 'load-' . $load_page, $provision );
		}
		if ( isset( $sections ) && $sections ) {
			foreach ( $sections as $section ) {
				$this->_add_section( $section, $page );
			}
			unset( $page_args['sections'] ); // Optimize vars
		}
		/**
		 * Cache argument for callback method
		 */
		$argsKey = $this->hash . '_page_' . $page;
		self::$arguments[$argsKey] = $page_args;
	}

}
