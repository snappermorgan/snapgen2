<?php
add_action( 'wp_enqueue_scripts', 'snapgen_enqueue' );
function snapgen_enqueue() {
	
	global $wp_styles;
	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Roboto:400,300,700');
	wp_enqueue_style( 'google-fonts', '//fonts.googleapis.com/css?family=Raleway:300');
	wp_enqueue_script('snapgen_js', get_stylesheet_directory_uri() . '/js/snapgen.js');
	
	wp_enqueue_style('font-awesome', get_stylesheet_directory_uri() . '/css/font-awesome.css');
	wp_enqueue_style('snapgen-styles', get_stylesheet_directory_uri() . '/style.css');

}
add_theme_support( 'title-tag' );

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * See: https://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	set_post_thumbnail_size( 825, 510, true );

	// This theme uses wp_nav_menu() in two locations.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu',      'snapgen' ),
		'social'  => __( 'Social Links Menu', 'snapgen' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

function snapgen_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'snapgen' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Add widgets here to appear in your sidebar.', 'snapgen' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	)	);


	register_sidebar(array(
		'name'          => __( 'Hero', 'snapgen' ),
		'id'            => 'hero',
		'description'   => __( 'Add widgets here to appear in your hero section.', 'snapgen' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	));
}
add_action( 'widgets_init', 'snapgen_widgets_init' );
