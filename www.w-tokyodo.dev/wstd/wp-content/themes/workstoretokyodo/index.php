<?php
/**
 * Workstore Tokyo Do Theme Root Template
 *
 * @since 0.0
 */
get_header(); ?>
<div class="row">
	<div class="col-md-9 col-sm-8">
<?php
wstd_breadcrumb();
/**
 * WordPress Loop
 */
if ( have_posts() ) :
	while ( have_posts() ) : the_post(); ?>
		<section <?php post_class(); ?>>
<?php
		if ( ! ( is_division() && is_page_top() ) ) {
			the_title( '<h2>', '</h2>' );
		}
		the_content(); ?>
		</section>
<?php
	endwhile;
endif; ?>
	</div>
	<?php
get_sidebar(); ?>
</div>
<?php
get_footer();
