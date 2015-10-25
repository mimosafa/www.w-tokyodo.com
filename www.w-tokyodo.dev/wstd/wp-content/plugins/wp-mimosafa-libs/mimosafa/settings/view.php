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
	 * @access public
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
		 * @var string  $class
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
			$page_class = 'wrap';
			if ( $class ) {
				$page_class .= ' ' . trim( $class );
			}
			?>
<div class="<?= esc_attr( $page_class ) ?>">
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
	 * @access public
	 *
	 * @global WP_Screen $current_screen
	 *
	 * @param  array $args
	 */
	public function section_callback( Array $args ) {
		global $current_screen;
		$args = $current_screen->settings_page_args['sections'][$args['id']];
		/**
		 * @var string $content
		 */
		extract( $args, \EXTR_SKIP );
		echo $content;
	}

	/**
	 * Field Callback
	 *
	 * @access public
	 *
	 * @param  array $args
	 */
	public function field_callback( Array $args ) {
		/**
		 * @var string $field
		 * @var string $label_for              Optional
		 * @var string $title                  Optional
		 * @var array  $options                Optional
		 * @var string $content_{before|after} Optional
		 * @var string $attr_{$attr}           Optional
		 * ..
		 */
		extract( $args );
		$fs = isset( $options ) && count( $options ) > 1 ? '<fieldset id="' . $field . '">' : '';
		echo $fs ?: '';
		echo isset( $content_before ) ? $content_before : '';
		if ( isset( $options ) && $options ) {
			$i = 0;
			foreach ( $options as $option ) {
				if ( isset( $option['option'] ) ) {
					$this->do_option_callback( $option, $i );
					$i++;
				}
			}
		}
		echo isset( $content_after ) ? $content_after : '';
		echo $fs ? '</fieldset>' : '';
	}

	/**
	 * Option Callback
	 *
	 * @access private
	 *
	 * @param  array $args
	 */
	private function do_option_callback( Array $args, $i ) {
		/**
		 * @var string   $option
		 * @var string   $option_callback_type   Optional
		 * @var callable $callback               Optional
		 * @var boolean  $br                     Optional
		 * @var boolean  $p                      Optional
		 * @var string   $content_{before|after} Optional
		 * @var string   $label_{before|after}   Optional
		 * @var array    $items                  Optional
		 * ..
		 */
		extract( $args, \EXTR_SKIP );
		$br = $i && isset( $br ) ? '<br>' : '';
		echo isset( $p ) ? '<p>' : $br;
		echo isset( $content_before ) ? $content_before : '';
		if ( isset( $option_callback_type ) ) {
			$callback = [ $this, 'option_callback_' . $option_callback_type ];
		}
		else if ( isset( $items ) && $items ) {
			$callback = [ $this, 'option_callback_select' ];
		}
		echo call_user_func( $callback, $args );
		echo isset( $content_after ) ? $content_after : '';
		echo isset( $p ) ? '</p>' : '';
	}

	/**
	 * Render <input type="CHECKBOX">
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function option_callback_checkbox( Array $args ) {
		/**
		 * @var string $option
		 * @var string $label                Optional
		 * @var string $label_{before|after} Optional
		 * @var string $class                Optional
		 * ..
		 */
		extract( $args );
		$escOpt = esc_attr( $option );
		$attr = checked( get_option( $option ), true, false );
		$label = isset( $label ) ? $label : '';
		$class = isset( $class ) ? $class : '';
		if ( $label_before  = isset( $label_before )  ? $label_before  : '' ) {
			$class = ' label_before';
		}
		$label_after = isset( $label_after ) ? $label_after : $label;
		if ( $class ) {
			$attr .= ' class="' . esc_attr( trim( $class ) ) . '"';
		}
		/**
		 * Render HTML
		 */
		$html = <<<EOF
<label for="{$escOpt}">
	{$label_before}<input type="checkbox" name="{$escOpt}" id="{$escOpt}" value="1"{$attr}>{$label_after}
