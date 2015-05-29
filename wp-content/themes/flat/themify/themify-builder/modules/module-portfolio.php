<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Portfolio
 * Description: Display portfolio custom post type
 */

///////////////////////////////////////
// Load Post Type
///////////////////////////////////////
add_action( 'init', 'themify_builder_portfolio_loaded' );
add_filter( 'post_updated_messages', 'themify_builder_portfolio_updated_messages' );

function themify_builder_portfolio_loaded() {
	global $ThemifyBuilder;
	
	if ( post_type_exists( 'portfolio' ) ) {
		// check taxonomy register
		if ( ! taxonomy_exists( 'portfolio-category' ) ) 
			themify_builder_portfolio_register_taxonomy();
	} else {
		themify_builder_portfolio_register_cpt();
		themify_builder_portfolio_register_taxonomy();
		add_filter( 'themify_do_metaboxes', 'themify_builder_portfolio_meta_boxes' );
		
		// push to themify builder class
		$ThemifyBuilder->push_post_types( 'portfolio' );
	}
}

/**
 * Customize post type updated messages.
 * @param $messages
 * @return mixed
 */
function themify_builder_portfolio_updated_messages( $messages ) {
	global $post, $post_ID;
	$view = esc_url( get_permalink( $post_ID ) );
	$preview = esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) );
	$messages['portfolio'] = array(
		0 => '',
		1 => sprintf( __( 'Portfolio updated. <a href="%s">View portfolio</a>.', 'themify' ), $view ),
		2 => __( 'Custom field updated.', 'themify' ),
		3 => __( 'Custom field deleted.', 'themify' ),
		4 => __( 'Portfolio updated.', 'themify' ),
		5 => isset( $_GET['revision'] ) ? sprintf( __( 'Portfolio restored to revision from %s', 'themify' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
		6 => sprintf( __( 'Portfolio published. <a href="%s">View portfolio</a>.', 'themify' ), $view ),
		7 => __( 'Portfolio saved.', 'themify' ),
		8 => sprintf( __( 'Portfolio submitted. <a href="%s">Preview portfolio</a>.', 'themify' ), $preview ),
		9 => sprintf( __( 'Portfolio scheduled for: <strong>%s</strong>.', 'themify' ),
			date_i18n( __( 'M j, Y @ G:i', 'themify' ), strtotime( $post->post_date ) ) ),
		10 => sprintf( __( 'Portfolio draft updated. <a href="%s">Preview portfolio</a>.', 'themify' ), $preview ),
	);
	return $messages;
}

///////////////////////////////////////
// Register Post Type
///////////////////////////////////////
function themify_builder_portfolio_register_cpt( $cpt = array() ) {
	$cpt = array(
		'plural' => __('Portfolios', 'themify'),
		'singular' => __('Portfolio', 'themify'),
		'rewrite' => apply_filters('themify_portfolio_rewrite', 'project')
	);

	register_post_type( 'portfolio', array(
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
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'rewrite' => array( 'slug' => isset($cpt['rewrite'])? $cpt['rewrite']: strtolower($cpt['singular']) ),
		'query_var' => true,
		'can_export' => true,
		'capability_type' => 'post',
		'menu_icon' => 'dashicons-portfolio'
	));
}

///////////////////////////////////////
// Register Taxonomy
///////////////////////////////////////
function themify_builder_portfolio_register_taxonomy( $cpt = array() ) {
	global $ThemifyBuilder;

	$cpt = array(
		'plural' => __('Portfolios', 'themify'),
		'singular' => __('Portfolio', 'themify'),
		'rewrite' => apply_filters('themify_portfolio_rewrite', 'project')
	);

	register_taxonomy( 'portfolio-category', array('portfolio'), array(
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
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'show_admin_column' => true,
		'show_tagcloud' => true,
		'hierarchical' => true,
		'rewrite' => true,
		'query_var' => true
	));

	add_filter( 'manage_edit-portfolio-category_columns', array($ThemifyBuilder, 'taxonomy_header'), 10, 2 );
	add_filter( 'manage_portfolio-category_custom_column', array($ThemifyBuilder, 'taxonomy_column_id'), 10, 3 );

	// admin column custom taxonomy
	add_filter( 'manage_taxonomies_for_portfolio_columns', 'themify_builder_portfolio_category_columns' );
	function themify_builder_portfolio_category_columns( $taxonomies ) {
		$taxonomies[] = 'portfolio-category';
		return $taxonomies;
	}
}

///////////////////////////////////////
// Register Metaboxes
///////////////////////////////////////
function themify_builder_portfolio_meta_boxes( $meta_boxes ) {
	global $ThemifyBuilder;

	/** Portfolio Meta Box Options */
	$portfolio_meta_box = array(
		// Feature Image
		$ThemifyBuilder->post_image,
		// Featured Image Size
		$ThemifyBuilder->featured_image_size,
		// Image Width
		$ThemifyBuilder->image_width,
		// Image Height
		$ThemifyBuilder->image_height,
		// Hide Title
		array(
			"name" 		=> "hide_post_title",
			"title"		=> __('Hide Post Title', 'themify'),
			"description"	=> "",
			"type" 		=> "dropdown",			
			"meta"		=> array(
				array("value" => "default", "name" => "", "selected" => true),
				array("value" => "yes", 'name' => __('Yes', 'themify')),
				array("value" => "no",	'name' => __('No', 'themify'))
			)
		),
		// Unlink Post Title
		array(
			"name" 		=> "unlink_post_title",
			"title" 		=> __('Unlink Post Title', 'themify'), 	
			"description" => __('Unlink post title (it will display the post title without link)', 'themify'), 				
			"type" 		=> "dropdown",			
			"meta"		=> array(
				array("value" => "default", "name" => "", "selected" => true),
				array("value" => "yes", 'name' => __('Yes', 'themify')),
				array("value" => "no",	'name' => __('No', 'themify'))
			)
		),
		// Hide Post Date
		array(
			"name" 		=> "hide_post_date",
			"title"		=> __('Hide Post Date', 'themify'),
			"description"	=> "",
			"type" 		=> "dropdown",			
			"meta"		=> array(
				array("value" => "default", "name" => "", "selected" => true),
				array("value" => "yes", 'name' => __('Yes', 'themify')),
				array("value" => "no",	'name' => __('No', 'themify'))
			)
		),
		// Hide Post Meta
		array(
			"name" 		=> "hide_post_meta",
			"title"		=> __('Hide Post Meta', 'themify'),
			"description"	=> "",
			"type" 		=> "dropdown",			
			"meta"		=> array(
				array("value" => "default", "name" => "", "selected" => true),
				array("value" => "yes", 'name' => __('Yes', 'themify')),
				array("value" => "no",	'name' => __('No', 'themify'))
			)
		),
		// Hide Post Image
		array(
			"name" 		=> "hide_post_image",
			"title" 		=> __('Hide Featured Image', 'themify'), 	
			"description" => "", 				
			"type" 		=> "dropdown",			
			"meta"		=> array(
				array("value" => "default", "name" => "", "selected" => true),
				array("value" => "yes", 'name' => __('Yes', 'themify')),
				array("value" => "no",	'name' => __('No', 'themify'))
			)			
		),
		// Unlink Post Image
		array(
			"name" 		=> "unlink_post_image",
			"title" 		=> __('Unlink Featured Image', 'themify'), 	
			"description" => __('Display the Featured Image without link)', 'themify'), 				
			"type" 		=> "dropdown",			
			"meta"		=> array(
				array("value" => "default", "name" => "", "selected" => true),
				array("value" => "yes", 'name' => __('Yes', 'themify')),
				array("value" => "no",	'name' => __('No', 'themify'))
			)
		),
		// External Link
		$ThemifyBuilder->external_link,
		// Lightbox Link
		$ThemifyBuilder->lightbox_link
	);

	return array_merge($meta_boxes, array(
		array(
			'name'	=> __('Portfolio Options', 'themify'),
			'id' 		=> 'portfolio-options',
			'options' => $portfolio_meta_box,
			'pages'	=> 'portfolio'
		)
	));
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
$image_sizes = themify_get_image_sizes_list( false );

$this->modules['portfolio'] = apply_filters( 'themify_builder_module_portfolio', array(
	'name' => __('Portfolio', 'themify'),
	'options' => array(
		array(
			'id' => 'mod_title_portfolio',
			'type' => 'text',
			'label' => __('Module Title', 'themify'),
			'class' => 'large'
		),
		array(
			'id' => 'layout_portfolio',
			'type' => 'layout',
			'label' => __('Portfolio Layout', 'themify'),
			'options' => array(
				array('img' => 'grid4.png', 'value' => 'grid4', 'label' => __('Grid 4', 'themify')),
				array('img' => 'grid3.png', 'value' => 'grid3', 'label' => __('Grid 3', 'themify')),
				array('img' => 'grid2.png', 'value' => 'grid2', 'label' => __('Grid 2', 'themify')),
				array('img' => 'fullwidth.png', 'value' => 'fullwidth', 'label' => __('fullwidth', 'themify'))
			)
		),
		array(
			'id' => 'category_portfolio',
			'type' => 'query_category',
			'label' => __('Category', 'themify'),
			'options' => array(
				'taxonomy' => 'portfolio-category'
			),
			'help' => sprintf(__('Add more <a href="%s" target="_blank">portfolio posts</a>', 'themify'), admin_url('post-new.php?post_type=portfolio'))
		),
		array(
			'id' => 'post_per_page_portfolio',
			'type' => 'text',
			'label' => __('Limit', 'themify'),
			'class' => 'xsmall',
			'help' => __('number of posts to show', 'themify')
		),
		array(
			'id' => 'offset_portfolio',
			'type' => 'text',
			'label' => __('Offset', 'themify'),
			'class' => 'xsmall',
			'help' => __('number of post to displace or pass over', 'themify')
		),
		array(
			'id' => 'order_portfolio',
			'type' => 'select',
			'label' => __('Order', 'themify'),
			'help' => __('Descending = show newer posts first', 'themify'),
			'options' => array(
				'desc' => __('Descending', 'themify'),
				'asc' => __('Ascending', 'themify')
			)
		),
		array(
			'id' => 'orderby_portfolio',
			'type' => 'select',
			'label' => __('Order By', 'themify'),
			'options' => array(
				'date' => __('Date', 'themify'),
				'id' => __('Id', 'themify'),
				'author' => __('Author', 'themify'),
				'title' => __('Title', 'themify'),
				'name' => __('Name', 'themify'),
				'modified' => __('Modified', 'themify'),
				'rand' => __('Rand', 'themify'),
				'comment_count' => __('Comment Count', 'themify')
			)
		),
		array(
			'id' => 'display_portfolio',
			'type' => 'select',
			'label' => __('Display', 'themify'),
			'options' => array(
				'content' => __('Content', 'themify'),
				'excerpt' => __('Excerpt', 'themify'),
				'none' => __('None', 'themify')
			)
		),
		array(
			'id' => 'hide_feat_img_portfolio',
			'type' => 'select',
			'label' => __('Hide Featured Image', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			)
		),
		array(
			'id' => 'image_size_portfolio',
			'type' => 'select',
			'label' => $this->is_img_php_disabled() ? __('Image Size', 'themify') : false,
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'hide' => $this->is_img_php_disabled() ? false : true,
			'options' => $image_sizes
		),
		array(
			'id' => 'img_width_portfolio',
			'type' => 'text',
			'label' => __('Image Width', 'themify'),
			'class' => 'xsmall'
		),
		array(
			'id' => 'img_height_portfolio',
			'type' => 'text',
			'label' => __('Image Height', 'themify'),
			'class' => 'xsmall'
		),
		array(
			'id' => 'unlink_feat_img_portfolio',
			'type' => 'select',
			'label' => __('Unlink Featured Image', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			)
		),
		array(
			'id' => 'hide_post_title_portfolio',
			'type' => 'select',
			'label' => __('Hide Post Title', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			)
		),
		array(
			'id' => 'unlink_post_title_portfolio',
			'type' => 'select',
			'label' => __('Unlink Post Title', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			)
		),
		array(
			'id' => 'hide_post_date_portfolio',
			'type' => 'select',
			'label' => __('Hide Post Date', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			)
		),
		array(
			'id' => 'hide_post_meta_portfolio',
			'type' => 'select',
			'label' => __('Hide Post Meta', 'themify'),
			'empty' => array(
				'val' => '',
				'label' => ''
			),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			)
		),
		array(
			'id' => 'hide_page_nav_portfolio',
			'type' => 'select',
			'label' => __('Hide Page Navigation', 'themify'),
			'options' => array(
				'yes' => __('Yes', 'themify'),
				'no' => __('No', 'themify')
			)
		)
	),
	// Styling
	'styling' => array(
		// Animation
		array(
			'id' => 'separator_animation',
			'title' => '',
			'description' => '',
			'type' => 'separator',
			'meta' => array('html'=>'<h4>'.__('Animation', 'themify').'</h4>'),
		),
		array(
			'id' => 'animation_effect',
			'type' => 'select',
			'label' => __( 'Effect', 'themify' ),
			'meta'	=> array(
				array('value' => '',   'name' => '', 'selected' => true),
				array('value' => 'fly-in',   'name' => __('Fly In', 'themify')),
				array('value' => 'fade-in', 'name' => __('Fade In', 'themify')),
				array('value' => 'slide-up',  'name' => __('Slide Up', 'themify'))
			)
		),
		// Background
		array(
			'type' => 'separator',
			'meta' => array('html'=>'<hr />')
		),
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
			'id' => 'css_portfolio',
			'type' => 'text',
			'label' => __('Additional CSS Class', 'themify'),
			'class' => 'large exclude-from-reset-field',
			'description' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') )
		)
	),
	'styling_selector' => array(
		'.module-portfolio .post' => array(
			'background_color', 'font_family', 'font_size', 'line_height', 'color', 'padding', 'margin', 'border_top', 'border_right', 'border_bottom', 'border_left'
		),
		'.module-portfolio a' => array( 'link_color', 'text_decoration' ),
		'.module-portfolio .post-title' => array(
			'font_family', 'color'
		),
		'.module-portfolio .post-title a' => array(
			'font_family', 'color'
		)
	)
) );

?>