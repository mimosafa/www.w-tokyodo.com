<?php

/**
 * areaSiteHeader - Legacy
 * - used in header.php
 *
 * @uses is_page_top
 * @uses is_division
 * @uses get_division
 */
function area_site_header_legacy() {

    /**
     * output #wstdLogo
     */
    function wstd_logo() {
        if ( is_division() )
            echo '<a href="' . home_url() . '" class="packOff">Workstore Tokyo Do</a>';
        else
            echo '<span>食を通じて「賑わい」と「笑顔」と「思い出」を作るプロフェッショナル!!</span>';
    }

    function contact_hash() {
        $hash = '';
        if ( is_division( 'direct' ) )
            $hash = '#event';
        elseif ( is_division( 'sharyobu' ) )
            $hash = '#car';
        echo $hash;
    }

    /**
     * output #siteid
     */
    function site_id() {
        $string = '株式会社ワークストア・トウキョウドゥ';
        $href   = home_url();
        if ( $division = get_division() ) {
            $string = esc_html( $division->post_title );
            $href = get_permalink( $division );
        }
        if ( is_home() || ( is_division() && is_page_top() ) )
            echo '<h1 class="packOff">' . $string . '</h1>';
        else
            echo '<a href="' . $href . '" class="packOff">' . $string . '</a>';
    }

    /**
     * output division nav list
     */
    function nav_lists() {
        $array = array(
            'direct'   => '<li id="btn-tokyodo"%s>%sTokyo Do%s</li>',
            'neostall' => '<li id="btn-neostall"%s>%sネオ屋台村%s</li>',
            'neoponte' => '<li id="btn-neoponte"%s>%sネオポンテ%s</li>',
            'sharyobu' => '<li id="btn-sharyobu"%s>%s車両部%s</li>'
        );
        foreach ( $array as $key => $format ) {
            if ( !is_division( $key ) )
                printf( $format, '', '<a href="/' . $key . '">', '</a>' );
            else
                printf( $format, ' class="now-on-display"', '', '' );
            echo "\n";
        }
    }

?>
<?php //<div class="visible-md visible-lg visible-sm"> ?>
<div>
<div id="areaSiteHeader">
<div class="container">
<div id="wstdLogo">
<?php wstd_logo(); ?>
</div>
<ul id="wstdCompanyNav">
<li><a href="#" class="hide"><i class="fa fa-child"></i> 採用情報</a></li>
<li><a href="/#company"><i class="fa fa-building-o"></i> 会社案内</a></li>
<li><a href="/contact<?php contact_hash(); ?>"><i class="fa fa-envelope"></i> お問い合わせ</a></li>
</ul>
</div>
</div><!-- /#areaSiteHeader -->
<div id="header-legacy">
<div class="container">
<div id="siteid">
<?php site_id(); ?>
</div>
<ul>
<?php nav_lists(); ?>
</ul>
</div>
</div><!-- /.header-legacy -->
</div>
<?php
}

/**
 * header image
 * - used in header.php
 *
 * @uses is_page_top
 * @uses is_division
 */
function wstd_header_image() {
	$image = '';
	if ( is_division() && is_page_top() ) {
		if ( has_post_thumbnail() )
			$image = esc_url( wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' )[0] );
        /*
        */
		else
			$image = esc_url( 'http://fakeimg.pl/1024x300/?text=Dummy!');
	} elseif ( is_home() ) {
		$image = get_stylesheet_directory_uri() . '/img/great-founder-darker.jpg';
	}
	if ( $image ) {
?>
<div class="wstd-header-image" style="background-image:url(<?php echo $image; ?>);"><?php
        if ( is_home() ) { ?>
<p id="kokokara">全てはココから始まった…<br><small>- 昭和38年頃、晴海埠頭にて</small></p><?php
        } elseif ( $subtitle = get_post_meta( get_the_ID(), 'subtitle', true ) ) { ?>
<h2 class="h3"><?php echo esc_html( $subtitle ); ?></h2><?php
        } ?>
</div>
<?php
	}
}

/**
 * breadcrumb
 * - used in nowhere 2014/3/11
 */
function wstd_breadcrumb() {
	if ( is_home() || ( is_division() && is_page_top() ) )
		return;
	global $post;
	$bc = array();
	if ( !is_division() ) {
		$bc[] = array( 'Workstore Tokyo Do', home_url() );
		if ( is_page() ) {
			$anc = get_ancestors( $post->ID, 'page' );
			rsort( $anc );
			foreach ( $anc as $page_id ) {
				$bc[] = array( get_the_title( $page_id ), get_permalink( $page_id ) );
			}
		}
		$bc[] = get_the_title( $post );
	}
	if ( !empty( $bc ) ) { ?>
<ul class="breadcrumb">
<?php
		foreach ( $bc as $link ) {
			if ( is_array( $link ) ) { ?>
<li><a href="<?php echo esc_url( $link[1] ); ?>"><?php echo esc_html( $link[0] ); ?></a></li>
<?php
			} else { ?>
<li><?php echo esc_html( $link ); ?></li>
<?php
			}
		} ?>
</ul>
<?php
	}
}