<?php
/**
 * header image
 */
add_action( 'wstd_header_image', function() {
	if ( has_post_thumbnail() )
		$image = esc_url( wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' )[0] );
	else
		$image = esc_url( 'http://fakeimg.pl/1024x300/?text=Dummy!');
?>
<div class="wstd-header-image" style="background-image:url(<?php echo $image; ?>);"><?php
    if ( $subtitle = get_post_meta( get_the_ID(), 'subtitle', true ) ) { ?>
<h2 class="h3"><?php echo esc_html( $subtitle ); ?></h2><?php
    } ?>
</div><?php
} );

get_header();

/**
 * main loop
 */
the_post();

/**
 * sub pages query
 */
$children = get_posts( array(
	'post_parent' => get_the_ID(),
	'post_type' => 'page',
	'orderby' => 'menu_order',
	'order' => 'ASC'
) );
if ( !empty( $children ) ) : ?>
<section <?php post_class( 'row division-child-page' ); ?>><?php

	/**
	 * sub pages loop
	 */
	foreach ( $children as $post ) {
		setup_postdata( $post );

		/**
		 * activate <!--more-->
		 * @link http://codex.wordpress.org/Function_Reference/the_content
		 */
		//global $more;
		//$more = 0;

		/**
		 * division 'DIRECT'
		 */
		if ( is_division( 'direct' ) ) {
			if ( has_post_thumbnail() ) { ?>
<div class="col-lg-9 col-sm-8">
<header class="page-header">
<?php the_title( '<h3>', '</h3>' ); ?>
</header>
<?php the_content(); //the_excerpt(); ?>
</div>
<div class="child-page-image col-lg-3 col-sm-4">
<?php the_post_thumbnail( 'medium' ); ?>
</div><?php
			} else { ?>
<div class="col-sm-12">
<header class="page-header">
<?php the_title( '<h3>', '</h3>' ); ?>
</header>
<?php the_content(); ?>
</div><?php
			}
		}

		/**
		 * division 'SHARYOBU'
		 */
		if ( is_division( 'sharyobu' ) ) { ?>
<div class="col-sm-4"><?php
			/**
			 * icon
			 */
			$fa = get_post_meta( $post->ID, 'fontawesome4', true );
			if ( $fa ) {
				$icon = '<i class="fa fa-' . esc_attr( $fa ) . '"></i> ';
			} ?>
<header class="page-header">
<?php the_title( '<h3 class="page-title">' . $icon, '</h3>' ); ?>
</header>
<section class="page-desc">
<?php the_content(); ?>
</section><?php
			if ( have_rows( 'division_thumbnails' ) ) { ?>
<div class="wstd-gallery"><?php
				while ( have_rows( 'division_thumbnails' ) ) {
					the_row();
					$img_id = get_sub_field( 'images' );
					$alt = esc_attr( get_sub_field( 'label' ) );
					$img = wp_get_attachment_image( get_sub_field( 'images' ), 'medium', false, array( 'alt' => $alt, 'title' => $alt ) );
					echo $img;
				} ?>
</div><?php
			}
?>
<?php /* <a href="<?php the_permalink(); ?>" class="btn btn-default"><i class="fa fa-angle-double-right"></i> 詳細</a> */ ?>
</div><?php
		}
	} ?>
</section><?php
endif;
wp_reset_postdata();

add_action( 'wp_footer', function() { ?>
<script>
  (function($) {
    $(window, document).on('resize ready', function() {
      $('.page-desc').autoHeight();
      $('.page-title').autoHeight();
    })
  })(jQuery);
</script><?php
} );
get_footer();
