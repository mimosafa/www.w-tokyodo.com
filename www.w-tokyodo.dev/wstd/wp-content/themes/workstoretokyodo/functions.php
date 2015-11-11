<?php
/**
 * WordPress Theme Functions File for Workstore Tokyo Do Web Site
 *
 * @since 0.0
 *
 * @package WordPress
 *
 * @author Toshimichi Mimoto <mimosafa@gmail.com>
 */

/**
 * Include Files
 *
 * @since 0.1
 */
require_once 'inc/redirects.php';
require_once 'inc/setup.php';
require_once 'inc/repositories.php';
require_once 'inc/googleanalytics.php';
require_once 'inc/workstoretokyodo.php';
require_once 'inc/shortcodes.php';
require_once 'inc/views.php';

/**
 * Deprecated
 */
require_once 'functions/company-attribute.php';
require_once 'functions/elements.php';

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
