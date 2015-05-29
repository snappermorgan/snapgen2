<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Slider
 * Description: Display slider content
 */

///////////////////////////////////////
// Load Post Type
///////////////////////////////////////
add_action( 'init', 'themify_builder_slider_loaded' );
add_filter( 'post_updated_messages', 'themify_builder_slider_updated_messages' );

function themify_builder_slider_loaded() {
	global $ThemifyBuilder;

	if ( post_type_exists( 'slider' ) ) {
		// check taxonomy register
		if ( ! taxonomy_exists( 'slider-category' ) ) 
			themify_builder_slider_register_taxonomy();
	} else {
		themify_builder_slider_register_cpt();
		themify_builder_slider_register_taxonomy();
		add_filter( 'themify_do_metaboxes', 'themify_builder_slider_meta_boxes' );
		
		// push to themify builder class
		$ThemifyBuilder->push_post_types( 'slider' );
	}
}

/**
 * Customize post type updated messages.
 * @param $messages
 * @return mixed
 */
function themify_builder_slider_updated_messages( $messages ) {
	global $post;
	$messages['slider'] = array(
		0 => '',
		1 => __( 'Slide updated.', 'themify' ),
		2 => __( 'Custom field updated.', 'themify' ),
		3 => __( 'Custom field deleted.', 'themify' ),
		4 => __( 'Slide updated.', 'themify' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Slide restored to revision from %s', 'themify' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => __( 'Slide published.', 'themify' ),
		7 => __( 'Slide saved.', 'themify' ),
		8 => __( 'Slide submitted.', 'themify' ),
		9 => sprintf( __( 'Slide scheduled for: <strong>%s</strong>.', 'themify' ),
			date_i18n( __( 'M j, Y @ G:i', 'themify' ), strtotime( $post->post_date ) ) ),
		10 => __( 'Slide draft updated.', 'themify' ),
	);
	return $messages;
}

///////////////////////////////////////
// Register Post Type
///////////////////////////////////////
function themify_builder_slider_register_cpt( $cpt = array() ) {
	$cpt = array(
		'plural' => __('Sliders', 'themify'),
		'singular' => __('Slider', 'themify'),
		'supports' => array('title', 'editor', 'author', 'custom-fields')
	);

	register_post_type( 'slider', array(
		'labels' => array(
			'name' => $cpt['plural'],
			'singular_name' => $cpt['singular'],
			'add_new' => __( 'Add New', 'themify' ),
			'add_new_item' => sprintf(__( 'Add New %s', 'themify' ), $cpt['singular']),
			'edit_item' => sprintf(__( 'Edit %s', 'themify' ), $cpt['singular']),
			'new_item' => sprintf(__( 'New %s', 'themify' ), $cpt['singular']),
			'view_item' => sprintf(__( 'View %s', 'themify' ), $cpt['singular']),
			'search_items' => sprintf(__( 'Search %s', 'themify' ), $cpt['plural']),
			'not_found' => sprintf(__( 'No %s found', 'themify' ), $cpt['plural']),
			'not_found_in_trash' => sprintf(__( 'No %s found in Trash', 'themify' ), $cpt['plural']),
			'menu_name' => $cpt['plural']
		),
		'supports' => isset($cpt['supports'])? $cpt['supports'] : array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
		//'menu_position' => $position++,
		'hierarchical' => false,
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'show_in_nav_menus' => false,
		'publicly_queryable' => true,
		'rewrite' => array( 'slug' => isset($cpt['rewrite'])? $cpt['rewrite']: strtolower($cpt['singular']) ),
		'query_var' => true,
		'can_export' => true,
		'capability_type' => 'post',
		'menu_icon' => 'dashicons-slides'
	));
}

///////////////////////////////////////
// Register Taxonomy
///////////////////////////////////////
function themify_builder_slider_register_taxonomy( $cpt = array() ) {
	global $ThemifyBuilder;

	$cpt = array(
		'plural' => __('Sliders', 'themify'),
		'singular' => __('Slider', 'themify'),
		'supports' => array('title', 'editor', 'author', 'custom-fields')
	);

	register_taxonomy( 'slider-category', array('slider'), array(
		'labels' => array(
			'name' => sprintf(__( '%s Categories', 'themify' ), $cpt['singular']),
			'singular_name' => sprintf(__( '%s Category', 'themify' ), $cpt['singular']),
			'search_items' => sprintf(__( 'Search %s Categories', 'themify' ), $cpt['singular']),
			'popular_items' => sprintf(__( 'Popular %s Categories', 'themify' ), $cpt['singular']),
			'all_items' => sprintf(__( 'All Categories', 'themify' ), $cpt['singular']),
			'parent_item' => sprintf(__( 'Parent %s Category', 'themify' ), $cpt['singular']),
			'parent_item_colon' => sprintf(__( 'Parent %s Category:', 'themify' ), $cpt['singular']),
			'edit_item' => sprintf(__( 'Edit %s Category', 'themify' ), $cpt['singular']),
			'update_item' => sprintf(__( 'Update %s Category', 'themify' ), $cpt['singular']),
			'add_new_item' => sprintf(__( 'Add New %s Category', 'themify' ), $cpt['singular']),
			'new_item_name' => sprintf(__( 'New %s Category', 'themify' ), $cpt['singular']),
			'separate_items_with_commas' => sprintf(__( 'Separate %s Category with commas', 'themify' ), $cpt['singular']),
			'add_or_remove_items' => sprintf(__( 'Add or remove %s Category', 'themify' ), $cpt['singular']),
			'choose_from_most_used' => sprintf(__( 'Choose from the most used %s Category', 'themify' ), $cpt['singular']),
			'menu_name' => sprintf(__( '%s Category', 'themify' ), $cpt['singular']),
		),
		'public' => true,
		'show_in_nav_menus' => false,
		'show_ui' => true,
		'show_admin_column' => true,
		'show_tagcloud' => true,
		'hierarchical' => true,
		'rewrite' => true,
		'query_var' => true
	));

	add_filter( 'manage_edit-slider-category_columns', array($ThemifyBuilder, 'taxonomy_header'), 10, 2 );
	add_filter( 'manage_slider-category_custom_column', array($ThemifyBuilder, 'taxonomy_column_id'), 10, 3 );

	// admin column custom taxonomy
	add_filter( 'manage_taxonomies_for_slider_columns', 'themify_builder_slider_category_columns' );
	function themify_builder_slider_category_columns( $taxonomies ) {
		$taxonomies[] = 'slider-category';
		return $taxonomies;
	}
}

///////////////////////////////////////
// Register Metaboxes
///////////////////////////////////////
function themify_builder_slider_meta_boxes( $meta_boxes ) {
	global $ThemifyBuilder;

	/** Slider Meta Box Options */
	$slider_meta_box = array(
		// Feature Image
		$ThemifyBuilder->post_image,
		// Featured Image Size
		$ThemifyBuilder->featured_image_size,
		// Image Width
		$ThemifyBuilder->image_width,
		// Image Height
		$ThemifyBuilder->image_height,
		// External Link
		$ThemifyBuilder->external_link,
		// Lightbox Link
		$ThemifyBuilder->lightbox_link,
		array(
			'name' 		=> 'video_url',
			'title' 	=> __('Video URL', 'themify'),
			'description' => __('URL to embed a video instead of featured image', 'themify'),
			'type' 		=> 'textbox',
			'meta'		=> array()
		)
	);

	return array_merge($meta_boxes, array(
		array(
			'name'		=> __('Slider Options', 'themify'),
			'id' 		=> 'slider-options',	
			'options'	=> $slider_meta_box,
			'pages'	=> 'slider'
		),
	));
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
$visible_opt = array(1 => 1, 2, 3, 4, 5, 6, 7);
$auto_scroll_opt = array('off', '1 sec', '2 sec', '3 sec', '4 sec', '5 sec', '6 sec', '7 sec', '8 sec', '9 sec', '10 sec');

$image_sizes = themify_get_image_sizes_list( false );

$this->modules['slider'] = apply_filters( 'themify_builder_module_slider', array(
	'name' => __('Slider', 'themify'),
	'options' => array(
		array(
			'id' => 'mod_title_slider',
			'type' => 'text',
			'label' => __('Module Title', 'themify'),
			'class' => 'large'
		),
		array(
			'id' => 'layout_display_slider',
			'type' => 'radio',
			'label' => __('Display', 'themify'),
			'options' => array(
				'blog' => __('Blog Posts', 'themify'),
				'slider' => __('Slider Posts', 'themify'),
				'portfolio' => __('Portfolio', 'themify'),
				'testimonial' => __('Testimonial', 'themify'),
				'image' => __('Images', 'themify'),
				'video' => __('Videos', 'themify'),
				'text' => __('Text', 'themify')
			),
			'default' => 'blog',
			'option_js' => true
		),
		///////////////////////////////////////////
		// Blog post option
		///////////////////////////////////////////
		array(
			'id' => 'blog_category_slider',
			'type' => 'query_category',
			'label' => __('Category', 'themify'),
			'options' => array(),
			'help' => sprintf(__('Add more <a href="%s" target="_blank">blog posts</a>', 'themify'), admin_url('post-new.php')),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog'
		),
		array(
			'id' => 'slider_category_slider',
			'type' => 'query_category',
			'label' => __('Category', 'themify'),
			'options' => array(
				'taxonomy' => 'slider-category'
			),
			'help' => sprintf(__('Add more <a href="%s" target="_blank">slider posts</a>', 'themify'), admin_url('post-new.php?post_type=slider')),
			'wrap_with_class' => 'tf-group-element tf-group-element-slider'
		),
		array(
			'id' => 'portfolio_category_slider',
			'type' => 'query_category',
			'label' => __('Category', 'themify'),
			'options' => array(
				'taxonomy' => 'portfolio-category'
			),
			'help' => sprintf(__('Add more <a href="%s" target="_blank">portfolio posts</a>', 'themify'), admin_url('post-new.php?post_type=portfolio')),
			'wrap_with_class' => 'tf-group-element tf-group-element-portfolio'
		),
		array(
			'id' => 'testimonial_category_slider',
			'type' => 'query_category',
			'label' => __('Category', 'themify'),
			'options' => array(
				'taxonomy' => 'testimonial-category'
			),
			'help' => sprintf(__('Add more <a href="%s" target="_blank">testimonial posts</a>', 'themify'), admin_url('post-new.php?post_type=testimonial')),
			'wrap_with_class' => 'tf-group-element tf-group-element-testimonial'
		),
		array(
			'id' => 'posts_per_page_slider',
			'type' => 'text',
			'label' => __('Query', 'themify'),
			'class' => 'xsmall',
			'help' => __('number of posts to query', 'themify'),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-portfolio tf-group-element-slider tf-group-element-testimonial'
		),
		array(
			'id' => 'offset_slider',
			'type' => 'text',
			'label' => __('Offset', 'themify'),
			'class' => 'xsmall',
			'help' => __('number of post to displace or pass over', 'themify'),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-portfolio tf-group-element-slider tf-group-element-testimonial'
		),
		array(
			'id' => 'display_slider',
			'type' => 'select',
			'label' => __('Display', 'themify'),
			'options' => array(
				'content' => __('Content', 'themify'),
				'excerpt' => __('Excerpt', 'themify'),
				'none' => __('None', 'themify')
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio tf-group-element-testimonial'
		),
		array(
			'id' => 'hide_post_title_slider',
			'type' => 'select',
			'label' => __('Hide Post Title', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio tf-group-element-testimonial'
		),
		array(
			'id' => 'unlink_post_title_slider',
			'type' => 'select',
			'label' => __('Unlink Post Title', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio'
		),
		array(
			'id' => 'hide_feat_img_slider',
			'type' => 'select',
			'label' => __('Hide Featured Image', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio tf-group-element-testimonial'
		),
		array(
			'id' => 'unlink_feat_img_slider',
			'type' => 'select',
			'label' => __('Unlink Featured Image', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio'
		),

		///////////////////////////////////////////
		// Image post option
		///////////////////////////////////////////
		array(
			'id' => 'img_content_slider',
			'type' => 'builder',
			'options' => array(
				array(
					'id' => 'img_url_slider',
					'type' => 'image',
					'label' => __('Image URL', 'themify'),
					'class' => 'xlarge'
				),
				array(
					'id' => 'img_title_slider',
					'type' => 'text',
					'label' => __('Image Title', 'themify'),
					'class' => 'fullwidth'
				),
				array(
					'id' => 'img_link_slider',
					'type' => 'text',
					'label' => __('Image Link', 'themify'),
					'class' => 'fullwidth'
				),
				array(
					'id' => 'img_caption_slider',
					'type' => 'textarea',
					'label' => __('Image Caption', 'themify'),
					'class' => 'fullwidth',
					'rows' => 6
				)
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-image'
		),

		///////////////////////////////////////////
		// Video post option
		///////////////////////////////////////////
		array(
			'id' => 'video_content_slider',
			'type' => 'builder',
			'options' => array(
				array(
					'id' => 'video_url_slider',
					'type' => 'text',
					'label' => __('Video URL', 'themify'),
					'class' => 'xlarge',
					'help' => array(
						'new_line' => true,
						'text' => __('YouTube, Vimeo, etc', 'themify')
					)
				),
				array(
					'id' => 'video_title_slider',
					'type' => 'text',
					'label' => __('Video Title', 'themify'),
					'class' => 'fullwidth'
				),
				array(
					'id' => 'video_title_link_slider',
					'type' => 'text',
					'label' => __('Video Title Link', 'themify'),
					'class' => 'fullwidth'
				),
				array(
					'id' => 'video_caption_slider',
					'type' => 'textarea',
					'label' => __('Video Caption', 'themify'),
					'class' => 'fullwidth',
					'rows' => 6
				),
				array(
					'id' => 'video_width_slider',
					'type' => 'text',
					'label' => __('Video Width', 'themify'),
					'class' => 'xsmall'
				)
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-video'
		),

		///////////////////////////////////////////
		// Text Slider option
		///////////////////////////////////////////
		array(
			'id' => 'text_content_slider',
			'type' => 'builder',
			'options' => array(
				array(
					'id' => 'text_caption_slider',
					'type' => 'wp_editor',
					'label' => false,
					'class' => 'fullwidth builder-field',
					'rows' => 6
				)
			),
			'wrap_with_class' => 'tf-group-element tf-group-element-text'
		),

		array(
			'id' => 'layout_slider',
			'type' => 'layout',
			'label' => __('Slider Layout', 'themify'),
			'separated' => 'top',
			'options' => array(
				array('img' => 'slider-default.png', 'value' => 'slider-default', 'label' => __('Slider Default', 'themify')),
				array('img' => 'slider-image-top.png', 'value' => 'slider-overlay', 'label' => __('Slider Overlay', 'themify')),
				array('img' => 'slider-caption-overlay.png', 'value' => 'slider-caption-overlay', 'label' => __('Slider Caption Overlay', 'themify'))
			)
		),
		array(
			'id' => 'image_size_slider',
			'type' => 'select',
			'label' => $this->is_img_php_disabled() ? __('Image Size', 'themify') : false,
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'hide' => $this->is_img_php_disabled() ? false : true,
			'options' => $image_sizes,
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio tf-group-element-image'
		),
		array(
			'id' => 'img_w_slider',
			'type' => 'text',
			'label' => __('Image Width', 'themify'),
			'class' => 'xsmall',
			'help' => 'px',
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio tf-group-element-image'
		),
		array(
			'id' => 'img_h_slider',
			'type' => 'text',
			'label' => __('Image Height', 'themify'),
			'class' => 'xsmall',
			'help' => 'px',
			'wrap_with_class' => 'tf-group-element tf-group-element-blog tf-group-element-slider tf-group-element-portfolio tf-group-element-image'
		),

		array(
			'id' => 'slider_option_slider',
			'type' => 'slider',
			'label' => __('Slider Options', 'themify'),
			'options' => array(
				array(
					'id' => 'visible_opt_slider',
					'type' => 'select',
					'default' => 1,
					'options' => $visible_opt,
					'help' => __('Visible', 'themify')
				),
				array(
					'id' => 'auto_scroll_opt_slider',
					'type' => 'select',
					'default' => 4,
					'options' => $auto_scroll_opt,
					'help' => __('Auto Scroll', 'themify')
				),
				array(
					'id' => 'scroll_opt_slider',
					'type' => 'select',
					'options' => $visible_opt,
					'help' => __('Scroll', 'themify')
				),
				array(
					'id' => 'speed_opt_slider',
					'type' => 'select',
					'options' => array(
						'normal' => __('Normal', 'themify'),
						'fast' => __('Fast', 'themify'),
						'slow' => __('Slow', 'themify')
					),
					'help' => __('Speed', 'themify')
				),
				array(
					'id' => 'effect_slider',
					'type' => 'select',
					'options' => array(
						'scroll' => __('Slide', 'themify'),
						'fade' => __('Fade', 'themify'),
						'continuously' => __('Continuously', 'themify')
					),
					'help' => __('Effect', 'themify')
				),
				array(
					'id' => 'pause_on_hover_slider',
					'type' => 'select',
					'options' => array(
						'resume' => __('Resume', 'themify'),
						'immediate' => __('Immediate', 'themify'),
						'false' => __('Disable', 'themify')
					),
					'help' => __('Pause On Hover', 'themify')
				),
				array(
					'id' => 'wrap_slider',
					'type' => 'select',
					'help' => __('Wrap', 'themify'),
					'options' => array(
						'yes' => __('Yes', 'themify'),
						'no' => __('No', 'themify')
					)
				),
				array(
					'id' => 'show_nav_slider',
					'type' => 'select',
					'help' => __('Show slider pagination', 'themify'),
					'options' => array(
						'yes' => __('Yes', 'themify'),
						'no' => __('No', 'themify')
					)
				),
				array(
					'id' => 'show_arrow_slider',
					'type' => 'select',
					'help' => __('Show slider arrow buttons', 'themify'),
					'options' => array(
						'yes' => __('Yes', 'themify'),
						'no' => __('No', 'themify')
					)
				),
				array(
					'id' => 'left_margin_slider',
					'type' => 'text',
					'class' => 'xsmall',
					'unit' => 'px',
					'help' => __('Left margin space between slides', 'themify')
				),
				array(
					'id' => 'right_margin_slider',
					'type' => 'text',
					'class' => 'xsmall',
					'unit' => 'px',
					'help' => __('Right margin space between slides', 'themify')
				)
			)
		)
	),
	// Styling
	'styling' => array(
		array(
			'id' => 'separator_image_background',
			'title' => '',
			'description' => '',
			'type' => 'separator',
			'meta' => array('html'=>'<h4>'.__('Background', 'themify').'</h4>'),
		),
		array(
			'id' => 'background_color',
			'type' => 'color',
			'label' => __('Background Color', 'themify'),
			'class' => 'small'
		),
		// Font
		array(
			'type' => 'separator',
			'meta' => array('html'=>'<hr />')
		),
		array(
			'id' => 'separator_font',
			'type' => 'separator',
			'meta' => array('html'=>'<h4>'.__('Font', 'themify').'</h4>'),
		),
		array(
			'id' => 'font_family',
			'type' => 'select',
			'label' => __('Font Family', 'themify'),
			'class' => 'font-family-select',
			'meta' => array_merge( themify_get_web_safe_font_list(), themify_get_google_web_fonts_list() )
		),
		array(
			'id' => 'font_color',
			'type' => 'color',
			'label' => __('Font Color', 'themify'),
			'class' => 'small'
		),
		array(
			'id' => 'multi_font_size',
			'type' => 'multi',
			'label' => __('Font Size', 'themify'),
			'fields' => array(
				array(
					'id' => 'font_size',
					'type' => 'text',
					'class' => 'xsmall'
				),
				array(
					'id' => 'font_size_unit',
					'type' => 'select',
					'meta' => array(
						array('value' => '', 'name' => ''),
						array('value' => 'px', 'name' => __('px', 'themify')),
						array('value' => 'em', 'name' => __('em', 'themify'))
					)
				)
			)
		),
		array(
			'id' => 'multi_line_height',
			'type' => 'multi',
			'label' => __('Line Height', 'themify'),
			'fields' => array(
				array(
					'id' => 'line_height',
					'type' => 'text',
					'class' => 'xsmall'
				),
				array(
					'id' => 'line_height_unit',
					'type' => 'select',
					'meta' => array(
						array('value' => '', 'name' => ''),
						array('value' => 'px', 'name' => __('px', 'themify')),
						array('value' => 'em', 'name' => __('em', 'themify')),
						array('value' => '%', 'name' => __('%', 'themify'))
					)
				)
			)
		),
		// Link
		array(
			'type' => 'separator',
			'meta' => array('html'=>'<hr />')
		),
		array(
			'id' => 'separator_link',
			'type' => 'separator',
			'meta' => array('html'=>'<h4>'.__('Link', 'themify').'</h4>'),
		),
		array(
			'id' => 'link_color',
			'type' => 'color',
			'label' => __('Color', 'themify'),
			'class' => 'small'
		),
		array(
			'id' => 'text_decoration',
			'type' => 'select',
			'label' => __( 'Text Decoration', 'themify' ),
			'meta'	=> array(
				array('value' => '',   'name' => '', 'selected' => true),
				array('value' => 'underline',   'name' => __('Underline', 'themify')),
				array('value' => 'overline', 'name' => __('Overline', 'themify')),
				array('value' => 'line-through',  'name' => __('Line through', 'themify')),
				array('value' => 'none',  'name' => __('None', 'themify'))
			)
		),
		// Padding
		array(
			'type' => 'separator',
			'meta' => array('html'=>'<hr />')
		),
		array(
			'id' => 'separator_padding',
			'type' => 'separator',
			'meta' => array('html'=>'<h4>'.__('Padding', 'themify').'</h4>'),
		),
		array(
			'id' => 'multi_padding',
			'type' => 'multi',
			'label' => __('Padding', 'themify'),
			'fields' => array(
				array(
					'id' => 'padding_top',
					'type' => 'text',
					'description' => __('top', 'themify'),
					'class' => 'xsmall'
				),
				array(
					'id' => 'padding_right',
					'type' => 'text',
					'description' => __('right', 'themify'),
					'class' => 'xsmall'
				),
				array(
					'id' => 'padding_bottom',
					'type' => 'text',
					'description' => __('bottom', 'themify'),
					'class' => 'xsmall'
				),
				array(
					'id' => 'padding_left',
					'type' => 'text',
					'description' => __('left (px)', 'themify'),
					'class' => 'xsmall'
				)
			)
		),
		// Margin
		array(
			'type' => 'separator',
			'meta' => array('html'=>'<hr />')
		),
		array(
			'id' => 'separator_margin',
			'type' => 'separator',
			'meta' => array('html'=>'<h4>'.__('Margin', 'themify').'</h4>'),
		),
		array(
			'id' => 'multi_margin',
			'type' => 'multi',
			'label' => __('Margin', 'themify'),
			'fields' => array(
				array(
					'id' => 'margin_top',
					'type' => 'text',
					'description' => __('top', 'themify'),
					'class' => 'xsmall'
				),
				array(
					'id' => 'margin_right',
					'type' => 'text',
					'description' => __('right', 'themify'),
					'class' => 'xsmall'
				),
				array(
					'id' => 'margin_bottom',
					'type' => 'text',
					'description' => __('bottom', 'themify'),
					'class' => 'xsmall'
				),
				array(
					'id' => 'margin_left',
					'type' => 'text',
					'description' => __('left (px)', 'themify'),
					'class' => 'xsmall'
				)
			)
		),
		// Border
		array(
			'type' => 'separator',
			'meta' => array('html'=>'<hr />')
		),
		array(
			'id' => 'separator_border',
			'type' => 'separator',
			'meta' => array('html'=>'<h4>'.__('Border', 'themify').'</h4>'),
		),
		array(
			'id' => 'multi_border_top',
			'type' => 'multi',
			'label' => __('Border', 'themify'),
			'fields' => array(
				array(
					'id' => 'border_top_color',
					'type' => 'color',
					'class' => 'small'
				),
				array(
					'id' => 'border_top_width',
					'type' => 'text',
					'description' => 'px',
					'class' => 'xsmall'
				),
				array(
					'id' => 'border_top_style',
					'type' => 'select',
					'description' => __('top', 'themify'),
					'meta' => array(
						array( 'value' => '', 'name' => '' ),
						array( 'value' => 'solid', 'name' => __( 'Solid', 'themify' ) ),
						array( 'value' => 'dashed', 'name' => __( 'Dashed', 'themify' ) ),
						array( 'value' => 'dotted', 'name' => __( 'Dotted', 'themify' ) ),
						array( 'value' => 'double', 'name' => __( 'Double', 'themify' ) )
					)
				)
			)
		),
		array(
			'id' => 'multi_border_right',
			'type' => 'multi',
			'label' => '',
			'fields' => array(
				array(
					'id' => 'border_right_color',
					'type' => 'color',
					'class' => 'small'
				),
				array(
					'id' => 'border_right_width',
					'type' => 'text',
					'description' => 'px',
					'class' => 'xsmall'
				),
				array(
					'id' => 'border_right_style',
					'type' => 'select',
					'description' => __('right', 'themify'),
					'meta' => array(
						array( 'value' => '', 'name' => '' ),
						array( 'value' => 'solid', 'name' => __( 'Solid', 'themify' ) ),
						array( 'value' => 'dashed', 'name' => __( 'Dashed', 'themify' ) ),
						array( 'value' => 'dotted', 'name' => __( 'Dotted', 'themify' ) ),
						array( 'value' => 'double', 'name' => __( 'Double', 'themify' ) )
					)
				)
			)
		),
		array(
			'id' => 'multi_border_bottom',
			'type' => 'multi',
			'label' => '',
			'fields' => array(
				array(
					'id' => 'border_bottom_color',
					'type' => 'color',
					'class' => 'small'
				),
				array(
					'id' => 'border_bottom_width',
					'type' => 'text',
					'description' => 'px',
					'class' => 'xsmall'
				),
				array(
					'id' => 'border_bottom_style',
					'type' => 'select',
					'description' => __('bottom', 'themify'),
					'meta' => array(
						array( 'value' => '', 'name' => '' ),
						array( 'value' => 'solid', 'name' => __( 'Solid', 'themify' ) ),
						array( 'value' => 'dashed', 'name' => __( 'Dashed', 'themify' ) ),
						array( 'value' => 'dotted', 'name' => __( 'Dotted', 'themify' ) ),
						array( 'value' => 'double', 'name' => __( 'Double', 'themify' ) )
					)
				)
			)
		),
		array(
			'id' => 'multi_border_left',
			'type' => 'multi',
			'label' => '',
			'fields' => array(
				array(
					'id' => 'border_left_color',
					'type' => 'color',
					'class' => 'small'
				),
				array(
					'id' => 'border_left_width',
					'type' => 'text',
					'description' => 'px',
					'class' => 'xsmall'
				),
				array(
					'id' => 'border_left_style',
					'type' => 'select',
					'description' => __('left', 'themify'),
					'meta' => array(
						array( 'value' => '', 'name' => '' ),
						array( 'value' => 'solid', 'name' => __( 'Solid', 'themify' ) ),
						array( 'value' => 'dashed', 'name' => __( 'Dashed', 'themify' ) ),
						array( 'value' => 'dotted', 'name' => __( 'Dotted', 'themify' ) ),
						array( 'value' => 'double', 'name' => __( 'Double', 'themify' ) )
					)
				)
			)
		),
		// Additional CSS
		array(
			'type' => 'separator',
			'meta' => array( 'html' => '<hr/>')
		),
		array(
			'id' => 'css_slider',
			'type' => 'text',
			'label' => __('Additional CSS Class', 'themify'),
			'class' => 'large exclude-from-reset-field',
			'description' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') )
		)
	),
	'styling_selector' => array(
		'.module-slider' => array(
			'background_color', 'padding', 'margin', 'border_top', 'border_right', 'border_bottom', 'border_left'
		),
		'.module-slider .slide-content' => array(
			 'font_family', 'font_size', 'line_height', 'color'
		),
		'.module-slider a' => array( 'link_color', 'text_decoration' ),
		'.module-slider .slide-content .slide-title' => array(
			 'font_family', 'color'
		),
		'.module-slider .slide-content .slide-title a' => array(
			 'font_family', 'color'
		)
	)
) );

?>