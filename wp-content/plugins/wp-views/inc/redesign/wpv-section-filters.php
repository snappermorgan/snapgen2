<?php

/*
* We can enable this to hide the Filters section
*/

add_filter('wpv_sections_query_show_hide', 'wpv_show_hide_content_filter', 1,1);

function wpv_show_hide_content_filter($sections) {
	$sections['content-filter'] = array(
		'name'		=> __('Query filters', 'wpv-views'),
		);
	return $sections;
}

add_action('view-editor-section-query', 'add_view_filters', 50, 2);

function add_view_filters($view_settings, $view_id) {//echo '<pre>';print_r($view_settings);echo '</pre>';
    global $views_edit_help;
	$hide = '';
	if (isset($view_settings['sections-show-hide']) && isset($view_settings['sections-show-hide']['content-filter']) && 'off' == $view_settings['sections-show-hide']['content-filter']) {
		$hide = ' hidden';
	}?>
	<div class="wpv-setting-container wpv-settings-content-filter js-wpv-settings-content-filter<?php echo $hide; ?>">
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Query filter', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['filter_the_results']['title']; ?>" data-content="<?php echo $views_edit_help['filter_the_results']['content']; ?>"></i>
			</h3>
		</div>
		<div class="wpv-setting">
			<p class="js-no-filters hidden"><?php _e( 'No filters set', 'wpv-views' ) ?></p>
			<ul class="filter-list js-filter-list hidden">
				<?php
				if (isset($view_settings['query_type']) && isset($view_settings['query_type'][0])) {
					wpv_display_filters_list( $view_settings['query_type'][0], $view_settings );
				}
				?>
			</ul>
			<input type="hidden" class="js-wpv-filter-update-filters-list-nonce" value="<?php echo wp_create_nonce( 'wpv_view_filter_update_filters_list_nonce' ); ?>" />
			<p>
				<button class="button-secondary js-wpv-filter-add-filter" type="button" data-empty="<?php echo htmlentities( __('Add a filter', 'wpv-views'), ENT_QUOTES ); ?>" data-nonempty="<?php echo htmlentities( __('Add another filter', 'wpv-views'), ENT_QUOTES ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_add_filter' ); ?>">
					<i class="icon-plus"></i> <?php echo htmlentities( __('Add a filter', 'wpv-views'), ENT_QUOTES ); ?>
				</button>
				<?php
			// TODO all the following alerts should be added using javascript
				?>
			</p>
		</div>
	</div>

	<div class="popup-window-container"> <!-- Use this element as a container for all popup windows. This element is hidden. -->

		<div class="wpv-dialog js-filter-add-filter-form-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Add a filter','wpv-views') ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			<div class="wpv-dialog-content">

				<strong><?php _e('Select what to filter by:', 'wpv-views'); ?></strong>

				<?php wpv_filters_add_filter_select($view_settings); ?>

			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close js-filters-cancel-filter"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-primary js-filters-insert-filter" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filters_add_filter_nonce' ); ?>"><?php _e('Add filter','wpv-views') ?></button>
			</div>
		</div>

		<div class="wpv-dialog js-filter-taxonomy-delete-filter-row-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Delete taxonomy filters','wpv-views') ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			<div class="wpv-dialog-content">

				<h3><?php _e('There are more than one taxonomy filters', 'wpv-views'); ?></h3>
				<p>
					<?php _e( 'You can delete them all at once or open the editor and delete individual filters.', 'wpv-views' ); ?>
				</p>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close js-filters-cancel-filter"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button js-wpv-filter-taxonomy-edit-filter-row"><?php _e( 'Edit the taxonomy filters', 'wpv-views' ); ?></button>
				<button class="button button-primary js-wpv-filters-taxonomy-delete-filter-row" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_taxonomy_row_delete_nonce' ); ?>"><?php _e('Delete all taxonomy filters','wpv-views') ?></button>
			</div>
		</div>

		<div class="wpv-dialog js-filter-custom-field-delete-filter-row-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Delete custom field filters','wpv-views') ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			<div class="wpv-dialog-content">
				<h3><?php _e('There are more than one custom field filters', 'wpv-views'); ?></h3>
				<p>
					<?php _e( 'You can delete them all at once or open the editor and delete individual filters.', 'wpv-views' ); ?>
				</p>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close js-filters-cancel-filter"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button js-filter-custom-field-edit-filter-row"><?php _e( 'Edit the custom field filters', 'wpv-views' ); ?></button>
				<button class="button button-primary js-filters-custom-field-delete-filter-row" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_custom_field_row_delete_nonce' ); ?>"><?php _e('Delete all custom field filters','wpv-views') ?></button>
			</div>
		</div>
		<div class="wpv-dialog js-filter-usermeta-field-delete-filter-row-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Delete usermeta field filters','wpv-views') ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			<div class="wpv-dialog-content">
				<p>
					<strong><?php _e('There are more than one usermeta field filters. What would you like to do?', 'wpv-views'); ?></strong>
				</p>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close js-filters-cancel-filter"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button js-filter-usermeta-field-edit-filter-row"><?php _e( 'Edit the usermeta field filters', 'wpv-views' ); ?></button>
				<button class="button button-primary js-filters-usermeta-field-delete-filter-row" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_usermeta_field_row_delete_nonce' ); ?>"><?php _e('Delete all usermeta field filters','wpv-views') ?></button>
			</div>
		</div>

	</div>
<?php }

