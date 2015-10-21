<?php
namespace mimosafa\WP\Settings;

/**
 * WordPress Settings API View Class
 *
 * @access private
 *
 * @package WordPress
 * @subpackage WordPress Libraries by mimosafa
 *
 * @license GPLv2
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */
class View {

	/**
	 * Instance Getter (Singleton)
	 *
	 * @access public
	 */
	public static function instance() {
		static $instance;
		return $instance ?: $instance = new self();
	}

	/**
	 * Singleton Pattern
	 *
	 * @access private
	 */
	private function __construct() {}

	/**
	 * Settings Page Callback
	 *
	 * @global WP_Screen $current_screen
	 */
	public function page_callback() {
		global $current_screen;
		$args = $current_screen->settings_page_args;
		/**
		 * @var string  $page
		 * @var string  $title
		 * @var string  $content
		 * @var boolean $has_option_fields
		 * @var string  $file_path
		 * @var array   $include_file_args
		 */
		extract( $args, \EXTR_SKIP );
		if ( $file_path ) {
			/**
			 * File include
			 */
			extract( $include_file_args, \EXTR_SKIP );
			include( $file_path );
		} else {
			/**
			 * Drow HTML
			 */
			?>
<div class="wrap">
	<h2><?= $title ?></h2><?php
			if ( $has_option_fields ) {
				/**
				 * @see http://wpcj.net/354
				 */
				global $parent_file;
				if ( $parent_file !== 'options-general.php' ) {
					require ABSPATH . 'wp-admin/options-head.php';
				}
			}
			echo $content;
			if ( $has_option_fields ) {
				echo '<form method="post" action="options.php">';
				settings_fields( 'group_' . $page );
			}
			do_settings_sections( $page );
			if ( $has_option_fields ) {
				submit_button();
				echo '</form>';
			}
			?>
</div><?php
		}
		unset( $current_screen->settings_page_args );
	}

	/**
	 * Section Callback
	 *
	 * @global WP_Screen $current_screen
	 */
	public function section_callback( Array $section ) {
		global $current_screen;
		$args = $current_screen->settings_page_args['sections'][$section['id']];
		/**
		 * @var string $content
		 */
		extract( $args, \EXTR_SKIP );
		echo $content;
	}

	/**
	 * Field Callback
	 */
	public function field_callback( Array $args ) {
		/**
		 * @var string $id
		 * @var string $page
		 * @var string $section
		 * @var string $label_for              Optional
		 * @var string $title                  Optional
		 * @var string $option                 Optional
		 * @var string $content_{before|after} Optional
		 * @var string $attr_{$attr}           Optional
		 * ..
		 */
		extract( $args );
		if ( isset( $option ) && $option ) {
			$i = 0;
			foreach ( $option as $opt ) {
				/**
				 * @var array $opt {
				 *     @type string   $option
				 *     @type string   $option_callback_type
				 *     @type callable $callback
				 * }
				 */
				$f = '%s';
				if ( isset( $opt['option_callback_type'] ) ) {
					$cb = [ $this, 'option_callback_' . $opt['option_callback_type'] ];
					printf( $f, call_user_func_array( $cb, [ $opt, $i ] ) );
				} else if ( isset( $opt['callback'] ) ) {
					$tag = sprintf( 'option_callback_%s_%s_%s_%s', $page, $section, $id, $opt['option'] );
					do_action( $tag, $opt );
					# printf( $f, call_user_func( $opt['callback'], $opt ) );
				}
				$i++;
			}
		}
		/*
		$wrap = isset( $option ) && ( isset( $content_before ) || isset( $content_after ) || count( $option ) > 1 );
		echo $wrap ? "<fieldset>\n" : '';
		echo isset( $content_before ) ? $content_before : '';
		if ( isset( $option_callback_type ) ) {
			call_user_func( [ $this, 'option_callback_' . $option_callback_type ], $args );
		}
		echo isset( $content_after ) ? $content_after : '';
		echo $wrap ? "\n</fieldset>" : '';
		*/
	}

	/**
	 * Drow Form Element Checkbox
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function option_callback_checkbox( Array $args, $i ) {
		/**
		 * @var string $option
		 * @var string $label_{left|right}
		 * ..
		 */
		extract( $args );
		$escOpt = esc_attr( $option );
		$attr = checked( get_option( $option ), true, false );
		$label = isset( $label ) ? $label : '';
		if ( $label_l  = isset( $label_l )  ? $label_l  : '' ) {
			$attr .= ' class="label_l"';
		}
		$label_r = isset( $label_r ) ? $label_r : $label;
		$br = $i && isset( $br ) ? '<br>' : '';
		$before = isset( $p ) ? '<p>'  : $br;
		$after  = isset( $p ) ? '</p>' : '';
		/**
		 * Return HTML
		 */
		return <<<EOF
{$before}
<label for="{$escOpt}">
	{$label_l}<input type="checkbox" name="{$escOpt}" id="{$escOpt}" value="1"{$attr}>{$label_r}
</label>
{$after}
EOF;
	}

	/**
	 * Drow Form Element Imput Text
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	public function option_callback_text( Array $args, $i ) {
		/**
		 * @var string $id
		 * @var int    $attr_size
		 */
		extract( $args );
		$escOpt = esc_attr( $option );
		$val = esc_attr( get_option( $option ) );
		$label = isset( $label ) ? $label : '';
		$class = '';
		if ( $label_l  = isset( $label_l )  ? $label_l  : $label ) {
			$class .= 'label_l';
		}
		$label_r = isset( $label_r ) ? $label_r : '';
		$label_open  = $label_l || $label_r ? '<label>' : '';
		$label_close = $label_open ? '</label>' : '';
		$attr = '';
		if ( isset( $attr_size ) ) {
			$attr .= 'size="' . (int) $attr_size . '"';
		}
		if ( ! $attr ) {
			$class .= ' regular-text';
		}
		$attr .= ' class="' . trim( $class ) . '"';
		$br = $i && isset( $br ) ? '<br>' : '';
		$before = isset( $p ) ? '<p>'  : $br;
		$after  = isset( $p ) ? '</p>' : '';
		/**
		 * Return HTML
		 */
		return <<<EOF
{$before}
{$label_open}
	{$label_l}<input type="text" name="{$escOpt}" id="{$escOpt}" value="{$val}" {$attr}>{$label_r}
{$label_close}
{$after}
EOF;
	}

}
