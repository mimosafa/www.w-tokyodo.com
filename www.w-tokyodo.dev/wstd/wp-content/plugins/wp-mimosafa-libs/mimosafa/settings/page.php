<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Settings API Controller & Interface Class
 *
 * @access public
 * @uses mimosafa\WP\Settings\View
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

	/**
	 * Argument caches
	 *
	 * @var array
	 */
	private $cache = [ 'page' => [], 'section' => [], 'field' => [], 'option' => [] ];

	/**
	 * @var array
	 */
	private $screen_args = [];

	/**
	 * Options Model Class Instance
	 *
	 * @var mimosafa\WP\Settings\Options
	 */
	private $opts;

	/**
	 * View Class Instance
	 *
	 * @var mimosafa\WP\Settings\View
	 */
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
		self::$view ?: self::$view = View::instance();
		add_action( 'admin_menu', [ $this, '_init' ] );
		if ( $args = func_get_args() ) {
			$opts = $args[0];
			/**
			 * Set Options Instance
			 */
			if ( $opts instanceof Options ) {
				$this->opts = $opts;
			} else if ( $opts && is_string( $opts ) && $opts === sanitize_key( $opts ) && $opts[0] !== '_' ) {
				$this->opts = Options::instance( $opts );
			}
			/**
			 * Initialize Pages from Array
			 */
			if ( isset( $args[1] ) && $args[1] && is_array( $args[1] ) ) {
				$this->_init_pages_array( $args[1] );
			}
		}
	}

	/**
	 * Initialize Pages from Array
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function _init_pages_array( Array $args ) {
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
	 * @param  string $title      Optional
	 * @param  string $menu_title Optional
	 * @return mimosafa\WP\Settings\Page
	 */
	public function page( $page, $title = null, $menu_title = null ) {
		if ( $page = filter_var( $page ) ) {
			$this->_init_page();
			$cache =& $this->getCache( 'page' );
			$cache['page'] = $page;
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
		$cache =& $this->getCache();
		if ( ! empty( $cache['page'] ) ) {
			if ( $section = filter_var( $section ) ) {
				$this->_init_section();
				$cache['section']['id'] = $section;
				if ( is_string( $title ) ) {
					$cache['section']['title'] = $title;
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
		$cache =& $this->getCache();
		if ( ! empty( $cache['section'] ) ) {
			if ( $field = filter_var( $field ) ) {
				$this->_init_field();
				$cache['field']['id'] = $field;
				if ( $title = filter_var( $title ) ) {
					$cache['field']['title'] = $title;
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
		if ( ! empty( $cache['field'] ) ) {
			if ( $option = filter_var( $option ) ) {
				$this->_init_option();
				$cache['option']['option'] = $option;
				if ( $callback ) {
					$this->callback( $callback, $sanitize );
				} else if ( $sanitize ) {
					$this->sanitize( $sanitize );
				}
				if ( ! isset( $cache['page']['has_option_fields'] ) ) {
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
			if ( $cache === $this->getCache( 'option' ) ) {
				if ( is_string( $callback ) && method_exists( self::$view, 'option_callback_' . $callback ) ) {
					$cache['option_callback_type'] = $callback;
				}
				if ( $sanitize ) {
					$this->sanitize( $sanitize );
				}
			}
			else if ( is_callable( $callback ) ) {
				$cache['callback'] = $callback;
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
		if ( $cache =& $this->getCurrentCache() ) {
			if ( substr( $name, 0, 5 ) === 'attr_' ) {
				return $this->misc( [ $name => $params[0] ] );
			}
		}
		return $this;
	}

	/**
	 * Set other argument
	 *
	 * @access public
	 *
	 * @param  array $args
	 * @return mimosafa\WP\Settings\Page
	 */
	public function misc( Array $args ) {
		if ( $cache =& $this->getCurrentCache() ) {
			foreach ( $args as $key => $val ) {
				if ( ! array_key_exists( $key, $cache ) ) {
					$cache[$key] = $val;
				}
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
	public function position( $position, $replace = false ) {
		if ( $cache =& $this->getCache( 'page' ) ) {
			if ( $position = absint( $position ) ) {
				$cache['position'] = $position;
				$cache['replace_menu'] = filter_var( $replace, \FILTER_VALIDATE_BOOLEAN );
			}
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
				$tar = 'content_after';
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
	public function _init() {
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
	 * Initialize static cache $option
	 *
	 * @access private
	 */
	private function _init_option() {
		$cache =& $this->getCache();
		if ( $cache['option'] ) {
			if ( ! isset( $cache['field']['option'] ) ) {
				$cache['field']['option'] = [];
			}
			$cache['field']['option'][] = $cache['option'];
		}
		$cache['option'] = [];
	}

	/**
	 * Get caches of $this
	 *
	 * @access private
	 *
	 * @return &array
	 */
	private function &getCache( $key = null ) {
		if ( ! $key || ! in_array( $key, [ 'page', 'section', 'field', 'option' ] ) ) {
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
		$falseVal = false;
		$cache =& $this->getCache();
		if ( $cache['option'] ) {
			return $cache['option'];
		}
		else if ( $cache['field'] ) {
			return $cache['field'];
		}
		else if ( $cache['section'] ) {
			return $cache['section'];
		}
		else if ( $cache['page'] ) {
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
		global $admin_page_hooks;
		/**
		 * @var string   $page
		 * @var string   $title             Optional
		 * @var string   $menu_title        Optional
		 * @var string   $capability        Optional
		 * @var callable $callback          Optional
		 * @var string   $icon_url          Optional
		 * @var int      $position          Optional
		 * @var boolean  $replace_menu      Optional
		 * @var array    $sections          Optional
		 * @var string   $content           Optional
		 * @var boolean  $has_option_fields Optional
		 * @var string   $file_path         Optional
		 * @var array    $include_file_args Optional
		 */
		extract( $args, \EXTR_SKIP );
		if ( array_key_exists( $page, $admin_page_hooks ) ) {
			/**
			 * Avoiding Duplicated Page
			 */
			return;
		}
		if ( ! isset( $title ) ) {
			$title = __( ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $page ) ) ) );
			$args['title'] = $title;
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
		}
		if ( $page === $this->toplevel ) {
			if ( ! isset( $icon_url ) ) {
				$icon_url = '';
			}
			if ( ! isset( $position ) ) {
				$position = null;
			} else if ( ! $replace_menu ) {
				global $menu;
				$positions = array_keys( $menu );
				for ( $position; true; $position++ ) { 
					if ( ! in_array( $position, $positions, true ) ) {
						break;
					}
				}
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
		/**
		 * Store Args
		 */
		$this->screen_args[$load_page] = self::_page_callback_args( $args );
		if ( isset( $sections ) && $sections ) {
			/**
			 * Sections
			 */
			foreach ( $sections as $section ) {
				$this->_add_section( $section, $page, $load_page );
			}
		}
		add_action( 'load-' . $load_page, [ $this, '_set_screen_args' ] );
	}

	/**
	 * @access private
	 *
	 * @param  array $args
	 * @return array {
	 *     @type string  $page
	 *     @type string  $title
	 *     @type string  $content
	 *     @type boolean $has_option_fields
	 *     @type string  $file_path
	 *     @type array   $include_file_args
	 * }
	 */
	private static function _page_callback_args( Array $args ) {
		$return = [];
		$return['page']              = $args['page'];
		$return['title']             = $args['title'];
		$return['content']           = isset( $args['content'] ) ? $args['content'] : '';
		$return['has_option_fields'] = isset( $args['has_option_fields'] ) ? $args['has_option_fields'] : false;
		$return['file_path']         = isset( $args['file_path'] ) ? $args['file_path'] : '';
		$return['include_file_args'] = isset( $args['include_file_args'] ) ? $args['include_file_args'] : [];
		return $return;
	}

	/**
	 * @access private
	 *
	 * @global WP_Screen $current_screen
	 */
	public function _set_screen_args() {
		global $current_screen;
		if ( empty( $current_screen ) ) {
			set_current_screen();
		}
		$current_screen->settings_page_args = $this->screen_args[$current_screen->base];
	}

	/**
	 * Add section
	 *
	 * @access private
	 *
	 * @param  array  $args
	 * @param  string $page
	 * @param  string $load_page
	 * @return void
	 */
	private function _add_section( Array $args, $page, $load_page ) {
		/**
		 * @var string   $id
		 * @var string   $title
		 * @var callable $callback
		 * @var array    $fields
		 * @var string   $content
		 */
		extract( $args, \EXTR_SKIP );
		if ( ! isset( $title ) ) {
			$title = null;
		}
		if ( ! isset( $callback ) ) {
			$callback = [ self::$view, 'section_callback' ];
		}
		$this->sections[] = [ $id, $title, $callback, $page ];
		if ( isset( $fields ) && $fields ) {
			foreach ( $fields as $field ) {
				$this->_add_field( $field, $page, $id );
			}
		}
		/**
		 * Store Args
		 */
		$this->screen_args[$load_page]['sections'][$id] = self::_section_callback_args( $args );
	}

	/**
	 * @access private
	 *
	 * @param  array $args
	 * @return array {
	 *     @type string $content
	 * }
	 */
	private static function _section_callback_args( Array $args ) {
		$return = [];
		$return['content'] = isset( $args['content'] ) ? $args['content'] : '';
		return $return;
	}

	/**
	 * Add & set field
	 *
	 * @access private
	 *
	 * @param  array  $args
	 * @param  string $page
	 * @param  string $section
	 * @return void
	 */
	private function _add_field( Array $args, $page, $section ) {
		/**
		 * @var string $id
		 * @var string $label_for
		 * @var string $title
		 * @var string $option
		 * @var string $attr_{size}
		 * @var string $label_{left|right}
		 * ..
		 */
		extract( $args, \EXTR_SKIP );
		if ( ! isset( $title ) ) {
			$title = __( ucwords( str_replace( [ '-', '_' ], ' ', $id ) ) );
		}
		if ( ! isset( $callback ) ) {
			$callback = [ self::$view, 'field_callback' ];
		} else {
			/**
			 * Optimize Vars
			 */
			unset( $args['callback'] );
		}
		if ( isset( $option ) && $option ) {
			if ( count( $option ) === 1 ) {
				$args['label_for'] = $option[0]['option'];
			}
			$option_group = 'group_' . $page;
			foreach ( $option as $opt ) {
				if ( ! isset( $opt['sanitize'] ) ) {
					$sanitize = '';
				} else {
					$sanitize = $opt['sanitize'];
					unset( $opt['sanitize'] );
				}
				$this->settings[] = [ $option_group, $option, $sanitize ];
			}
		}
		$this->fields[] = [ $id, $title, $callback, $page, $section, $args ];
	}

	private function _add_option( Array $args, $page ) {
		/**
		 *
		 */
		extract( $args );
		$option_group = 'group_' . $page;
		if ( isset( $sanitize ) ) {
			unset( $args['sanitize'] );
		}
		$this->settings[] = [ $option_group, $option, isset( $sanitize ) ? $sanitize : '' ];
	}

	/**
	 * Setting sections & fields method
	 *
	 * @access private
	 *
	 * @uses   add_settings_section
	 * @uses   add_settings_add_settings_field
	 * @uses   register_setting
	 */
	public function _add_settings() {
		foreach ( $this->sections as $section_arg ) {
			call_user_func_array( 'add_settings_section', $section_arg );
		}
		foreach ( $this->fields as $field_arg ) {
			call_user_func_array( 'add_settings_field', $field_arg );
		}
		foreach ( $this->settings as $setting_arg ) {
			call_user_func_array( 'register_setting', $setting_arg );
		}
	}

}