add_action('admin_head', 'wpv_filter_url_check_js');

function wpv_filter_url_check_js() {
	// TODO move this to the script localization
	$reserved_list = array(
		'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in',
		'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'customize_messenger_channel',
		'customized', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute',
		'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id',
		'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status',
		'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search',
		'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in',
		'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments',
		'year '
	);

	$toolset_reserved_words = array(
		'wpv_column_sort_id', 'wpv_column_sort_dir', 'wpv_paged_preload_reach', 'wpv_view_count', 'wpv_filter_submit', 'wpv_post_search'
	);
	$toolset_reserved_words = apply_filters('wpv_toolset_reserved_words', $toolset_reserved_words);
	
	$toolset_reserved_attributes = array(
		'name', 'limit', 'offset'
	);
	$toolset_reserved_attributes = apply_filters('wpv_toolset_reserved_attributes', $toolset_reserved_attributes);

	global $wp_post_types;
    	$reserved_post_types = array_keys( $wp_post_types );

    	$wpv_taxes = get_taxonomies();
    	$reserved_taxonomies = array_keys( $wpv_taxes );

    	$wpv_forbidden_parameters = array(
		'wordpress' => $reserved_list,
		'toolset' => $toolset_reserved_words,
		'toolset_attr' => $toolset_reserved_attributes,
		'post_type' => $reserved_post_types,
		'taxonomy' => $reserved_taxonomies,
    	);

    	$hierarchical_post_names = array();
    	$hierarchical_post_types = get_post_types( array( 'hierarchical' => true ), 'objects');
    	foreach ($hierarchical_post_types as $post_type) {
		$hierarchical_post_names[] = $post_type->name;
    	}

	?>
    <script type="text/javascript">
		var wpv_forbidden_parameters = <?php echo json_encode($wpv_forbidden_parameters); ?>;
		var wpv_hierarchical_post_types = <?php echo json_encode($hierarchical_post_names); ?>;
	</script>
	<?php
}

add_action('wp_ajax_wpv_filters_add_filter_row', 'wpv_filters_add_filter_row_callback');

function wpv_filters_add_filter_row_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_filters_add_filter_nonce') ) die("Security check");
	if ( empty( $_POST['filter_type'] ) ) {
		echo $_POST['id'];
		die();
	}
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	if (!isset($view_array['taxonomy_type']) || empty($view_array['taxonomy_type'])) {
		$view_array['taxonomy_type'] = array('category');
	}
	if (!isset($view_array['roles_type']) || empty($view_array['roles_type'])) {
		$view_array['roles_type'] = array('administrator');
	}
	if (!isset($view_array['post_type']) || empty($view_array['post_type'])) {
		$view_array['post_type'] = array();
	}
	$filters = array();
	$filters = apply_filters('wpv_filters_add_filter', $filters, $view_array['post_type']);
	$filters = apply_filters('wpv_taxonomy_filters_add_filter', $filters, $view_array['taxonomy_type'][0]);
	$filters = apply_filters('wpv_users_filters_add_filter', $filters, $view_array['roles_type'][0]);
	if (isset($filters[$_POST['filter_type']])) {
		if (isset($filters[$_POST['filter_type']]['args'])) {
			call_user_func($filters[$_POST['filter_type']]['callback'], $filters[$_POST['filter_type']]['args']);
		} else {
			call_user_func($filters[$_POST['filter_type']]['callback']);
		}
	}
	die();
}


