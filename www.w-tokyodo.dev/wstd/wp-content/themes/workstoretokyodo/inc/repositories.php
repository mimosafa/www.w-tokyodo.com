<?php
/**
 * Post Types & Taxonomies
 *
 * @since 0.0
 */
add_action( 'init', 'wstd_repogitories' );
function wstd_repogitories() {
	/**
	 * Post Type: Event
	 */
	register_post_type(
		'event',
		[
			'labels' => [ 'name' => 'イベント実績' ],
			'public' => true,
			'has_archive'   => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-smiley',
			'taxonomies'    => [ 'works' ],
			'register_meta_box_cb' => 'event_meta_box',
		]
	);

	/**
	 * Post Type: Works
	 */
	register_taxonomy(
		'works',
		[ 'event', 'attachment' ],
		[
			'labels' => [ 'name' => '業務種別' ],
			'public' => true
		]
	);
	register_taxonomy_for_object_type( 'works', 'attachment' );

	/**
	 * Support Except for Page
	 */
	add_post_type_support( 'page', 'excerpt' );
}

/**
 * Post Type: Event
 */
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
