<?php
/**
 * @since 0.0
 */
get_header();
do_action( 'wstd_contents_inner_wrapper_open' );
do_action( 'wstd_contents_top' );

/**
 * WordPress Loop
 */
if ( have_posts() ) :
	while ( have_posts() ) : the_post(); ?>
<section <?php post_class(); ?>>
	<?php
		get_template_part( 'loop' ); ?>
</section>
<?php
	endwhile;
endif;
do_action( 'wstd_contents_bottom' );
do_action( 'wstd_get_sidebar' );
do_action( 'wstd_contents_inner_wrapper_close' );
get_footer();
