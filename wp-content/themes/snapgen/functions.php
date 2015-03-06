<?php
add_action('wp_enqueue_scripts', 'snapgen_enqueue');
function snapgen_enqueue() {

	global $wp_styles;
	wp_enqueue_style('google-fonts', '//fonts.googleapis.com/css?family=Roboto:400,300,700');
	wp_enqueue_style('google-fonts', '//fonts.googleapis.com/css?family=Raleway:300');
	wp_enqueue_script('snapgen_js', get_stylesheet_directory_uri() . '/js/snapgen.js', array('jquery'));
	wp_enqueue_script('easing', get_stylesheet_directory_uri() . '/js/vendor/jquery.easing.1.3.js', array('jquery'));
	wp_enqueue_script('scrollto', get_stylesheet_directory_uri() . '/js/vendor/jquery.scrollto.js', array('jquery'));
	wp_enqueue_script('bootstrap', get_stylesheet_directory_uri() . '/js/vendor/bootstrap.js', array('jquery'));
	wp_enqueue_script('popunder', get_stylesheet_directory_uri() . '/js/jquery.popunder.js', array('jquery'));
    wp_enqueue_script('leanmodal', '/wp-content/plugins/snapgen/jquery.leanModal.min.js', array('jquery'));
	wp_enqueue_style('font-awesome', get_stylesheet_directory_uri() . '/css/font-awesome.css');
	wp_enqueue_style('snapgen-styles', get_stylesheet_directory_uri() . '/style.css');

}
add_theme_support('title-tag');

/*
 * Enable support for Post Thumbnails on posts and pages.
 *
 * See: https://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
 */
add_theme_support('post-thumbnails');
set_post_thumbnail_size(825, 510, true);

// This theme uses wp_nav_menu() in two locations.
register_nav_menus(array(
	'primary' => __('Primary Menu', 'snapgen'),
	'social' => __('Social Links Menu', 'snapgen'),
));

/*
 * Switch default core markup for search form, comment form, and comments
 * to output valid HTML5.
 */
add_theme_support('html5', array(
	'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
));

function snapgen_widgets_init() {
	register_sidebar(array(
		'name' => __('Sidebar', 'snapgen'),
		'id' => 'sidebar-1',
		'description' => __('Add widgets here to appear in your sidebar.', 'snapgen'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));

	register_sidebar(array(
		'name' => __('Hero', 'snapgen'),
		'id' => 'hero',
		'description' => __('Add widgets here to appear in your hero section.', 'snapgen'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));

	register_sidebar(array(
		'name' => __('Header', 'snapgen'),
		'id' => 'header',
		'description' => __('Add widgets here to appear in your header section.', 'snapgen'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h2 class="widget-title">',
		'after_title' => '</h2>',
	));
}
add_action('widgets_init', 'snapgen_widgets_init');
add_filter( 'gform_ajax_spinner_url', 'snapgen_custom_gforms_spinner' );
/**
 * Changes the default Gravity Forms AJAX spinner.
 *
 * @since 1.0.0
 *
 * @param string $src  The default spinner URL.
 * @return string $src The new spinner URL.
 */
function snapgen_custom_gforms_spinner( $src ) {

    return get_stylesheet_directory_uri() . '/images/ajax-loader.gif';
    
}

add_filter( 'gform_date_max_year', 'set_max_year' ,10, 3  );
function set_max_year( $max_year, $form, $field ) {

	if($form['id']==4){
	
	    return date('Y') - 25;
	}else{
		return $max_year;
	}
}

add_filter( 'gform_date_min_year', 'set_min_year', 10, 3  );
function set_min_year( $min_year, $form, $field ) {
	if($form['id']==4){
	 	return date('Y') - 80;
	}else{
		return $min_year;
	}
}

add_filter('gform_confirmation_anchor', '__return_false');



add_filter('gform_pre_render_4', 'add_jscript' );

function add_jscript($form){
	$current_page = GFFormDisplay::get_current_page( $form['id'] );

	if ($current_page == 0){
	?>
		<script type="text/javascript">
		var script = document.createElement("script");
		script.innerHTML = "jQuery('document').ready(function(){jQuery(document).bind('gform_confirmation_loaded', function(){jQuery('#working').delay('2000').fadeOut({complete:function(){jQuery('#continue_app').fadeIn();}});});});";
			window.parent.document.body.appendChild(script);
    
    </script>
    <?php
    return $form;
	} 
	else if($current_page == 2){

        ?>
        <script type="text/javascript">
       var script = document.createElement("script");
       
        	zip = window.parent.document.getElementById("input_4_25").value;
 			urlToShow = "/bw-life?zip="+zip;
 			

			script.innerHTML = "jQuery('document').ready(function(){jQuery(document).bind('gform_post_render', function(){jQuery(document).click(function(){if( jQuery('body', window.parent.document).data('popunder') == 'YES' ){jQuery('body', window.parent.document).data('popunder','NO');jQuery.popunder('"+urlToShow+"');}else{jQuery('body', window.parent.document).data('popunder','NO');}});});});";
			window.parent.document.body.appendChild(script);

        </script>
        <?php
        return $form;
    }
    else if($current_page==4){
		?>
		
		<script type="text/javascript">
		var script = document.createElement("script");
		script.innerHTML = "jQuery('document').ready(function(){jQuery(document).bind('gform_post_render', function(){jQuery('.gform_page_footer').after(jQuery('#field_4_23'));});});";
			window.parent.document.body.appendChild(script);
    
    </script>
    <?php
    return $form;
	} else{
		return $form;
	}
}

