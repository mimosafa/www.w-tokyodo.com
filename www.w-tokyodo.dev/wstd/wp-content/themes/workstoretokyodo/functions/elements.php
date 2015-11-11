<?php

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