function give_group_to_field( $filters )
{
	$generics = array( 'post_author', 'post_status', 'post_search', 'post_parent', 'post_relationship', 'post_id' );
	$users_filters = array( 'users_filter', 'usermeta_filter');
	$groups = array();

	foreach( $filters as $type => $filter )
	{
		if( in_array( $type,  $generics) )
		{
			$groups["Post filters"][$type] = $filter;
		}
		else if( $type == 'post_category' || strpos($type, 'tax_input') !== false )
		{

			$groups['Taxonomy'][$type] = $filter;
		}
		else if( strpos($type, 'custom-field-wpcf-') !== false )
		{
				$g = '';
				$nice_name = explode('custom-field-wpcf-', $type);
				$id = ( isset($nice_name[1] ) ) ? $nice_name[1] : $type;
				if( function_exists('wpcf_admin_fields_get_groups_by_field') )
				{
					foreach( wpcf_admin_fields_get_groups_by_field( $id ) as $gs )
					{
						$g = $gs['name'];
					}
				}
				$gr = $g ? $g : "Custom fields";

				$groups[$gr][$type] = $filter;
		}
		else if( strpos($type, 'custom-field-views_woo_') !== false )
		{
			$g = '';
			$nice_name = explode('custom-field-', $type);
	    		$id = ( isset($nice_name[1] ) ) ? $nice_name[1] : $type;
			if( function_exists('wpcf_admin_fields_get_groups_by_field') )
			{
				foreach( wpcf_admin_fields_get_groups_by_field( $id ) as $gs )
				{
					$g = $gs['name'];
				}
			}
			$gr = $g ? $g : "WooCommerce Views filter fields";

			$groups[$gr][$type] = $filter;
		}
        
        else if( strpos($type, 'usermeta-field-basic-') !== false )
        {
            $gr = "Basic fields";
            $groups[$gr][$type] = $filter;
        }
        else if( strpos($type, 'usermeta-field-wpcf-') !== false )
        {
                $g = '';
                $nice_name = explode('usermeta-field-wpcf-', $type);
                $id = ( isset($nice_name[1] ) ) ? $nice_name[1] : $type;
                if( function_exists('wpcf_admin_fields_get_groups_by_field') )
                {
                    foreach( wpcf_admin_fields_get_groups_by_field( $id, 'wp-types-user-group' ) as $gs )
                    {
                        $g = $gs['name'];
                    }
                }
                $gr = $g ? $g : "Users fields";

                $groups[$gr][$type] = $filter;
        }
        else if( strpos($type, 'usermeta-field-') !== false &&  strpos($type, 'usermeta-field-basic-') === false &&  strpos($type, 'usermeta-field-wpcf-') === false )
        {
                $gr = "User fields";
                $groups[$gr][$type] = $filter;
        }
		else if( in_array( $type,  $users_filters) ){
			$groups["Users filters"][$type] = $filter;
		}
		else
		{

			$groups['Custom fields'][$type] = $filter;
		}
	}
	return $groups;
}


