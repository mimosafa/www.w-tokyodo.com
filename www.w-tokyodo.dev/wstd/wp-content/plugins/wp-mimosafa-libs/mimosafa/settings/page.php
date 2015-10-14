<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Settings API interface class
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
	 * @param  string $page
	 * @param  string $page_title Optional
	 * @param  string $menu_title Optional
	 * @return mimosafa\WP\Settings\Page
	 */
	public function init( $page, $page_title = null, $menu_title = null ) {
		$this->_init_page();
		$cache =& $this->getCache( 'page' );
		if ( ! $this->toplevel && ! $page = filter_var( $page ) )
			$page = 'options-general.php';
		if ( $page ) {
			$cache = [ 'page' => $page ];
			if ( ! $this->toplevel )
				$this->toplevel = $page;
			if ( $page_title )
				$this->page_title( $page_title );
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @return mimosafa\WP\Settings\Page
	 */
	public function field( $field_id, $field_title = null ) {
		$this->_init_field();
		if ( ! $field_id = filter_var( $field_id ) )
			return;
		$cache =& $this->getCache( 'field' );
		$cache = [ 'id' => $field_id, 'label_for' => $field_id ];
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @access public
	 */
	public function __call( $name, $params ) {
		return $this->misc( [ $name => $params[0] ] );
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
	 * Set Page Title
	 *
	 * @access public
	 *
	 * @param  string $page_title
	 * @return mimosafa\WP\Settings\Page
	 */
	public function page_title( $page_title ) {
		if ( ! $page_title = filter_var( $page_title ) )
			return;
		if ( ! $cache =& $this->getCurrentCache() )
			return;
		$cache['page_title'] = $page_title;
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @return mimosafa\WP\Settings\Page
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
	 * @param  string  $path
	 * @param  array   $args
	 * @param  boolean $wrap_div Optional. if true wrap $html by 'div'
	 * @return mimosafa\WP\Settings\Page
	 */
	public function file( $path, $args = [], $wrap = false ) {
		if ( ( ! $realPath = realpath( $path ) ) || $realPath != $path )
			return;
		if ( ! $cache =& $this->getCache( 'page' ) )
			return;
		$cache['callback'] = [ &$this, 'include_file' ];
		$cache['file_path'] = $realPath;
		$cache['include_file_args'] = $args;
		$cache['wrap_included_file'] = filter_var( $wrap, \FILTER_VALIDATE_BOOLEAN );
		return $this;
	}

	/**
	 * Set submit button ---- yet !!
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
	public function done() {
		$this->_init_page();
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
	 * @param  array $page_args
	 * @return void
	 */
	private function _add_page( Array $page_args ) {
		global $admin_page_hooks;
		extract( $page_args ); // $page must be generated.
		/**
		 * Avoid duplicate page body display
		 */
		if ( array_key_exists( $page, $admin_page_hooks ) )
			return;
		if ( ! isset( $page_title ) ) {
			$page_title = ucwords( trim( str_replace( [ '-', '_', '/', '.php' ], ' ', $page ) ) );
			$page_args['page_title'] = $page_title;
		}
		if ( ! isset( $menu_title ) ) {
			$menu_title = $page_title;
			$page_args['menu_title'] = $menu_title;
		}
		if ( ! isset( $capability ) ) {
			$capability = 'manage_options';
			$page_args['capability'] = $capability;
		}
		if ( ! isset( $callback ) ) {
			if ( isset( $sections ) || isset( $fields ) || isset( $html ) || isset( $description ) ) {
				$callback = [ &$this, 'page_body' ];
			} else if ( $page === $this->toplevel && count( $this->pages ) > 1 ) {
				$callback = '';
				// Remove submenu
				add_action( __NAMESPACE__ . '_added_pages', function() {
					remove_submenu_page( $this->toplevel, $this->toplevel );
				} );
			} else {
				$callback = [ &$this, 'empty_page' ];
			}
		}
		else
			unset( $page_args['callback'] ); // Optimize vars
		if ( $page === $this->toplevel && ! array_key_exists( $page, $admin_page_hooks ) ) {
			if ( ! isset( $icon_url ) )
				$icon_url = '';
			if ( ! isset( $position ) )
				$position = null;
			/**
			 * Add as top level page
			 */
			add_menu_page( $page_title, $menu_title, $capability, $page, $callback, $icon_url, $position );
		} else {
			/**
			 * Add as sub page
			 */
			add_submenu_page( $this->toplevel, $page_title, $menu_title, $capability, $page, $callback );
		}
		/**
		 * Sections
		 */
		if ( isset( $sections ) && $sections ) {
			foreach ( $sections as $section ) {
				$this->_add_section( $section, $page );
			}
			unset( $page_args['sections'] ); // Optimize vars
		}
		/**
		 * fields
		 */
		if ( isset( $fields ) && $fields ) {
			foreach ( $fields as $field ) {
				$this->_add_field( $field, $page );
			}
			unset( $page_args['fields'] ); // Optimize vars
		}
		/**
		 * Cache argument for callback method
		 */
		$argsKey = $this->hash . '_page_' . $page;
		self::$arguments[$argsKey] = $page_args;
	}

	/**
	 * Add section
	 *
	 * @access private
	 *
	 * @param  array  $section
	 * @param  string $menu_slug
	 * @return void
	 */
	private function _add_section( Array $section, $menu_slug ) {
		extract( $section ); // $id must be generated
		if ( ! isset( $title ) )
			$title = null;
		if ( ! isset( $callback ) )
			$callback = [ &$this, 'section_body' ];
		else
			unset( $section['callback'] ); // Optimize vars
		$this->sections[] = [ $id, $title, $callback, $menu_slug ];
		/**
		 * fields
		 */
		if ( isset( $fields ) && $fields ) {
			foreach ( $fields as $field ) {
				$this->_add_field( $field, $menu_slug, $id );
			}
			unset( $section['fields'] ); // Optimize vars
		}
		/**
		 * Cache argument for callback method
		 */
		$argsKey = $this->hash . '_section_' . $id;
		self::$arguments[$argsKey] = $section;
	}

	/**
	 * Add & set field
	 *
	 * @access private
	 *
	 * @param  array  $field
	 * @param  string $menu_slug
	 * @param  string $section_id Optional.
	 * @return void
	 */
	private function _add_field( Array $field, $menu_slug, $section_id = '' ) {
		extract( $field ); // $id must be generated
		if ( ! isset( $title ) ) {
			$title = ucwords( str_replace( [ '-', '_' ], ' ', $id ) );
			$field['title'] = $title;
		}
		if ( ! isset( $callback ) )
			$callback = [ &$this, 'field_body' ];
		else
			unset( $field['callback'] ); // Optimize vars
		if ( isset( $option ) ) {
			$option_group = 'group_' . $menu_slug;
			if ( ! isset( $sanitize ) || ( ! method_exists( __CLASS__, $sanitize ) && ! is_callable( $sanitize ) ) )
				$sanitize = '';
			else if ( isset( $sanitize ) )
				unset( $field['sanitize'] ); // Optimize vars
			$this->settings[] = [ $option_group, $option, $sanitize ];
		}
		$this->fields[] = [ $id, $title, $callback, $menu_slug, $section_id, $field ]; // $field is argument for callback method
	}

	/**
	 * Setting sections & fields method
	 *
	 * @access private
	 */
	public function _add_settings() {
		if ( ! doing_action( 'admin_init' ) || ! $this->pages )
			return;
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

	/**
	 * Drow default page html (if has form)
	 * 
	 * @return void
	 */
	public function page_body() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		if ( ! $arg = self::$arguments[$this->hash . '_page_' . $menu_slug] )
			return;
		echo '<div class="wrap">';
		echo "<h2>{$arg['page_title']}</h2>";
		if ( isset( $arg['has_option_fields'] ) ) {
			/**
			 * @see http://wpcj.net/354
			 */
			global $parent_file;
			if ( $parent_file !== 'options-general.php' )
				require ABSPATH . 'wp-admin/options-head.php';
		}
		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';
		if ( isset( $arg['has_option_fields'] ) ) {
			echo '<form method="post" action="options.php">';
			settings_fields( 'group_' . $menu_slug );
		}
		
		do_settings_fields( $menu_slug, '' );
		do_settings_sections( $menu_slug );
		if ( isset( $arg['has_option_fields'] ) ) {
			submit_button();
			echo '</form>';
		}
		echo '</div>';
	}

	public function empty_page() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		# do_action( 'ddbbd_settings_page_empty_page_' . $menu_slug );
	}

	/**
	 *
	 */
	public function include_file() {
		$menu_slug = filter_input( \INPUT_GET, 'page' );
		$args = self::$arguments[$this->hash . '_page_' . $menu_slug];
		$path = $args['file_path'];
		$wrap = $args['wrap_included_file'];
		$title = $wrap && isset( $args['page_title'] ) ? $args['page_title'] : '';
		if ( $args = self::$arguments['page_' . $menu_slug]['include_file_args'] )
			extract( $args );
		echo $wrap ? '<div class="wrap">' : '';
		echo $title ? '<h2>' . $title . '</h2>' : '';
		include $path;
		echo $wrap ? '</div>' : '';
	}

	/**
	 * @param  array $array
	 */
	public function section_body( Array $array ) {
		$arg = self::$arguments[$this->hash . '_section_' . $array['id']];
		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';
	}

	/**
	 * @param  array $array
	 */
	public function field_body( Array $arg ) {
		echo isset( $arg['description'] ) ? $arg['description'] : '';
		echo isset( $arg['html'] ) ? $arg['html'] : '';
	}

	/**
	 * Drow form element Checkbox
	 */
	public function checkbox( Array $args ) {
		static $def = [
			'id'          => \FILTER_DEFAULT,
			'option'      => \FILTER_DEFAULT,
			'label'       => \FILTER_DEFAULT,
			'description' => \FILTER_DEFAULT,
			'html'        => \FILTER_DEFAULT,
		];
		$args = filter_var_array( $args, $def );
		extract( $args );
		if ( $id && $option ) {
?>
<label for="<?= esc_attr( $id ) ?>">
	<input type="checkbox" name="<?= esc_attr( $option) ?>" id="<?= esc_attr( $id ) ?>" value="1"<?php checked( get_option( $option) ); ?>>
	<?= $label ?>
</label>
<?= $description ?>
<?= $html ?>
<?php
		}
	}

	/**
	 * Drow form element Imput Text
	 */
	public function text( Array $args ) {
		static $def = [
			'id'          => \FILTER_DEFAULT,
			'option'      => \FILTER_DEFAULT,
			'size'        => [ 'filter' => \FILTER_VALIDATE_INT, 'options' => [ 'min_range' => 1 ] ],
			'description' => \FILTER_DEFAULT,
			'html'        => \FILTER_DEFAULT
		];
		$args = filter_var_array( $args, $def );
		extract( $args );
		if ( $id && $option ) {
			$attr = '';
			if ( $size ) {
				$attr .= ' size="' . $size . '"';
			}
			if ( ! $attr ) {
				$attr .= ' class="regular-text"';
			}
?>
<input type="text" name="<?= esc_attr( $option ) ?>" id="<?= esc_attr( $id) ?>" value="<?php form_option( $option ); ?>"<?= $attr ?>>
<?= $description ?>
<?= $html ?>
<?php
		}
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
