<?php

/**
 * 未ログインユーザーは拒否
 */
function wstd_404_redirect() {
	if ( is_user_logged_in() || $_SERVER['REMOTE_ADDR'] === '39.2.180.73' )
		return;
	wp_redirect( 'http://www.w-tokyodo.com/', '404' );
}
//add_action( 'init', 'wstd_404_redirect' );

function wstd_301_redirect() {
	if ( '/company' === $_SERVER['REQUEST_URI'] )
		wp_redirect( home_url() . '#company', '301' );
}
add_action( 'init', 'wstd_301_redirect' );

function wstd_302_redirect() {
	if ( preg_match( '/^\/sharyobu\/+./', $_SERVER['REQUEST_URI'] ) ) {
		wp_redirect( get_permalink( get_page_by_path( 'sharyobu' )->ID ), '302' );
	}
	if ( preg_match( '/^\/direct\/+./', $_SERVER['REQUEST_URI'] ) ) {
		wp_redirect( get_permalink( get_page_by_path( 'direct' )->ID ), '302' );
	}
}
add_action( 'init', 'wstd_302_redirect' );
add_filter( '404_template', 'wstd_302_redirect' );
/*
*/

/**
 * view, model, snippet などが定義されたファイルを読み込む。
 * 今回の場合、すべて functionsディレクトリーにまとめている。
 * 読み込みたくないファイルは削除をするか、ファイル名の最初に'_'を付けるかする。
 *
 * @param  string $path 読み込むディレクトリーのパス。要スラッシュ。
 * @return void
 *
 * 参考エントリー
 * @link http://dogmap.jp/2011/04/19/wordpress-managed-snippet/
 * @link http://kanamehackday.blog17.fc2.com/blog-entry-245.html
 *
 * ...開発が一段落したらちゃんと全部 require_once, または get_template_partする？
 */
class theme_functions_autoload {
	/**
	 * Singleton
	 *
	 * @link http://stein2nd.wordpress.com/2013/10/04/wordpress_and_oop/
	 * @link http://ja.phptherightway.com/pages/Design-Patterns.html
	 */
	public static function get_instance() {
		static $instance = null;
		if ( null == $instance )
			$instance = new static();
		return $instance;
	}
	private function __construct() {}
	private function __clone() {}
	private function __waleup() {}

	/**
	 * 読み込む phpファイル
	 */
	private $php_files = array();

	/**
	 * ディレクトリーに含まれる phpファイルを走査
	 */
	private function read_dir( $path ) {
		if ( !is_dir( $path ) )
			return;
		$dir = array();
		$entries = scandir( $path );
		foreach ( $entries as $entry ) {
			if ( '.' == $entry || '..' == $entry || '_' == $entry[0] )
				continue;
			$result = $path . $entry;
			if ( is_dir( $result ) )
				$dir[] = $this->read_dir( $result . '/' );
			elseif ( '.php' === strtolower( substr( $result, -4 ) ) )
				$dir[] = $result;
		}
		/**
		 * $dir は配列が入れ子になっていたりするので別関数で展開、$php_files に格納する
		 */
		$this->set_php_files( $dir );
	}

	private function set_php_files( $dir ) {
		if ( !empty( $dir ) ) {
			foreach ( $dir as $var ) {
				if ( is_array( $var ) )
					$this->set_php_files( $var );
				elseif ( is_file( $var ) ) // よく分からないが nullが混じっちゃうので…
					$this->php_files[] = $var;
			}
		}
	}

	/**
	 * 初期化 - $php_filesに格納された phpファイルを require_onceする
	 */
	public function init( $path ) {
		$this->read_dir( $path );
		if ( !empty( $this->php_files ) ) {
			foreach ( $this->php_files as $file )
				require_once( $file );
		}
	}
}
add_action( 'after_setup_theme', function() {
	$tfa = theme_functions_autoload::get_instance();
	$tfa->init( trailingslashit( get_stylesheet_directory() ) . 'functions/' );
} );

/**
 * Workstore Tokyo Do, theme setup
 */