function wpv_filters_add_filter_select($view_settings) {
	$filters = array();
	if (!isset($view_settings['post_type'])) $view_settings['post_type'] = array();
	if (!isset($view_settings['taxonomy_type'])) $view_settings['taxonomy_type'] = array('category');
	if (!isset($view_settings['roles_type'])) $view_settings['roles_type'] = array('users');
	if (isset($view_settings['query_type']) && isset($view_settings['query_type'][0])) {
		switch ($view_settings['query_type'][0]) {
			case 'posts':
				$filters = apply_filters('wpv_filters_add_filter', $filters, $view_settings['post_type']);
				break;
			case 'taxonomy':
				$filters = apply_filters('wpv_taxonomy_filters_add_filter', $filters, $view_settings['taxonomy_type'][0]);
				break;
			case 'users':
				$filters = apply_filters('wpv_users_filters_add_filter', $filters, $view_settings['roles_type'][0]);
				break;	
		}
	}
	?>
	
	<select id="filter-add-select" class="js-filter-add-select">
	<option value="-1"><?php echo __('--- Please select ---', 'wpv-views'); ?></option>
	<?php

	foreach( give_group_to_field( $filters ) as $group => $f )
	{
		if( $f && !empty( $f ) ):
		?>
		<optgroup label="<?php echo $group?>">
		<?php
		foreach($f as $type => $filter) {
			if (!isset($view_settings[$filter['present']])) {
				?>
				<option value="<?php echo $type; ?>"><?php echo $filter['name']; ?></option>

				<?php
			}
		}
		?>
		</optgroup>
		<?php
		endif;
	}
	?>
	</select>
<?php }

add_action('wp_ajax_wpv_filters_upate_filters_select', 'wpv_filters_upate_filters_select_callback');