</label>
EOF;
		/**
		 * Return HTML
		 */
		return apply_filters( 'mimosafa_settings_option_form_html', trim( $html ), $option );
	}

	/**
	 * Render <INPUT> - Common
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function _option_callback_input( Array $args ) {
		/**
		 * @var string $type
		 * @var string $option
		 * @var string $val
		 * @var string $attr
		 * @var string $before
		 * @var string $after
		 */
		extract( $args );
		$option = esc_attr( $option );
		$attr = trim( $attr );
		$label_open  = $before || $after ? '<label>' : '';
		$label_close = $label_open ? '</label>' : '';
		/**
		 * Render HTML
		 */
		$html = <<<EOF
{$label_open}
	{$before}<input type="{$type}" name="{$option}" id="{$option}" value="{$val}" {$attr}>{$after}
{$label_close}
EOF;
		/**
		 * Return HTML
		 */
		return apply_filters( 'mimosafa_settings_option_form_html', trim( $html ), $option );
	}

	/**
	 * Render <input type="TEXT">
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function option_callback_text( Array $args ) {
		/**
		 * @var string $option
		 * @var int    $size                 Optional
		 * @var string $label                Optional
		 * @var string $label_{before|after} Optional
		 * @var string $class                Optional
		 */
		extract( $args );
		static $type = 'text';
		$val = get_option( $option );
		$class = isset( $class ) ? $class : '';
		$label = isset( $label ) ? $label : '';
		$before  = isset( $label_before )  ? $label_before  : $label;
		$after = isset( $label_after ) ? $label_after : '';
		$class .= $before || $after ? ' labeled' : '';
		$attr = '';
		if ( isset( $size ) ) {
			$attr .= ' size="' . (int) $size . '"';
		}
		if ( ! $attr ) {
			$class .= ' regular-text';
		}
		$attr .= ' class="' . esc_attr( trim( $class ) ) . '"';
		return $this->_option_callback_input(
			compact( 'type', 'option', 'val', 'attr', 'before', 'after' )
		);
	}

	/**
	 * Render <TEXTAREA>
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function option_callback_textarea( Array $args ) {
		/**
		 * @var string $option
		 * @var int    $cols                 Optional
		 * @var int    $rows                 Optional
		 * @var string $label                Optional
		 * @var string $label_{before|after} Optional
		 * @var string $class                Optional
		 */
		extract( $args );
		$escOpt = esc_attr( $option );
		$val = esc_textarea( get_option( $option ) );
		$class = isset( $class ) ? $class : '';
		$label = isset( $label ) ? $label : '';
		if ( $label_before = isset( $label_before ) ? $label_before : $label ) {
			$label_before = '<p><label for="' . $escOpt . '">' . $label_before . '</label></p>';
		}
		if ( $label_after = isset( $label_after ) ? $label_after : '' ) {
			$label_after = '<p><label for="' . $escOpt . '">' . $label_after . '</label></p>';
		}
		$attr = '';
		if ( isset( $cols ) ) {
			$attr .= ' cols="' . (int) $cols . '"';
		}
		if ( isset( $rows ) ) {
			$attr .= ' rows="' . (int) $rows . '"';
		}
		if ( ! $attr ) {
			$class .= ' large-text';
		}
		if ( $class ) {
			$attr .= ' class="' . esc_attr( trim( $class ) ) . '"';
		}
		/**
		 * Render HTML
		 */
		$html = <<<EOF
{$label_before}
<p><textarea id="{$escOpt}" name="{$escOpt}"{$attr}>{$val}</textarea></p>
{$label_after}
EOF;
		/**
		 * Return HTML
		 */
		return apply_filters( 'mimosafa_settings_option_form_html', trim( $html ), $option );
	}

	/**
	 * Render <SELECT>
	 *
	 * @param  array $args
	 * @return void
	 */
	private function option_callback_select( Array $args ) {
		/**
		 * @var string  $option
		 * @var array   $items
		 * @var string  $label        Optional
		 * @var string  $label_before Optional
		 * @var string  $label_after  Optional
		 * @var string  $class        Optional
		 * @var boolean $has_blank    Optional
		 */
		extract( $args );
		if ( ! isset( $items ) || ! $items ) {
			return;
		}
		$escOpt = esc_attr( $option );
		$val = get_option( $option );
		$class = isset( $class ) ? $class : '';
		$label = isset( $label ) ? $label : '';
		if ( $label_before  = isset( $label_before )  ? $label_before  : $label ) {
			$class .= ' label_before';
		}
		if ( $label_after = isset( $label_after ) ? $label_after : '' ) {
			$class .= ' label_after';
		}
		$label_open  = $label_before || $label_after ? '<label>' : '';
		$label_close = $label_open ? '</label>' : '';
		$attr = '';
		//
		$options = '';
		if ( isset( $has_blank ) ? filter_var( $has_blank, \FILTER_VALIDATE_BOOLEAN ) : true ) {
			$options .= '<option>' . apply_filters( 'mimosafa_settings_select_option_blank_label', '-', $option ) . '</option>';
		}
		foreach ( $items as $key => $label ) {
			$options .= '<option value="' . esc_attr( $key ) . '" ' . selected( $val, $key, false ) .'>' . $label . '</option>';
		}
		/**
		 * Render HTML
		 */
		$html = <<<EOF
{$label_open}
	{$label_before}
	<select name="{$escOpt}" id="{$escOpt}"{$attr}>
		{$options}
	</select>
	{$label_after}
{$label_close}
EOF;
		/**
		 * Return HTML
		 */
		return apply_filters( 'mimosafa_settings_option_form_html', trim( $html ), $option );
	}

	/**
	 * Checkboxes
	 *
	 * @access private
	 *
	 * @param  array $args
	 * @return void
	 */
	private function option_callback_checkboxes( Array $args ) {
		/**
		 * @var string $option
		 * @var array  $items
		 */
		extract( $args );
		if ( ! isset( $items ) || ! $items ) {
			return;
		}
		$escOpt = esc_attr( $option );
		$val = get_option( $option );
		$options = '';
		foreach ( $items as $key => $lbl ) {
			$options .= '<label><input type="checkbox"' . checked( $val, $key, false ) . '>' . $lbl . '</label>'; 
		}
		//
		/**
		 * Render HTML
		 */
		$html = <<<EOF
<fieldset id="{$escOpt}">
	{$options}
</fieldset>
EOF;
		/**
		 * Return HTML
		 */
		return apply_filters( 'mimosafa_settings_option_form_html', trim( $html ), $option );
	}

}