function workstoretokyodo_theme_setup() {

	/**
	 * Post Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );

	/**
	 * remove action - wp_head
	 */
	add_action( 'template_redirect', function() {
		if ( is_page() ) {
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
		}
	} );

	/**
	 * default scripts and styles enqueue
	 * - bootstrap 3.1.1
	 * - fontawesome 4.0.3
	 * - modernizr 2.7.1
	 * ...and theme style, script
	 */
	add_action( 'wp_enqueue_scripts', function() {
		if ( !is_admin() ) {

			/**
			 * styles
			 */
			wp_enqueue_style( 'bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.css', array(), '3.1.1' );
			wp_enqueue_style( 'font-awesone', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css', array( 'bootstrap' ), '4.0.3' );
			wp_enqueue_style( 'wstd', get_stylesheet_uri(), array(), date( 'YmdHis', filemtime( get_stylesheet_directory() . '/style.css' ) ) );
			// wstd icon font
			$path = '/css/tokyodo2014.css';
			wp_enqueue_style( 'tokyodo2014', get_stylesheet_directory_uri() . $path, array(), date( 'YmdHis', filemtime( get_stylesheet_directory() . $path ) ) );

			/**
			 * scripts
			 */
			wp_enqueue_script( 'wstd', get_stylesheet_directory_uri() . '/js/script.js', array( 'jquery' ), date( 'YmdHis', filemtime( get_stylesheet_directory() . '/js/script.js' ) ), true );
			wp_enqueue_script( 'modernizr', '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.7.1/modernizr.min.js', array(), '2.7.1' );
			wp_enqueue_script( 'bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js', array( 'jquery' ), '3.1.1', true );

		}
	} );

	/**
	 * カスタム投稿タイプ - event-works
	 */
	add_action( 'init', function() {
		/**
		 * イベントデータ
		 */
		register_post_type( 'event', array(
			'labels' => array( 'name' => 'イベント実績' ),
			'public' => true,
			'has_archive' => true,
			'menu_position' => 5,
			'menu_icon' => 'dashicons-smiley',
			'taxonomies' => array( 'works' )
		) );
		/**
		 * 車両データ
		 */
		register_post_type( 'car', array(
			'labels' => array( 'name' => '車両' ),
			'public' => true,
			'has_archive' => true,
			'menu_position' => 5,
		) );
		/**
		 * 業務種別
		 */
		register_taxonomy( 'works', array( 'event', 'attachment' ), array(
			'labels' => array( 'name' => '業務種別' ),
			'public' => true
		) );
		/**
		 * 車種
		 */
		register_taxonomy( 'model', array( 'car' ), array(
			'labels' => array( 'name' => '車種' ),
			'public' => true,
			'hierarchical' => true
		) );
		/**
		 * 車両カテゴリー
		 */
		register_taxonomy( 'car-category', array( 'car' ), array(
			'labels' => array( 'name' => '車両カテゴリー' ),
			'public' => true
		) );
	} );

	/**
	 * 管理画面の車両一覧で、投稿タイトルの前にに車両カテゴリーを挿入する
	 */
	add_action( 'load-edit.php', function() {
		$screen = get_current_screen();
		if ( 'car' === $screen->post_type ) {
			add_filter( 'the_title', function( $title, $id ) {
				$cats = get_the_terms( $id, 'car-category' );
				$pre_title = '';
				if ( $cats ) {
					$pre_title .= '[ ';
					foreach ( $cats as $cat ) {
						$pre_title .= esc_html( $cat->name ) . ', ';
					}
					$pre_title = substr( $pre_title, 0, -2 ) . ' ] ';
				}
				return $pre_title . $title;
			}, 10, 2 );
		}
	} );

	/**
	 * 固定ページに抜粋
	 */
	add_post_type_support( 'page', 'excerpt' );

	/**
	 * Google Analytics tracking code
	 */
	add_action( 'wp_head', function() {
		if ( !is_user_logged_in() ) { ?>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-5722525-1', 'w-tokyodo.com');
  ga('send', 'pageview');

</script><?php
		}
	} );
}
add_action( 'after_setup_theme', 'workstoretokyodo_theme_setup' );

/**
 * cusom post type 'event' meta box
 */
add_action( 'init', function() {
	register_post_type( 'event', array( 'register_meta_box_cb' => 'event_meta_box' ) );
} );

function event_meta_box() {
	$images = get_children( array(
		'post_parent' => get_the_ID(),
		'post_type' => 'attachment',
		'post_mime_type' => 'image'
	) );
	$works = get_terms( 'works' );
	foreach ( $works as $work ) {
		$slug  = esc_attr( $work->slug );
		$title = esc_html( $work->name );
		add_meta_box(
			"event_works_{$slug}",
			$title,
			'event_works_meta_box_cb',
			'event',
			'normal',
			'default',
			array( 'work' => $work, 'slug' => $slug, 'images' => $images )
		);
	}
}
function event_works_meta_box_cb( $post, $metabox ) {
	$title  = $metabox['title'];
	$slug   = $metabox['args']['slug'];
	$work   = $metabox['args']['work'];
	$attachments = array();
	if ( $images = $metabox['args']['images'] ) {
		foreach ( $images as $image ) {
			if ( $term_objs = get_the_terms( $image->ID, 'works' ) ) {
				$terms = array();
				foreach ( $term_objs as $term_obj ) {
					$terms[] = $term_obj->slug;
				}
				if ( in_array( $slug, $terms ) ) {
					$attachments[] = $image;
				}
			}
		}
	}
?>
<p><?php echo $title; ?>の画像:</p>
<div id="event-images-<?php echo $slug; ?>">
<?php if ( !empty( $attachments ) ) { ?>
<pre>
<?php var_dump( $attachments ); ?>
</pre>
<?php } else { ?>
<p>画像がありません</p>
<?php } ?>
</div>
<input type="button" class="button event-works-attachment-add" data-target="#event-images-<?php echo $slug; ?>" value="Select" />
<?php
}

/**
 *
 */
add_action( 'init', function() {
	register_post_type( 'attachment', array( 'taxonomies' => array( 'works' ) ) );
} );

add_filter( 'gallery_style', 'rm_gallery_style' );
function rm_gallery_style() {
	return '<div class="wstd-gallery">';
}


/**
 *
 */
/*
function manage_attachment_image_metabox() {
	//global $post;
	$images = get_children( array(
		'post_parent' => get_the_ID(),
		'post_type' => 'attachment',
		'post_mime_type' => 'image'
	) );
	$screens = array( 'post', 'page' );
	foreach ( $screens as $screen ) {
		add_meta_box(
			'manage_attachiment_image',
			'添付画像',
			'manage_attachiment_image_cb',
			$screen,
			'normal',
			'default',
			$images
		);
	}
}
add_action( 'add_meta_boxes', 'manage_attachment_image_metabox' );

function manage_attachiment_image_cb( $post, $metabox ) {
	$images = $metabox['args'];
	if ( empty( $images ) )
		return;
	foreach ( $images as $image ) {
		echo wp_get_attachment_image( $image->ID );
	}
}
*/