function wpv_filters_upate_filters_select_callback() {
	$nonce = $_POST["wpnonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_filter_add_filter') ) die("Security check");
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	wpv_filters_add_filter_select($view_array);
	die();
}

add_action('wp_ajax_wpv_filter_update_filters_list', 'wpv_filter_update_filters_list_callback');

function wpv_filter_update_filters_list_callback() {
	$nonce = $_POST["nonce"];
	if (! wp_verify_nonce($nonce, 'wpv_view_filter_update_filters_list_nonce') ) die("Security check");
	$view_array = get_post_meta($_POST["id"], '_wpv_settings', true);
	$return_result = array();
	// Filters list
	$filters_list = '';
	ob_start();
	wpv_display_filters_list( $view_array['query_type'][0], $view_array );
	$filters_list = ob_get_contents();
	ob_end_clean();
	$return_result['wpv_filter_update_filters_list'] = $filters_list;
	$return_result['success'] = $_POST['id'];
	echo json_encode( $return_result );
	die();
}

add_action('wp_ajax_wpv_filter_make_intersection_filters', 'wpv_filter_make_intersection_filters');

function wpv_filter_make_intersection_filters() { // TODO this is undone still
	$nonce = $_POST["nonce"];
	if (! wp_verify_nonce( $nonce, 'wpv_view_make_intersection_filters' ) ) die( "Security check" );
	$view_array = get_post_meta( $_POST["id"], '_wpv_settings', true );
	$view_array['taxonomy_relationship'] = 'AND';
	$view_array['custom_fields_relationship'] = 'AND';
	$view_array['usermeta_fields_relationship'] = 'AND';
	update_post_meta( $_POST["id"], '_wpv_settings', $view_array );
	$return_result = array();
	// Filters list
	$filters_list = '';
	ob_start();
	wpv_display_filters_list( $view_array['query_type'][0], $view_array );
	$filters_list = ob_get_contents();
	ob_end_clean();
	$return_result['wpv_filter_update_filters_list'] = $filters_list;
	$return_result['success'] = $_POST['id'];
	echo json_encode( $return_result );
	die();
}

function wpv_display_filters_list( $query_type, $view_settings ) {
	switch ( $query_type ) {
		case 'posts':
			do_action('wpv_add_filter_list_item', $view_settings);
			break;
		case 'taxonomy':
			do_action('wpv_add_taxonomy_filter_list_item', $view_settings);
			break;
		case 'users':
			do_action('wpv_add_users_filter_list_item', $view_settings);
			break;
	}
}

/**
* WPV_Filter_Item
*
* Class to display several common elements in the Query filters section, mainly for list items
*
* @since 1.7.0
*/

class WPV_Filter_Item {
	
	public static function simple_filter_list_item( $filter_slug = 'slug', $filter_target = 'posts', $li_slug = 'slug', $title = 'Filter', $content = '' ) {
		?>
		<li id="js-row-<?php echo $filter_slug; ?>" data-filterslug="<?php echo $filter_slug; ?>" class="js-filter-row js-filter-row-simple js-wpv-filter-row-<?php echo $li_slug; ?> js-filter-for-<?php echo $filter_target; ?> js-filter-<?php echo $li_slug; ?> js-filter-row-<?php echo $filter_slug; ?>">

			<span class="wpv-filter-title">
				<i class="icon-filter"></i>&nbsp;&nbsp;<?php echo $title; ?>
			</span>
			<?php
			echo $content;
			?>
		</li>
		<?php
	}
	
	public static function simple_filter_list_item_buttons( $li_slug = 'slug', $save_action = '', $save_nonce = '', $delete_action = '', $delete_nonce = '' ) {
		?>
		<span class='edit-filter wpv-edit-filter js-wpv-filter-edit-controls'>
			<button class="button button-secondary button-small js-wpv-filter-edit-ok js-wpv-filter-<?php echo $li_slug; ?>-edit-ok hidden" data-save="<?php echo esc_attr( __('Save', 'wpv-views') ); ?>" data-close="<?php echo esc_attr( __('Close', 'wpv-views') ); ?>" data-success="<?php echo esc_attr( __('Updated', 'wpv-views') ); ?>" data-unsaved="<?php echo esc_attr( __('Not saved', 'wpv-views') ); ?>" data-saveaction="<?php echo $save_action; ?>" data-nonce="<?php echo $save_nonce; ?>">
				<i class='icon-chevron-up'></i>
				<?php  _e('Close', 'wpv-views'); ?>
			</button>
			<button class='button button-secondary button-small js-wpv-filter-edit-open js-wpv-filter-<?php echo $li_slug; ?>-edit-open' title='<?php echo esc_attr( __('Edit this filter','wpv-views') ); ?>'>
				<i class='icon-edit'></i>
				<?php _e( 'Edit', 'wpv-views' ); ?>
			</button>
			<button class='button button-secondary button-small js-wpv-filter-remove js-filter-remove' title='<?php echo esc_attr( __('Delete this filter', 'wpv-views') ); ?>' data-deleteaction="<?php echo $delete_action; ?>" data-nonce='<?php echo $delete_nonce; ?>'>
				<i class='icon-trash'></i>
				<?php _e( 'Delete', 'wpv-views' ); ?>
			</button>
		</span>
		<?php
	}
	
	public static function multiple_filter_list_item( $filter_slug = 'slug', $filter_target = 'posts', $title = 'Filter', $content = '' ) {
		?>
		<li id="js-row-<?php echo $filter_slug; ?>" data-filterslug="<?php echo $filter_slug; ?>" class="filter-row-multiple js-filter-row js-filter-row-multiple js-wpv-filter-row-<?php echo $filter_slug; ?> js-filter-for-<?php echo $filter_target; ?> js-filter-<?php echo $filter_slug; ?> js-filter-row-<?php echo $filter_slug; ?>">

			<span class="wpv-filter-title">
				<i class="icon-filter"></i>&nbsp;&nbsp;<?php echo $title; ?>
			</span>
			<?php
			echo $content;
			?>
		</li>
		<?php
	}
	
	public static function filter_list_item_buttons( $li_slug = 'slug', $save_action = '', $save_nonce = '', $delete_action = '', $delete_nonce = '' ) {
		?>
		<span class='edit-filter wpv-edit-filter js-wpv-filter-edit-controls'>
			<button class="button button-secondary button-small js-wpv-filter-edit-ok js-wpv-filter-<?php echo $li_slug; ?>-edit-ok hidden" data-save="<?php echo esc_attr( __('Save', 'wpv-views') ); ?>" data-close="<?php echo esc_attr( __('Close', 'wpv-views') ); ?>" data-success="<?php echo esc_attr( __('Updated', 'wpv-views') ); ?>" data-unsaved="<?php echo esc_attr( __('Not saved', 'wpv-views') ); ?>" data-saveaction="<?php echo $save_action; ?>" data-nonce="<?php echo $save_nonce; ?>">
				<i class='icon-chevron-up'></i>
				<?php  _e('Close', 'wpv-views'); ?>
			</button>
			<button class='button button-secondary button-small js-wpv-filter-edit-open js-wpv-filter-<?php echo $li_slug; ?>-edit-open' title='<?php echo esc_attr( __('Edit this filter','wpv-views') ); ?>'>
				<i class='icon-edit'></i>
				<?php _e( 'Edit', 'wpv-views' ); ?>
			</button>
			<button class='button button-secondary button-small js-wpv-filter-remove js-filter-remove js-wpv-filter-remove-<?php echo $li_slug; ?>' title='<?php echo esc_attr( __('Delete this filter', 'wpv-views') ); ?>' data-deleteaction="<?php echo $delete_action; ?>" data-nonce='<?php echo $delete_nonce; ?>'>
				<i class='icon-trash'></i>
				<?php _e( 'Delete', 'wpv-views' ); ?>
			</button>
		</span>
		<?php
	}
	
	public static function get_custom_filter_function_and_value( $value ) {
		$trim = trim( $value );
		$function = 'constant';
		$return_val = $value;
		$text_boxes = 1;
		$singles = array(
			'url' => '/^URL_PARAM\((.*?)\)/',
			'attribute' => '/^VIEW_PARAM\((.*?)\)/',
			'future_day' => '/^FUTURE_DAY\((.*?)\)/',
			'past_day' => '/^PAST_DAY\((.*?)\)/',
			'future_month' => '/^FUTURE_MONTH\((.*?)\)/',
			'past_month' => '/^PAST_MONTH\((.*?)\)/',
			'future_year' => '/^FUTURE_YEAR\((.*?)\)/',
			'past_year' => '/^PAST_YEAR\((.*?)\)/',
			'seconds_from_now' => '/^SECONDS_FROM_NOW\((.*?)\)/',
			'months_from_now' => '/^MONTHS_FROM_NOW\((.*?)\)/',
			'years_from_now' => '/^YEARS_FROM_NOW\((.*?)\)/',
			'date' => '/^DATE\((.*?)\)/'
		);
		foreach ( $singles as $code => $pattern ) {
			if ( preg_match( $pattern, $trim, $matches ) == 1 ) {
				$function = $code;
				$return_val = $matches[1];
				break;
			}
		}
		$zeros = array(
			'now' => '/^NOW\((.*?)\)/',
			'today' => '/^TODAY\((.*?)\)/',
			'this_month' => '/^THIS_MONTH\((.*?)\)/',
			'this_year' => '/^THIS_YEAR\((.*?)\)/'
		);
		foreach ( $zeros as $code => $pattern ) {
			if ( preg_match( $pattern, $trim, $matches ) == 1 ) {
				$function = $code;
				$return_val = '';
				$text_boxes = 0;
				break;
			}
		}
		$return_val = str_replace( '####coma####', ',', $return_val );
		return array( 'function' => $function, 'value' => $return_val, 'text_boxes' => $text_boxes );
	}
	
	//function _wpv_encode_date( $value ) {
	public static function encode_date( $value ) {
		if ( preg_match_all( '/DATE\(([\\d,-]*)\)/', $value, $matches ) ) {
			foreach( $matches[0] as $match ) {
				$value = str_replace( $match, str_replace( ',', '####coma####', $match ), $value );
			}		
		}
		return $value;
	}

	//function _wpv_unencode_date( $value ) {
	public static function unencode_date( $value ) {
		return str_replace( '####coma####', ',', $value );
	}
	
	public static function date_field_controls( $function, $value ) {
		global $wp_locale;
		if ( $function == 'date' ) {
			$date_parts = explode( ',', $value );
			$time_adj = mktime( 0, 0, 0, $date_parts[1], $date_parts[0], $date_parts[2] );
		} else {
			$time_adj = current_time( 'timestamp' );
		}
		$jj = gmdate( 'd', $time_adj );
		$mm = gmdate( 'm', $time_adj );
		$aa = gmdate( 'Y', $time_adj );
		?>
		<span class="js-wpv-custom-field-date js-wpv-usermeta-field-date">
			<select autocomplete="off">
			<?php
			for ( $i = 1; $i < 13; $i = $i +1 ) {
				$monthnum = zeroise( $i, 2 );
				?>
				<option value="<?php echo $monthnum; ?>" <?php selected( $i, $mm ); ?>><?php echo $monthnum . ' - ';echo $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ); ?></option>
				<?php
			}
			?>
			</select>
			<input type="text" value="<?php echo $jj; ?>" size="2" maxlength="2" autocomplete="off" />
			<input type="text" value="<?php echo $aa; ?>" size="4" maxlength="4" autocomplete="off" />
		</span>
		<?php
	}
	
}