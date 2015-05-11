<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 * 
 */

function optionsframework_option_name() {
	// This gets the theme name from the stylesheet (lowercase and without spaces)
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace("/\W/", "_", strtolower($themename) );
	
	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = $themename;
	update_option('optionsframework', $optionsframework_settings);
}

/**
 * Returns an array of all files in $directory_path of type $filetype.
 *
 * The $directory_uri + file name is used for the key
 * The file name is the value
 */
  
function options_stylesheets_get_file_list( $directory_path, $filetype, $directory_uri ) {
    $alt_stylesheets = array();
    $alt_stylesheet_files = array();
    if ( is_dir( $directory_path ) ) {
        $alt_stylesheet_files = glob( $directory_path . "*.$filetype");
        foreach ( $alt_stylesheet_files as $file ) {
            $file = str_replace( $directory_path, "", $file);
            $alt_stylesheets[ $directory_uri . $file] = $file;
        }
    }
    return $alt_stylesheets;
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the "id" fields, make sure to use all lowercase and no spaces.
 *  
 */

function optionsframework_options() {

	// Menu position
	
	// Buttons
	$btn_color = array("default" => "Default","primary" => "Primary","info" => "Info","success" => "Success","warning" => "Warning","danger" => "Danger","inverse" => "Inverse");
	$btn_size = array("mini" => "Mini","small" => "Small","default" => "Medium","large" => "Large");

	// fixed or scroll position
	$fixed_scroll = array("fixed" => "Fixed","static" => "Static");

	// Menu color
	$nav_color = array("default" => "Default","inverse" => "Inverse");

	// Homepage Latest Blog or Featured Image
	$hp_array = array(
		'featured' => __('Featured Hero Unit', 'responsive'),
		'latest' => __('Latest Blog Post', 'responsive')
		
	);

	$alt_stylesheets = options_stylesheets_get_file_list(
    get_stylesheet_directory() . '/css/bootswatch/', // $directory_path
    'css', // $filetype
    get_stylesheet_directory_uri() . '/css/bootswatch/' // $directory_uri
	);

	
	// Pull all the categories into an array
	$options_categories = array();  
	$options_categories_obj = get_categories();
	foreach ($options_categories_obj as $category) {
    	$options_categories[$category->cat_ID] = $category->cat_name;
	}
	
	// Pull all the pages into an array
	$options_pages = array();  
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages[''] = 'Select a page:';
	foreach ($options_pages_obj as $page) {
    	$options_pages[$page->ID] = $page->post_title;
	}
		
	// If using image radio buttons, define a directory path
	$imagepath =  get_bloginfo('template_directory') . '/images/';
		
	$options = array();

	$options[] = array( "name" => "General Settings",
						"type" => "heading");

	$options[] = array( "name" => "Logo",
						"desc" => "Upload image for Logo",
						"id" => "logo_upload",
						"type" => "upload");
						
	$options[] = array( "name" => "Link Color",
						"desc" => "Default used if no color is selected.",
						"id" => "link_color",
						"std" => "",
						"type" => "color");
					
	$options[] = array( "name" => "Link:hover Color",
						"desc" => "Default used if no color is selected.",
						"id" => "link_hover_color",
						"std" => "",
						"type" => "color");
						
	$options[] = array( "name" => "Link:active Color",
						"desc" => "Default used if no color is selected.",
						"id" => "link_active_color",
						"std" => "",
						"type" => "color");

	$options[] = array( "name" => "Breadcrumbs",
						"desc" => "Use breadcrumbs on pages",
						"id" => "breadcrumbs",
						"std" => "1",
						"type" => "checkbox");

		$options[] = array( "name" => "Homepage",
						"type" => "heading");


	$options[] = array( "name" => "Hero Content",
						"desc" => "Hero Unit Content",
						"id" => "home_hero_area",
						"std" => "",
						"type" => "textarea");

	$options[] = array( "name" => "Portfolio",
						"type" => "heading");

	$options[] = array( "name" => "Portfolio Columns",
						"desc" => "Select the number of columns (two, three, or four)",
						"id" => "portfolio_column",
						"std" => "three",
						"type" => "images",
						"options" => array(
							'two' => $imagepath . '2c.png',
							'three' => $imagepath . '3c.png',
							'four' => $imagepath . '4c.png')
						);

	$options[] = array( "name" => "Filter Buttons",
						"desc" => "Use filter buttons",
						"id" => "filter_btns",
						"std" => "1",
						"type" => "checkbox");

	$options[] = array( "name" => "Filter Button Size",
						"desc" => "size of filter button",
						"id" => "f_btn_size",
						"std" => "",
						"type" => "select",
						"class" => "mini", //mini, tiny, small
						"options" => $btn_size);

	$options[] = array( "name" => "Filter Button Color",
						"desc" => "color of filter button",
						"id" => "f_btn_color",
						"std" => "",
						"type" => "select",
						"class" => "mini", //mini, tiny, small
						"options" => $btn_color);

	$options[] = array( "name" => "Project Titles",
						"desc" => "Display project titles",
						"id" => "project_title",
						"std" => "1",
						"type" => "checkbox");
						
	$options[] = array( "name" => "Social",
						"type" => "heading");

	$options[] = array( "name" => "Twitter",
						"desc" => "Complete URL",
						"id" => "twitter_url",
						"std" => "",
						"type" => "text");

	$options[] = array( "name" => "Facebook",
						"desc" => "Complete URL",
						"id" => "fb_url",
						"std" => "",
						"type" => "text");

	$options[] = array( "name" => "Pinterest",
						"desc" => "Complete URL",
						"id" => "pinterest_url",
						"std" => "",
						"type" => "text");

	$options[] = array( "name" => "LinkedIn",
						"desc" => "Complete URL",
						"id" => "linkedin_url",
						"std" => "",
						"type" => "text");

	$options[] = array( "name" => "Google+",
						"desc" => "Complete URL",
						"id" => "google_url",
						"std" => "",
						"type" => "text");

	$options[] = array( "name" => "Github",
						"desc" => "Complete URL",
						"id" => "github_url",
						"std" => "",
						"type" => "text");

	$options[] = array( "name" => "RSS Feed",
						"desc" => "Complete URL",
						"id" => "rss_url",
						"std" => "",
						"type" => "text");

	
						
				
	return $options;
}

/* 
 * This is an example of how to add custom scripts to the options panel.
 * This example shows/hides an option when a checkbox is clicked.
 */

add_action('optionsframework_custom_scripts', 'optionsframework_custom_scripts');

function optionsframework_custom_scripts() { ?>

<script type="text/javascript">
jQuery(document).ready(function($) {

	$('#example_showhidden').click(function() {
  		$('#section-example_text_hidden').fadeToggle(400);
	});
	
	if ($('#example_showhidden:checked').val() !== undefined) {
		$('#section-example_text_hidden').show();
	}
	
});
</script>

<?php
}