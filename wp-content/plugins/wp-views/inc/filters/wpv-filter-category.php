<?php

/**
* Taxonomy filter
*
* @package Views
*
* @since unknown
*/

WPV_Taxonomy_Filter::on_load();

/**
* WPV_Taxonomy_Filter
*
* Views Taxonomy Filter Class
*
* @since 1.7.0
*/

class WPV_Taxonomy_Filter {

    static function on_load() {
        add_action( 'init', array( 'WPV_Taxonomy_Filter', 'init' ) );
		add_action( 'admin_init', array( 'WPV_Taxonomy_Filter', 'admin_init' ) );
    }

    static function init() {
		
    }
	
	static function admin_init() {
		// Register filters in lists and dialogs
		add_filter( 'wpv_filters_add_filter', array( 'WPV_Taxonomy_Filter', 'wpv_filters_add_filter_taxonomy' ), 20, 2 );
		add_action( 'wpv_add_filter_list_item', array( 'WPV_Taxonomy_Filter', 'wpv_add_filter_taxonomy_list_item' ), 1, 1 );
		//AJAX callbakcks
		add_action( 'wp_ajax_wpv_filter_taxonomy_update', array( 'WPV_Taxonomy_Filter', 'wpv_filter_taxonomy_update_callback' ) );
			// TODO This might not be needed here, maybe for summary filter
			add_action( 'wp_ajax_wpv_filter_taxonomy_sumary_update', array( 'WPV_Taxonomy_Filter', 'wpv_filter_taxonomy_sumary_update_callback' ) );
		add_action( 'wp_ajax_wpv_filter_taxonomy_delete', array( 'WPV_Taxonomy_Filter', 'wpv_filter_taxonomy_delete_callback' ) );
		add_filter( 'wpv-view-get-summary', array( 'WPV_Taxonomy_Filter', 'wpv_taxonomy_summary_filter' ), 6, 3 );
	}
	
	/**
	* wpv_filters_add_filter_taxonomy
	*
	* Register the taxonomy filter in the popup dialog
	*
	* @param $filters
	* @param $post_type
	*
	* @since unknown
	*/

