<?php

/**
 * short code
 * 子ページを表示する
 */
add_shortcode( 'output_page_children', 'wstd_output_page_children' );
function wstd_output_page_children() {
	if ( !is_page() )
		return;
	global $post;
	$args = array(
		'post_parent' => $post->ID,
		'post_type' => 'page',
		'orderby' => 'menu_order',
		'order' => 'ASC'
	);
	$children = get_posts( $args );
	if ( !empty( $children ) ) :
		foreach ( $children as $post ) {
			setup_postdata( $post );
			/**
			 * activate <!--more-->
			 * @link http://codex.wordpress.org/Function_Reference/the_content
			global $more;
			$more = 0;
			 */
			if ( has_post_thumbnail() ) {
?>
<section <?php post_class( 'row' ); ?>>
<div class="col-lg-9 col-sm-8">
<header class="page-header">
<?php the_title( '<h3>', '</h3>' ); ?>
</header>
<?php the_content(); //the_excerpt(); ?>
</div>
<div class="child-page-image col-lg-3 col-sm-4">
<?php the_post_thumbnail( 'medium' ); ?>
</div>
</section>
<?php
			} else {
?>
<section <?php post_class(); ?>>
<header class="page-header">
<?php the_title( '<h3>', '</h3>' ); ?>
</header>
<?php the_content(); ?>
</section>
<?php
			}
		}
	endif;
	wp_reset_postdata();
}