	static function wpv_filters_add_filter_taxonomy( $filters, $post_type ) {
		$taxonomies_valid = get_object_taxonomies( $post_type, 'objects' );
		$exclude_tax_slugs = array();
		$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
		foreach ( $taxonomies_valid as $category_slug => $category ) {
			if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
				continue;
			}
			if ( ! $category->show_ui ) {
				continue; // Only show taxonomies with show_ui set to TRUE
			}
			$taxonomy = $category->name;
			$name = ( $taxonomy == 'category' ) ? 'post_category' : 'tax_input[' . $taxonomy . ']';
			$filters[$name] = array(
				'name' => $category->label,
				'present' => 'tax_' . $taxonomy . '_relationship',
				'callback' => array( 'WPV_Taxonomy_Filter', 'wpv_add_new_filter_taxonomy_list_item' ),
				'args' => $category
			);
		}
		return $filters;
	}
	
	/**
	* wpv_add_new_filter_taxonomy_list_item
	*
	* Register the taxonomy filter in the filters list
	*
	* @param $args
	*
	* @since unknown
	*/

	static function wpv_add_new_filter_taxonomy_list_item( $args ) {
		$relationship_name = ( $args->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $args->name . '_relationship';
		$new_tax_filter_settings = array(
			$relationship_name => 'IN',
		);
		WPV_Taxonomy_Filter::wpv_add_filter_taxonomy_list_item( $new_tax_filter_settings );
	}
	
	/**
	* wpv_add_filter_taxonomy_list_item
	*
	* Render taxonomy filter item in the filters list
	*
	* @param $view_settings
	*
	* @since unknown
	*/

	static function wpv_add_filter_taxonomy_list_item( $view_settings ) {
		global $sitepress;
		if ( ! isset( $view_settings['taxonomy_relationship'] ) ) {
			$view_settings['taxonomy_relationship'] = 'AND';
		}
		$summary = '';
		$td = '';
		$taxonomies = get_taxonomies( '', 'objects' );
		foreach ( $taxonomies as $category_slug => $category ) {
			$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
			$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
			if ( isset( $view_settings[$relationship_name] ) ) {
				if ( ! isset( $view_settings[$save_name] ) ) {
					$view_settings[$save_name] = array();
				}
				if ( isset( $sitepress ) && function_exists( 'icl_object_id' ) ) {
					// Adjust for WPML support
					$trans_term_ids = array();
					foreach ( $view_settings[$save_name] as $untrans_term_id ) {
						$trans_term_ids[] = icl_object_id( $untrans_term_id, $category->name, true );
					}
					$view_settings[$save_name] = $trans_term_ids;
				}
				$name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
				$td .= WPV_Taxonomy_Filter::wpv_get_list_item_ui_post_taxonomy($category, $view_settings[$save_name], $view_settings);
				if ( $summary != '' ) {
					if ( $view_settings['taxonomy_relationship'] == 'OR') {
						$summary .= __( ' OR ', 'wpv-views' );
					} else {
						$summary .= __( ' AND ', 'wpv-views' );
					}
				}
				$summary .= wpv_get_taxonomy_summary( $name, $view_settings, $view_settings[$save_name] );
			}
		}
		if ( $td != '' ) {
			ob_start();
			WPV_Filter_Item::filter_list_item_buttons( 'taxonomy', 'wpv_filter_taxonomy_update', wp_create_nonce( 'wpv_view_filter_taxonomy_nonce' ), 'wpv_filter_taxonomy_delete', wp_create_nonce( 'wpv_view_filter_taxonomy_delete_nonce' ) );
			?>
			<?php if ( $summary != '' ) {
			?>
				<p class='wpv-filter-taxonomy-edit-summary js-wpv-filter-summary js-wpv-filter-taxonomy-summary'>
				<?php _e('Select posts with taxonomy: ', 'wpv-views');
				echo $summary; ?>
				</p>
			<?php 
			}
			?>
			<div id="wpv-filter-taxonomy-edit" class="wpv-filter-edit js-wpv-filter-edit js-wpv-filter-taxonomy-edit js-wpv-filter-options" style="padding-bottom:28px;">
			<?php echo $td;?>
				<div class="wpv-filter-taxonomy-relationship wpv-filter-multiple-element js-wpv-filter-taxonomy-relationship">
					<h4><?php _e('Taxonomy relationship:', 'wpv-views') ?></h4>
					<div class="wpv-filter-multiple-element-options">
						<?php _e('Relationship to use when querying with multiple taxonomies:', 'wpv-views'); ?>
						<select name="taxonomy_relationship">
							<option value="AND" <?php selected( $view_settings['taxonomy_relationship'], 'AND' ); ?>><?php _e( 'AND', 'wpv-views' ); ?>&nbsp;</option>
							<option value="OR" <?php selected( $view_settings['taxonomy_relationship'], 'OR' ); ?>><?php _e( 'OR', 'wpv-views' ); ?></option>
						</select>
					</div>
				</div>
				<span class="filter-doc-help">
				<?php echo sprintf(
					__( '%sLearn about filtering by taxonomy%s', 'wpv-views' ),
					'<a class="wpv-help-link" href="' . WPV_FILTER_BY_TAXONOMY_LINK . '" target="_blank">',
					' &raquo;</a>'
				); ?>
				</span>
			</div>
		<?php 
			$li_content = ob_get_clean();
			WPV_Filter_Item::multiple_filter_list_item( 'taxonomy', 'posts', __( 'Taxonomy filter', 'wpv-views' ), $li_content );
		}
	}
	
	/**
	* wpv_get_list_item_ui_post_taxonomy
	*
	* Render taxonomy filter item content in the filters list
	*
	* @param $category
	* @param $cats_selected
	* @param $view_settings
	*
	* @since unknown
	*/

	static function wpv_get_list_item_ui_post_taxonomy( $category, $cats_selected, $view_settings = array() ) {
		$type = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
		$taxonomy = $category->name;
		$taxonomy_name = $category->label;
		if ( ! isset($view_settings['tax_' . $taxonomy . '_relationship'] ) ) {
			$view_settings['tax_' . $taxonomy . '_relationship'] = 'IN';
		}
		if ( ! isset( $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] ) || empty( $view_settings['taxonomy-' . $taxonomy . '-attribute-url'] ) ) {
			$view_settings['taxonomy-' . $taxonomy . '-attribute-url'] = 'wpv' . $taxonomy;
		}
		if ( isset( $view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'] ) && is_array( $view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'] ) ) {
			$view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'] = $view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'][0];
		}
		if ( ! isset( $view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'] ) ) {
			$view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'] = 'slug';
		}
		ob_start();
		?>
			<div class="wpv-filter-multiple-element js-wpv-filter-multiple-element js-wpv-filter-taxonomy-multiple-element js-wpv-filter-row-taxonomy-<?php echo $taxonomy; ?> js-wpv-filter-row-tax-<?php echo $type; ?>" data-taxonomy="<?php echo $taxonomy; ?>">
				<h4><?php echo $taxonomy_name; ?></h4>
				<span class="wpv-filter-multiple-element-delete">
					<button class="button button-secondary button-small js-filter-remove" data-taxonomy="<?php echo $taxonomy; ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_taxonomy_delete_nonce' );?>">
						<i class="icon-trash"></i>&nbsp;&nbsp;<?php _e( 'Delete', 'wpv-views' ); ?>
					</button>
				</span>
				<div class="wpv-filter-multiple-element-options">
				<?php _e('Taxonomy is:', 'wpv-views'); ?>
					<select class="wpv_taxonomy_relationship js-wpv-taxonomy-relationship" name="tax_<?php echo $taxonomy; ?>_relationship">
						<option value="IN" <?php selected( $view_settings['tax_' . $taxonomy . '_relationship'], 'IN' ); ?>><?php _e('Any of the following', 'wpv-views'); ?></option>
						<option value="NOT IN" <?php selected( $view_settings['tax_' . $taxonomy . '_relationship'], 'NOT IN' ); ?>><?php _e('NOT one of the following', 'wpv-views'); ?></option>
						<option value="AND" <?php selected( $view_settings['tax_' . $taxonomy . '_relationship'], 'AND' ); ?>><?php _e('All of the following', 'wpv-views'); ?></option>
						<option value="FROM PAGE" <?php selected( $view_settings['tax_' . $taxonomy . '_relationship'], 'FROM PAGE' ); ?>><?php _e('Value set by the current page', 'wpv-views'); ?></option>
						<option value="FROM ATTRIBUTE" <?php selected( $view_settings['tax_' . $taxonomy . '_relationship'], 'FROM ATTRIBUTE' ); ?>><?php _e('Value set by View shortcode attribute', 'wpv-views'); ?></option>
						<option value="FROM URL" <?php selected( $view_settings['tax_' . $taxonomy . '_relationship'], 'FROM URL' ); ?>><?php _e('Value set by URL parameter', 'wpv-views'); ?></option>
						<option value="FROM PARENT VIEW" <?php selected( $view_settings['tax_' . $taxonomy . '_relationship'], 'FROM PARENT VIEW' ); ?>><?php _e('Value set by parent view', 'wpv-views'); ?></option>
					</select>
					<?php
					$hidden = '';
					if ( ! in_array( $view_settings['tax_' . $taxonomy . '_relationship'], array( 'IN', 'NOT IN', 'AND' ) ) ) {
						$hidden = ' hidden';
					}
					?>
					<ul id="taxonomy-<?php echo $taxonomy; ?>" class="wpv-mightlong-list wpv-filter-multiple-element-options-mode js-taxonomy-checklist<?php echo $hidden; ?>">
						<?php 
						$my_walker = new WPV_Walker_Taxonomy_Checkboxes_Flat();
						wp_terms_checklist( 0, array( 'taxonomy' => $taxonomy, 'selected_cats' => $cats_selected, 'walker' => $my_walker ) ) ?>
					</ul>
					<?php
					$hidden = '';
					if ( ! in_array( $view_settings['tax_' . $taxonomy . '_relationship'], array( 'FROM ATTRIBUTE', 'FROM URL' ) ) ) {
						$hidden = ' hidden';
					}
					?>
					<ul id="taxonomy-<?php echo $taxonomy; ?>-attribute-url" class="wpv-filter-multiple-element-options-mode js-taxonomy-parameter<?php echo $hidden; ?>">
						<li>
							<label><?php echo __('Value: ');?></label>
							<?php $checked = $view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'] == 'name' ? 'checked="checked"' : ''; ?>
							<label><input type="radio" name="taxonomy-<?php echo $taxonomy; ?>-attribute-url-format[]" value="name" <?php echo $checked;?> /><?php echo __('Taxonomy name', 'wpv-views');?></label>
							<?php $checked = $view_settings['taxonomy-' . $taxonomy . '-attribute-url-format'] == 'slug' ? 'checked="checked"' : ''; ?>
							<label><input type="radio" name="taxonomy-<?php echo $taxonomy; ?>-attribute-url-format[]" value="slug" <?php echo $checked;?> /><?php echo __('Taxonomy slug', 'wpv-views');?></label>
						</li>
						<li>
							<label class="js-taxonomy-param-label" data-attribute="<?php echo __('Shortcode attribute', 'wpv-views');?>" data-parameter="<?php echo __('URL parameter', 'wpv-views');?>"><?php echo __('Shortcode attribute', 'wpv-views');?></label>:
							<input type="text" data-class="js-taxonomy-<?php echo $taxonomy; ?>-param" data-type="url" class="wpv_taxonomy_param js-taxonomy-param js-taxonomy-<?php echo $taxonomy; ?>-param js-wpv-filter-validate" name="taxonomy-<?php echo $taxonomy; ?>-attribute-url" value="<?php echo esc_attr($view_settings['taxonomy-' . $taxonomy . '-attribute-url']); ?>" />
						</li>
						<li>
							<?php
								if (!isset($view_settings['taxonomy-' . $taxonomy . '-attribute-operator'])) {
									$view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] = 'IN';
								}
							?>
							<label for="taxonomy-<?php echo $taxonomy; ?>-attribute-operator"><?php echo __('Operator', 'wpv-views'); ?></label>
							<select name="taxonomy-<?php echo $taxonomy; ?>-attribute-operator" id="taxonomy-<?php echo $taxonomy; ?>-attribute-operator" class="js-taxonomy-<?php echo $taxonomy; ?>-attribute-operator">
								<option value="IN"<?php if ( $view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] == 'IN' ) echo ' selected="selected"'; ?>><?php echo __('IN', 'wpv-views'); ?></option>
								<option value="NOT IN"<?php if ( $view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] == 'NOT IN' ) echo ' selected="selected"'; ?>><?php echo __('NOT IN', 'wpv-views'); ?></option>
								<option value="AND"<?php if ( $view_settings['taxonomy-' . $taxonomy . '-attribute-operator'] == 'AND' ) echo ' selected="selected"'; ?>><?php echo __('AND', 'wpv-views'); ?></option>
							</select>
						</li>
					</ul>
				</div>
			</div>
		<?php
		$buffer = ob_get_clean();
		$buffer = str_replace( 'tax_input[' . $category->name . ']', 'tax_input_' . $category->name, $buffer );
		return $buffer;
	}

	/**
	* wpv_filter_taxonomy_update_callback
	*
	* Update taxonomy filter callback
	*
	* @since unknown
	*/

	static function wpv_filter_taxonomy_update_callback() {
		$nonce = $_POST["wpnonce"];
		if ( ! wp_verify_nonce( $nonce, 'wpv_view_filter_taxonomy_nonce' ) ) {
			die( "Security check" );
		}
		$view_array = get_post_meta( $_POST["id"], '_wpv_settings', true );
		$change = false;
		parse_str( $_POST['filter_taxonomy'], $filter_taxonomy );
		foreach ( $filter_taxonomy as $filter_key => $filter_data ) {
			if ( ! isset( $view_array[$filter_key] ) || $filter_data != $view_array[$filter_key] ) {
				$change = true;
				$view_array[$filter_key] = $filter_data;
			}
		}
		if ( $change ) {
			$success = update_post_meta( $_POST["id"], '_wpv_settings', $view_array );
		}
		$summary = __( 'Select posts with taxonomy: ', 'wpv-views' );
		$result = '';
		$taxonomies = get_taxonomies( '', 'objects' );
		foreach ( $taxonomies as $category_slug => $category ) {
			$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
			if ( isset( $view_array[$relationship_name] ) ) {
				$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
				if ( ! isset( $view_array[$save_name] ) ) {
					$view_array[$save_name] = array();
				}
				$name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
				if ( $result != '' ) {
					if ( $view_array['taxonomy_relationship'] == 'OR' ) {
						$result .= __( ' OR ', 'wpv-views' );
					} else {
						$result .= __( ' AND ', 'wpv-views' );
					}
				}
				$result .= wpv_get_taxonomy_summary( $name, $view_array, $view_array[$save_name] );
			}
		}
		$summary .= $result;
		echo $summary;
		die();
	}

	

	static function wpv_filter_taxonomy_sumary_update_callback() {
		parse_str($_POST['filter_taxonomy'], $view_settings);
		$summary = __('Select posts with taxonomy: ', 'wpv-views');
		$result = '';
		$taxonomies = get_taxonomies('', 'objects');
		foreach ($taxonomies as $category_slug => $category) {
			$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
			$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';

			if (isset($view_settings[$relationship_name])) {

				if (!isset($view_settings[$save_name])) {
					$view_settings[$save_name] = array();
				}

				$name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input[' . $category->name . ']';
				if ($result != '') {
					if ($view_settings['taxonomy_relationship'] == 'OR') {
						$result .= __(' OR ', 'wpv-views');
					} else {
						$result .= __(' AND ', 'wpv-views');
					}
				}

				$result .= wpv_get_taxonomy_summary($name, $view_settings, $view_settings[$save_name]);

			}
		}

		$summary .= $result;

		echo $summary;
		die();
	}

	/**
	* wpv_filter_taxonomy_delete_callback
	*
	* Delete taxonomy filter callback
	*
	* @since unknown
	*/

	static function wpv_filter_taxonomy_delete_callback() {
		$nonce = $_POST["wpnonce"];
		if ( ! wp_verify_nonce( $nonce, 'wpv_view_filter_taxonomy_delete_nonce' ) ) {
			die( "Security check" );
		}
		$view_array = get_post_meta( $_POST["id"], '_wpv_settings', true );
		$len = isset( $view_array['filter_controls_field_name'] ) ? count( $view_array['filter_controls_field_name'] ) : 0;
		$taxonomies = is_array( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : array( $_POST['taxonomy'] );
		foreach ( $taxonomies as $taxonomy ) {
			$to_delete = array(
				'tax_' . $taxonomy . '_relationship',
				'taxonomy-' . $taxonomy . '-attribute-url',
				'taxonomy-' . $taxonomy . '-attribute-url-format',
				'taxonomy-' . $taxonomy . '-attribute-operator',
			);
			if ( 'category' == $taxonomy ) {
				$to_delete[] = 'post_category';
			} else {
				$to_delete[] = 'tax_input_' . $taxonomy;
			}
			foreach ( $to_delete as $index ) {
				if ( isset( $view_array[$index] ) ) {
					unset( $view_array[$index] );
				}
			}
			$splice = false;
			for ( $i = 0; $i < $len; $i++ ) {
				if( strpos( $view_array['filter_controls_field_name'][$i], $taxonomy ) !== false ) {
					$splice = $i;
				}
			}
			if ( $splice !== false ) {
				foreach ( Editor_addon_parametric::$prm_db_fields as $dbf ) {
					array_splice( $view_array[$dbf], $splice, 1 );
				}
			}
		}
		update_post_meta( $_POST["id"], '_wpv_settings', $view_array );
		echo $_POST['id'];
		die();
	}


	/**
	* wpv_taxonomy_summary_filter
	
	* Show the taxonomy filter on the View summary
	*
	* @since unknown
	*/

	static function wpv_taxonomy_summary_filter( $summary, $post_id, $view_settings ) {
		$result = '';
		$result = wpv_get_filter_taxonomy_summary_txt( $view_settings );
		if ( $result != '' && $summary != '' ) {
			$summary .= '<br />';
		}
		$summary .= $result;
		return $summary;
	}
	
}


function wpv_taxonomy_get_url_params( $view_settings ) {
	$results = array();
	$taxonomies = get_taxonomies( '', 'objects' );
	foreach ( $taxonomies as $category_slug => $category ) {
		$save_name = ( $category->name == 'category' ) ? 'post_category' : 'tax_input_' . $category->name;
		$relationship_name = ( $category->name == 'category' ) ? 'tax_category_relationship' : 'tax_' . $category->name . '_relationship';
		if ( isset( $view_settings[$relationship_name] ) && $view_settings[$relationship_name] == 'FROM URL' ) {
			$url_parameter = $view_settings['taxonomy-' . $category->name . '-attribute-url'];
			$results[] = array(
				'name' => $category->name,
				'param' => $url_parameter,
				'mode' => 'tax',
				'cat' => $category
			);
		}
	}
	return $results;
}
