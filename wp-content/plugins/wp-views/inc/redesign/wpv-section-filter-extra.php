<?php

/*
* We can enable this to hide the Filter HTML/CSS/JS section
* TODO if we enable this and a user enables pagination with this section hidden there can be problems
*/

add_filter('wpv_sections_filter_show_hide', 'wpv_show_hide_filter_extra', 1,1);

function wpv_show_hide_filter_extra($sections) {
	$sections['filter-extra'] = array(
		'name'		=> __('Filter HTML/CSS/JS', 'wpv-views'),
		);
	return $sections;
}

add_action('view-editor-section-filter', 'add_view_filter_parametric_search', 30, 2);

function add_view_filter_parametric_search( $view_settings, $view_id ) {
	global $views_edit_help;
	$hide = '';
	if (isset($view_settings['sections-show-hide']) && isset($view_settings['sections-show-hide']['filter-extra']) && 'off' == $view_settings['sections-show-hide']['filter-extra']) {
		$hide = ' hidden';
	}
	?>
	<div class="wpv-setting-container js-wpv-settings-container-dps-filter<?php echo $hide; ?>">
	
		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Parametric search settings', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['parametric_search']['title']; ?>" data-content="<?php echo $views_edit_help['parametric_search']['content']; ?>"></i>
			</h3>
		</div>

		<div class="wpv-setting js-wpv-dps-settings">
			<?php
			$listing = '';
			if ( isset( $view_settings['query_type'] ) && is_array( $view_settings['query_type'] ) && in_array( 'posts', $view_settings['query_type'] ) ) {
				$listing = 'posts';
			}
			?>			
			<p class="toolset-alert toolset-alert-info wpv-settings-query-type-taxonomy wpv-settings-query-type-users<?php echo $listing == 'posts' ? ' hidden' : ''; ?>">
				<?php _e('Only Views listing posts can have parametric search inputs.', 'wpv-views'); ?>
			</p>
			<div class="wpv-settings-query-type-posts<?php echo $listing == 'posts' ? '' : ' hidden'; ?>">
				<?php
				$controls_per_kind = wpv_count_filter_controls( $view_settings );
				$controls_count = 0;
				$no_intersection = array();
				$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'];
				
				if ( isset( $controls_per_kind['cf'] ) && $controls_per_kind['cf'] > 1 && ( !isset( $view_settings['custom_fields_relationship'] ) || $view_settings['custom_fields_relationship'] != 'AND' ) ) {
					$no_intersection[] = __( 'custom field', 'wpv-views' );
				}
				if ( isset( $controls_per_kind['tax'] ) && $controls_per_kind['tax'] > 1 && ( !isset( $view_settings['taxonomy_relationship'] ) || $view_settings['taxonomy_relationship'] != 'AND' ) ) {
					$no_intersection[] = __( 'taxonomy', 'wpv-views' );
				}
				
				if ( isset( $controls_per_kind['warning'] ) ) {
					?>
					<!--<p class="toolset-alert toolset-alert-info js-wpv-mismatch-parametric-search-count">
						<?php echo $controls_per_kind['warning']; ?>
					</p>-->
					<?php
				}
				
				if ( isset( $controls_per_kind['error'] ) ) {
					echo $controls_per_kind['error'];
				}
				
				if ( ! isset( $view_settings['dps'] ) ) {
					$view_settings['dps'] = array();
					$view_settings['dps']['mode_helper'] = '';
				} else {
					if ( !isset( $view_settings['dps']['mode_helper'] ) ) {
						$view_settings['dps']['mode_helper'] = 'custom';
					}
				}
				?>
				<h3><?php _e( 'How do you want to update the results?', 'wpv-views' ); ?></h3>
				<ul>
					<li>
						<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'fullrefreshonsubmit' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-fullrefreshonsubmit" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-fullrefreshonsubmit" value="fullrefreshonsubmit">
						<label for="wpv-dps-mode-helper-fullrefreshonsubmit"><?php _e( 'Full page refresh when visitors click on the search button', 'wpv-views' ); ?></label>
					</li>
					<li>
						<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'ajaxrefreshonsubmit' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-ajaxrefreshonsubmit" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-ajaxrefreshonsubmit" value="ajaxrefreshonsubmit">
						<label for="wpv-dps-mode-helper-ajaxrefreshonsubmit"><?php _e( 'AJAX results update when visitors click on the search button', 'wpv-views' ); ?></label>
					</li>
					<li>
						<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'ajaxrefreshonchange' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-ajaxrefreshonchange" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-ajaxrefreshonchange" value="ajaxrefreshonchange">
						<label for="wpv-dps-mode-helper-ajaxrefreshonchange"><?php _e( 'AJAX results update when visitors change any filter values', 'wpv-views' ); ?></label>
					</li>
					<li>
						<input type="radio" <?php checked( $view_settings['dps']['mode_helper'], 'custom' ); ?> class="js-wpv-dps-mode-helper js-wpv-dps-mode-helper-custom" name="wpv-dps-mode-helper" id="wpv-dps-mode-helper-custom" value="custom">
						<label for="wpv-dps-mode-helper-custom"><?php _e( 'Let me choose individual settings manually', 'wpv-views' ); ?></label>
					</li>
				</ul>
				<div class="wpv-advanced-setting js-wpv-ps-settings-custom"<?php if ( $view_settings['dps']['mode_helper'] != 'custom' ) { echo ' style="display:none"'; } ?>>
					<h4><?php _e('When to update the Views results', 'wpv-views'); ?></h4>
					<ul>
						<?php
						if ( ! isset( $view_settings['dps']['ajax_results'] ) ) {
							$view_settings['dps']['ajax_results'] = '';
						}
						?>
						<li>
							<input type="radio" <?php checked( $view_settings['dps']['ajax_results'], 'disable' ); ?> value="disable" id="wpv-dps-ajax-results-disable" class="js-wpv-dps-ajax-results js-wpv-dps-ajax-results-disable" name="wpv-dps-ajax-results" />
							<label for="wpv-dps-ajax-results-disable"><?php _e('Update the View results only when clicking on the search button', 'wpv-views'); ?></label>
							<div class="wpv-setting-extra js-wpv-dps-ajax-results-extra js-wpv-dps-ajax-results-extra-disable"<?php if ( $view_settings['dps']['ajax_results'] != 'disable' ) { echo 'style="display:none"'; } ?>>
								<?php
								if ( !isset( $view_settings['dps']['ajax_results_submit'] ) ) {
									$view_settings['dps']['ajax_results_submit'] = '';
								}
								?>
								<p>
								<ul>
									<li>
										<input type="radio" <?php checked( $view_settings['dps']['ajax_results_submit'], 'ajaxed' ); ?> name="wpv-dps-ajax-results-submit" id="wpv-ajax-results-submit-ajaxed" class="js-wpv-ajax-results-submit js-wpv-ajax-results-submit-ajaxed" value="ajaxed" />
										<label for="wpv-ajax-results-submit-ajaxed"><?php _e('Update the Views results without reloading the page', 'wpv-views'); ?></label>
									</li>
									<li>
										<input type="radio" <?php checked( $view_settings['dps']['ajax_results_submit'], 'reload' ); ?> name="wpv-dps-ajax-results-submit" id="wpv-ajax-results-submit-reload" class="js-wpv-ajax-results-submit js-wpv-ajax-results-submit-reload" value="reload" />
										<label for="wpv-ajax-results-submit-reload"><?php _e('Reload the page to update the View results', 'wpv-views'); ?></label>
									</li>
								</ul>
								</p>
							</div>
						</li>
						<li>
							<input type="radio" <?php checked( $view_settings['dps']['ajax_results'], 'enable' ); ?> value="enable" id="wpv-dps-ajax-results-enable" class="js-wpv-dps-ajax-results js-wpv-dps-ajax-results-enable" name="wpv-dps-ajax-results" />
							<label for="wpv-dps-ajax-results-enable"><?php _e('Update the View results every time an input changes', 'wpv-views'); ?></label>
						</li>
					</ul>
					<div class="wpv-ajax-results-details js-wpv-ajax-extra-callbacks"<?php if ( $view_settings['dps']['ajax_results'] != 'enable' && $view_settings['dps']['ajax_results_submit'] == 'reload' ) { echo ' style="display:none"'; } ?>>
						<h4><?php _e('Javascript settings', 'wpv-views'); ?></h4>
						<p>
							<?php _e('You can execute custom javascript functions before and after the View results are updated:', 'wpv-views'); ?>
						</p>
						<ul>
							<li>
								<input type="text" id="wpv-dps-ajax-results-pre-before" class="js-wpv-dps-ajax-results-pre-before" name="wpv-dps-ajax-results-pre-before" value="<?php echo ( isset( $view_settings['dps']['ajax_results_pre_before'] ) ) ? esc_attr( $view_settings['dps']['ajax_results_pre_before'] ) : ''; ?>" />
								<label for="wpv-dps-ajax-results-pre-before"><?php _e('will run before getting the new results', 'wpv-views'); ?></label>
							</li>
							<li>
								<input type="text" id="wpv-dps-ajax-results-before" class="js-wpv-dps-ajax-results-before" name="wpv-dps-ajax-results-before" value="<?php echo ( isset( $view_settings['dps']['ajax_results_before'] ) ) ? esc_attr( $view_settings['dps']['ajax_results_before'] ) : ''; ?>" />
								<label for="wpv-dps-ajax-results-before"><?php _e('will run after getting the new results, but before updating them', 'wpv-views'); ?></label>
							</li>
							<li>
								<input type="text" id="wpv-dps-ajax-results-after" class="js-wpv-dps-ajax-results-after" name="wpv-dps-ajax-results-after" value="<?php echo ( isset( $view_settings['dps']['ajax_results_after'] ) ) ? esc_attr( $view_settings['dps']['ajax_results_after'] ) : ''; ?>" />
								<label for="wpv-dps-ajax-results-after"><?php _e('will run after updating the results', 'wpv-views'); ?></label>
							</li>
						</ul>
						
					</div>
					<h4><?php _e('Which options to display in the form inputs', 'wpv-views'); ?></h4>
					<?php
					if ( ! isset( $view_settings['dps']['enable_dependency'] ) ) {
						$view_settings['dps']['enable_dependency'] = '';
					}
					?>
					<p class="toolset-alert toolset-alert-info js-wpv-dps-intersection-fail<?php if ( count( $no_intersection ) == 0 ) echo ' hidden'; ?>">
						<?php
						$glue = __( ' and ', 'wpv-views' );
						$no_intersection_text = implode( $glue , $no_intersection );
						echo sprintf( __( 'Your %s filters are using an internal "OR" kind of relationship, and dependant parametric search for those filters needs "AND" relationships.', 'wpv-views' ), $no_intersection_text );
						?>
						<br /><br />
						<button class="button-secondary js-make-intersection-filters" data-nonce="<?php echo wp_create_nonce( 'wpv_view_make_intersection_filters' ); ?>" data-cf="<?php echo ( in_array( 'cf', $no_intersection ) ) ? 'true' : 'false'; ?>" data-tax="<?php echo ( in_array( 'tax', $no_intersection ) ) ? 'true' : 'false'; ?>">
							<?php _e('Fix filters relationship', 'wpv-views'); ?>
						</button>
					</p>
					<div class="js-wpv-dps-intersection-ok<?php if ( count( $no_intersection ) > 0 ) echo ' hidden'; ?>">
						<ul>
							<li>
								<input type="radio" <?php checked( $view_settings['dps']['enable_dependency'], 'disable' ); ?> value="disable" id="wpv-dps-enable-disable" class="js-wpv-dps-enable js-wpv-dps-enable-disable" name="wpv-dps-enable" />
								<label for="wpv-dps-enable-disable"><?php _e('Always show all values for inputs', 'wpv-views'); ?></label>
							</li>
							<li>
								<input type="radio" <?php checked( $view_settings['dps']['enable_dependency'], 'enable' ); ?> value="enable" id="wpv-dps-enable-enable" class="js-wpv-dps-enable js-wpv-dps-enable-enable" name="wpv-dps-enable" />
								<label for="wpv-dps-enable-enable"><?php _e('Show only available options for each input', 'wpv-views'); ?></label>
							</li>
						</ul>
						<div class="wpv-dps-crossed-details js-wpv-dps-crossed-details"<?php if ( $view_settings['dps']['enable_dependency'] != 'enable' ) { echo ' style="display:none"'; } ?>>
							<p>
								<?php _e('Choose if you want to hide or disable irrelevant options for inputs:', 'wpv-views'); ?>
							</p>
							<table class="widefat">
								<thead>
									<tr>
										<th>
											<?php _e('Input type', 'wpv-views'); ?>
										</th>
										<th>
											<?php _e('Disable / Hide', 'wpv-views'); ?>
										</th>
									</tr>
								</thead>
								<tbody>
									<tr class="alternate">
										<?php 
										if ( ! isset( $view_settings['dps']['empty_select'] ) ) {
											$view_settings['dps']['empty_select'] = '';
										}
										?>
										<td>
											<?php _e('Select dropdown', 'wpv-views'); ?>
										</td>
										<td>
											<input type="radio" <?php checked( $view_settings['dps']['empty_select'], 'disable' ); ?> id="wpv-dps-empty-select-disable" value="disable" class="js-wpv-dps-empty-select" name="wpv-dps-empty-select" />
											<label for="wpv-dps-empty-select-disable"><?php _e('Disable', 'wpv-views'); ?></label>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" <?php checked( $view_settings['dps']['empty_select'], 'hide' ); ?> id="wpv-dps-empty-select-hide" value="hide" class="js-wpv-dps-empty-select" name="wpv-dps-empty-select" />
											<label for="wpv-dps-empty-select-hide"><?php _e('Hide', 'wpv-views'); ?></label>
										</td>
									</tr>
									<tr>
										<?php 
										if ( ! isset( $view_settings['dps']['empty_multi_select'] ) ) {
											$view_settings['dps']['empty_multi_select'] = '';
										}
										?>
										<td>
											<?php _e('Multi-select', 'wpv-views'); ?>
										</td>
										<td>
											<input type="radio" <?php checked( $view_settings['dps']['empty_multi_select'], 'disable' ); ?> id="wpv-dps-empty-multi-select-disable" value="disable" class="js-wpv-dps-empty-multi-select" name="wpv-dps-empty-multi-select" />
											<label for="wpv-dps-empty-multi-select-disable"><?php _e('Disable', 'wpv-views'); ?></label>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" <?php checked( $view_settings['dps']['empty_multi_select'], 'hide' ); ?> id="wpv-dps-empty-multi-select-hide" value="hide" class="js-wpv-dps-empty-multi-select" name="wpv-dps-empty-multi-select" />
											<label for="wpv-dps-empty-multi-select-hide"><?php _e('Hide', 'wpv-views'); ?></label>
										</td>
									</tr>
									<tr class="alternate">
										<?php 
										if ( ! isset( $view_settings['dps']['empty_radios'] ) ) {
											$view_settings['dps']['empty_radios'] = '';
										}
										?>
										<td>
											<?php _e('Radio inputs', 'wpv-views'); ?>
										</td>
										<td>
											<input type="radio" <?php checked( $view_settings['dps']['empty_radios'], 'disable' ); ?> id="wpv-dps-empty-radios-disable" value="disable" class="js-wpv-dps-empty-radios" name="wpv-dps-empty-radios" />
											<label for="wpv-dps-empty-radios-disable"><?php _e('Disable', 'wpv-views'); ?></label>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" <?php checked( $view_settings['dps']['empty_radios'], 'hide' ); ?> id="wpv-dps-empty-radios-hide" value="hide" class="js-wpv-dps-empty-radios" name="wpv-dps-empty-radios" />
											<label for="wpv-dps-empty-radios-hide"><?php _e('Hide', 'wpv-views'); ?></label>
										</td>
									</tr>
									<tr>
										<?php 
										if ( ! isset( $view_settings['dps']['empty_checkboxes'] ) ) {
											$view_settings['dps']['empty_checkboxes'] = '';
										}
										?>
										<td>
											<?php _e('Checkboxes', 'wpv-views'); ?>
										</td>
										<td>
											<input type="radio" <?php checked( $view_settings['dps']['empty_checkboxes'], 'disable' ); ?> id="wpv-dps-empty-checkboxes-disable" value="disable" class="js-wpv-dps-empty-checkboxes" name="wpv-dps-empty-checkboxes" />
											<label for="wpv-dps-empty-checkboxes-disable"><?php _e('Disable', 'wpv-views'); ?></label>
											&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
											<input type="radio" <?php checked( $view_settings['dps']['empty_checkboxes'], 'hide' ); ?> id="wpv-dps-empty-checkboxes-hide" value="hide" class="js-wpv-dps-empty-checkboxes" name="wpv-dps-empty-checkboxes" />
											<label for="wpv-dps-empty-checkboxes-hide"><?php _e('Hide', 'wpv-views'); ?></label>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div><!-- end .js-wpv-dps-settings -->
		<span class="update-action-wrap auto-update">
			<input type="hidden" data-success="<?php echo esc_attr( __('Updated', 'wpv-views'), ENT_QUOTES ); ?>" data-unsaved="<?php echo esc_attr( __('Not saved', 'wpv-views'), ENT_QUOTES ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_dps_nonce' ); ?>" class="js-wpv-filter-dps-update">
		</span>
	</div>
	<?php
}

add_action('view-editor-section-filter', 'add_view_filter_extra', 35, 2);

function add_view_filter_extra($view_settings, $view_id) {
    global $views_edit_help;
	$hide = '';
	if (isset($view_settings['sections-show-hide']) && isset($view_settings['sections-show-hide']['filter-extra']) && 'off' == $view_settings['sections-show-hide']['filter-extra']) {
		$hide = ' hidden';
	}
	?>
	<div class="wpv-setting-container wpv-setting-container-horizontal wpv-settings-filter-markup js-wpv-settings-filter-extra<?php echo $hide; ?>">

		<div class="wpv-settings-header">
			<h3>
				<?php _e( 'Filter HTML/CSS/JS', 'wpv-views' ) ?>
				<i class="icon-question-sign js-display-tooltip" data-header="<?php echo $views_edit_help['filters_html_css_js']['title']; ?>" data-content="<?php echo $views_edit_help['filters_html_css_js']['content']; ?>"></i>
			</h3>
		</div>
		<?php
		$listing = '';
		if ( isset( $view_settings['query_type'] ) && is_array( $view_settings['query_type'] ) && in_array( 'posts', $view_settings['query_type'] ) ) {
			$listing = 'posts';
		}
		$controls_per_kind = wpv_count_filter_controls( $view_settings );
		if ( isset( $controls_per_kind['missing'] ) && is_array( $controls_per_kind['missing'] ) && !empty( $controls_per_kind['missing'] ) ) {
		?>
		<div class="toolset-help js-wpv-missing-filter-container wpv-settings-query-type-posts<?php echo $listing == 'posts' ? '' : ' hidden'; ?>">
			<div class="toolset-help-content">
				<?php
				_e( 'This View has some query filters that are missing from the form. Maybe you have removed them:', 'wpv-views' );
				?>
				<ul class="js-wpv-filter-missing">
				<?php
				foreach ( $controls_per_kind['missing'] as $missed ) {
					?>
					<li class="js-wpv-missing-filter" data-type="<?php echo $missed['type']; ?>" data-name="<?php echo $missed['name']; ?>">
						<?php
						echo sprintf( __( 'Filter by <strong>%s</strong>', 'wpv-views' ), $missed['name'] );
						?>
					</li>
					<?php
				}
				?>
				</ul>
				<?php
				_e( 'Can they also be removed from the query filtering?', 'wpv-views' );
				?>
				<p>
					<a href="#" class="button button-primary js-wpv-filter-missing-delete" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_missing_delete' ); ?>"><?php _e( 'Yes (recommended)', 'wpv-views' ); ?></a> <a href="#" class="button button-secondary js-wpv-filter-missing-close"><?php _e( 'No', 'wpv-views' ); ?></a>
				</p>
			</div>
			<div class="toolset-help-sidebar">
				<div class="toolset-help-sidebar-ico"></div>
			</div>
		</div>
		<?php
		} else {
		?>
		<div class="toolset-help js-wpv-missing-filter-container wpv-settings-query-type-posts hidden"></div>
		<?php
		}
		
		$controls_count = 0;
		$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'];
		if ( $controls_count == 0 ) {
		?>
		<div class="toolset-alert js-wpv-no-filters-container js-display-view-howto js-for-view-purpose-parametric wpv-settings-query-type-posts<?php echo $listing == 'posts' ? '' : ' hidden'; ?>">
			<p>
				<?php _e('Remember to add filters here. Right now, this parametric search has no filter items.', 'wpv-views'); ?>
			</p>
		</div>
		<?php
		}
		?>

		<div class="wpv-setting">

			<!-- <div class="js-error-container"></div> -->
			<div class="code-editor js-code-editor filter-html-editor" data-name="filter-html-editor" >
				<div class="code-editor-toolbar js-code-editor-toolbar">
					<ul class="js-wpv-filter-edit-toolbar">
						<?php echo apply_filters('wpv_meta_html_add_form_button_new', '', '#wpv_filter_meta_html_content'); ?>
						<li class="wpv-vicon-codemirror-button">
							<?php wpv_add_v_icon_to_codemirror( 'wpv_filter_meta_html_content' ); ?>
						</li>
						<li class="js-editor-pagination-button-wrapper">
							<button class="button-secondary js-code-editor-toolbar-button js-wpv-pagination-popup" data-content="wpv_filter_meta_html_content">
								<i class="icon-pagination"></i>
								<span class="button-label"><?php _e('Pagination controls','wpv-views'); ?></span>
							</button>
						</li>
						<li>
							<button class="button-secondary js-code-editor-toolbar-button js-wpv-media-manager" data-id="<?php echo $view_id;?>" data-content="wpv_filter_meta_html_content">
								<i class="icon-picture"></i>
								<span class="button-label"><?php _e('Media','wpv-views'); ?></span>
							</button>
						</li>
					</ul>

				</div>

				<textarea cols="30" rows="10" id="wpv_filter_meta_html_content" autocomplete="off" name="_wpv_settings[filter_meta_html]"><?php echo ( isset( $view_settings['filter_meta_html'] ) ) ? $view_settings['filter_meta_html'] : ''; ?></textarea>
				
			</div>
			
			<?php 
			$filter_extra_css = isset( $view_settings['filter_meta_html_css'] ) ? $view_settings['filter_meta_html_css'] : '';
			?>

			<p class="js-wpv-filter-css-editor-old-place">
				<input type="hidden" name="_wpv_settings[filter_meta_html_state][css]" id="wpv_filter_meta_html_extra_css_state" value="<?php echo isset($view_settings['filter_meta_html_state']['css']) ? $view_settings['filter_meta_html_state']['css'] : 'off'; ?>" />
				<button class="button-secondary js-code-editor-button filter-css-editor-button" data-target="filter-css-editor" data-state="closed" data-closed="<?php echo htmlentities( __( 'Open CSS editor', 'wpv-views' ), ENT_QUOTES ); ?>" data-opened="<?php echo htmlentities( __( 'Close CSS editor', 'wpv-views' ), ENT_QUOTES ); ?>">
					<span class="js-wpv-textarea-button-label"><?php _e( 'Open CSS editor', 'wpv-views' ) ?></span>
					<i class="icon-reorder js-wpv-textarea-full" style="margin-left:5px;color:green;<?php if ( empty( $filter_extra_css ) ) { echo ' display:none;'; } ?>"></i>
				</button>
			</p>

			<div class="js-code-editor code-editor filter-css-editor closed" data-name="filter-css-editor">
				<div class="code-editor-toolbar js-code-editor-toolbar">
					<ul>
						<li>
							<button class="button-secondary js-code-editor-toolbar-button js-wpv-media-manager" data-id="<?php echo $view_id;?>" data-content="wpv_filter_meta_html_css">
								<i class="icon-picture"></i>
								<span class="button-label"><?php _e('Media','wpv-views'); ?></span>
							</button>
						</li>
					</ul>
				</div>
				<textarea cols="30" rows="10" id="wpv_filter_meta_html_css" autocomplete="off" name="_wpv_settings[filter_meta_html_css]"><?php echo $filter_extra_css; ?></textarea>
			</div>
			
			<?php 
			$filter_extra_js = isset( $view_settings['filter_meta_html_js'] ) ? $view_settings['filter_meta_html_js'] : '';
			?>

			<p class="js-wpv-filter-js-editor-old-place">
				<input type="hidden" name="_wpv_settings[filter_meta_html_state][js]" id="wpv_filter_meta_html_extra_js_state" value="<?php echo isset($view_settings['filter_meta_html_state']['js']) ? $view_settings['filter_meta_html_state']['js'] : 'off'; ?>" />
				<button class="button-secondary js-code-editor-button filter-js-editor-button" data-target="filter-js-editor"  data-state="closed" data-closed="<?php echo htmlentities( __( 'Open JS editor', 'wpv-views' ), ENT_QUOTES ); ?>" data-opened="<?php echo htmlentities( __( 'Close JS editor', 'wpv-views' ), ENT_QUOTES ); ?>">
					<span class="js-wpv-textarea-button-label"><?php _e( 'Open JS editor', 'wpv-views' ) ?></span>
					<i class="icon-reorder js-wpv-textarea-full" style="margin-left:5px;color:green;<?php if ( empty( $filter_extra_js ) ) { echo ' display:none;'; } ?>"></i>
				</button>
			</p>

			<div class="js-code-editor code-editor filter-js-editor closed" data-name="filter-js-editor" >
				<div class="code-editor-toolbar js-code-editor-toolbar">
					<ul>
						<li>
							<button class="button-secondary js-code-editor-toolbar-button js-wpv-media-manager" data-id="<?php echo $view_id;?>" data-content="wpv_filter_meta_html_js">
								<i class="icon-picture"></i>
								<span class="button-label"><?php _e('Media','wpv-views'); ?></span>
							</button>
						</li>
					</ul>
				</div>
				<textarea cols="30" rows="10" id="wpv_filter_meta_html_js" autocomplete="off" name="_wpv_settings[filter_meta_html_js]"><?php echo $filter_extra_js; ?></textarea>
			</div>

			<p class="update-button-wrap">
				<button data-success="<?php echo htmlentities( __('Updated', 'wpv-views'), ENT_QUOTES ); ?>" data-unsaved="<?php echo htmlentities( __('Not saved', 'wpv-views'), ENT_QUOTES ); ?>" data-nonce="<?php echo wp_create_nonce( 'wpv_view_filter_extra_nonce' ); ?>" class="js-wpv-filter-extra-update button-secondary" disabled="disabled"><?php _e('Update', 'wpv-views'); ?></button>
			</p>
		</div>

	</div>
	
	<div class="popup-window-container"> <!-- Use this element as a container for all popup windows. This element is hidden. -->

		<div class="wpv-dialog wpv-dialog-dependant-wizard js-dependant-form-dialog"> <!-- Popup when the dependant parametric search is allowed --><!-- DEPRECATED -->
			<div class="wpv-dialog-header">
				<h2><?php _e('Would you like to make this parametric search a dependant one?','wpv-views') ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
		</div>
		
		<div class="wpv-dialog wpv-dialog-parametric-filter wpv-dialog-submit-button js-submit-button-dialog"> <!-- Popup for the submit button addition -->
			
			<div class="wpv-dialog-header">
				<h2><?php _e( 'Create a submit button for this parametric search.', 'wpv-views' ); ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			
			<div class="js-submit_shortcode_label-wrap wpv-dialog-content">
				<p>
					<label for="submit_shortcode_label" class="label-alignleft"><?php _e('Button label:', 'wpv-views'); ?></label>
					<input value="<?php echo esc_attr( __('Search', 'wpv-views') ); ?>" id="submit_shortcode_label" type="text">
					<span class="helper-text">lorem</span>
				</p>
				<p>
					<label for="submit_shortcode_button_classname" class="label-alignleft"><?php _e('Button classname:', 'wpv-views'); ?></label>
					<input value="" id="submit_shortcode_button_classname" type="text">
					<span class="helper-text">lorem</span>
				</p>
			</div>
			
			<div class="js-errors-in-parametric-box"></div>
			
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close" id="js_parametric_cancel"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button-primary js-code-editor-toolbar-button js-parametric-add-submit-short-tag-label"><?php _e('Insert submit button','wpv-views') ?></button>
			</div>
			
		</div> <!-- End of popup for the submit button addition -->
		
		<div class="wpv-dialog wpv-dialog-parametric-filter wpv-dialog-reset-button js-reset-button-dialog"> <!-- Popup for the reset button addition -->
			
			<div class="wpv-dialog-header">
				<h2><?php _e( 'Create a reset button for this parametric search.', 'wpv-views' ); ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			
			<div class="js-reset_shortcode_label-wrap wpv-dialog-content">
				<p>
					<label for="reset_shortcode_label" class="label-alignleft"><?php _e('Button label:', 'wpv-views'); ?></label>
					<input value="<?php echo esc_attr( __('Reset', 'wpv-views') ); ?>" id="reset_shortcode_label" type="text">
					<span class="helper-text">lorem</span>
				</p>
				<p>
					<label for="reset_shortcode_button_classname" class="label-alignleft"><?php _e('Button classname:', 'wpv-views'); ?></label>
					<input value="" id="reset_shortcode_button_classname" type="text">
					<span class="helper-text">lorem</span>
				</p>
			</div>
			
			<div class="js-errors-in-parametric-box"></div>
			
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close" id="js_parametric_cancel"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button-primary js-code-editor-toolbar-button js-parametric-add-reset-short-tag-label"><?php _e('Insert clear form button','wpv-views') ?></button>
			</div>
			
		</div> <!-- End of popup for the reset button addition -->
		
		<div class="wpv-dialog wpv-dialog-parametric-filter wpv-dialog-spinner-button js-spinner-button-dialog"> <!-- Popup for the spinner button addition -->
			
			<div class="wpv-dialog-header">
				<h2><?php _e( 'Create a spinner container for this parametric search.', 'wpv-views' ); ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			
			<div class="js-spinner_shortcode_label-wrap wpv-dialog-content">
				<p>
					<label for="spinner_shortcode_container_type" class="label-alignleft"><?php _e('Container type:', 'wpv-views'); ?></label>
					<select id="spinner_shortcode_container_type">
						<option value="div"><?php _e('Division', 'wpv-views'); ?></option>
						<option value="p"><?php _e('Paragraph', 'wpv-views'); ?></option>
						<option value="span"><?php _e('Span', 'wpv-views'); ?></option>
					</select>
					<span class="helper-text">lorem</span>

					<label for="spinner_shortcode_container_classname" class="label-alignleft"><?php _e('Container classname:', 'wpv-views'); ?></label>
					<input value="" id="spinner_shortcode_container_classname" type="text">
					<span class="helper-text">lorem</span>

					<label for="spinner_shortcode_spinner_position" class="label-alignleft"><?php _e('Spinner placement:', 'wpv-views'); ?></label>
					<select id="spinner_shortcode_spinner_position">
						<option value="none"><?php _e('Do not show the spinner', 'wpv-views'); ?></option>
						<option value="before"><?php _e('Before the text', 'wpv-views'); ?></option>
						<option value="after"><?php _e('After the text', 'wpv-views'); ?></option>
					</select>
					<span class="helper-text">lorem</span>

					<label for="spinner_shortcode_spinner_image" class="label-alignleft"><?php _e('Spinner image:', 'wpv-views'); ?></label>
					<ul style="overflow:hidden">
					<?php
					foreach ( glob( WPV_PATH_EMBEDDED . "/res/img/ajax-loader*" ) as $file ) {
						$filename = WPV_URL_EMBEDDED . '/res/img/' . basename( $file );
						?>
						<li style="min-width:49%;float:left;">
							<label>
								<input type="radio" class="js-wpv-ps-spinner-image" name="wpv-dps-spinner-image" value="<?php echo $filename; ?>" />
								<img src="<?php echo $filename; ?>" title="<?php echo $filename; ?>" />
							</label>
						</li>
					<?php } ?>
					</ul>
					<span class="helper-text">lorem</span>

					<label for="spinner_shortcode_content" class="label-alignleft"><?php _e('Container text:','wpv-views'); ?></label>
					<textarea id="spinner_shortcode_content"></textarea>
					<span class="helper-text">lorem</span>
				</p>
			</div>
			
			<div class="js-errors-in-parametric-box"></div>
			
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close" id="js_parametric_cancel"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button-primary js-code-editor-toolbar-button js-parametric-add-spinner-short-tag-label"><?php _e('Insert spinner container','wpv-views') ?></button>
			</div>
			
		</div> <!-- End of popup for the spinner button addition -->
	
	</div><!-- popup-window-container end -->
<?php }

add_action('wp_ajax_wpv_filter_update_dps_settings', 'wpv_filter_update_dps_settings');

function wpv_filter_update_dps_settings() {
	$nonce = $_POST["nonce"];
	if (! wp_verify_nonce( $nonce, 'wpv_view_filter_dps_nonce' ) ) die( "Security check" );
	$view_array = get_post_meta( $_POST["id"], '_wpv_settings', true );
	if ( !isset( $view_array['dps'] ) ) {
		$view_array['dps'] = array();
	}
	if ( isset( $_POST['dpsdata'] ) ) {
		$passed_data = wp_parse_args( $_POST['dpsdata'] );
		// Helper mode
		if ( isset( $passed_data['wpv-dps-mode-helper'] ) && in_array( $passed_data['wpv-dps-mode-helper'], array( 'fullrefreshonsubmit', 'ajaxrefreshonsubmit', 'ajaxrefreshonchange', 'custom' ) ) ) {
			$view_array['dps']['mode_helper'] = $passed_data['wpv-dps-mode-helper'];
		}
		// AJAX update View results
		if ( isset( $passed_data['wpv-dps-ajax-results'] ) && $passed_data['wpv-dps-ajax-results'] == 'enable' ) {
			$view_array['dps']['ajax_results'] = 'enable';
		} else {
			$view_array['dps']['ajax_results'] = 'disable';
		}
		if ( isset( $passed_data['wpv-dps-ajax-results-pre-before'] ) ) {
			$view_array['dps']['ajax_results_pre_before'] = esc_attr( $passed_data['wpv-dps-ajax-results-pre-before'] );
		} else {
			$view_array['dps']['ajax_results_pre_before'] = '';
		}
		if ( isset( $passed_data['wpv-dps-ajax-results-before'] ) ) {
			$view_array['dps']['ajax_results_before'] = esc_attr( $passed_data['wpv-dps-ajax-results-before'] );
		} else {
			$view_array['dps']['ajax_results_before'] = '';
		}
		if ( isset( $passed_data['wpv-dps-ajax-results-after'] ) ) {
			$view_array['dps']['ajax_results_after'] = esc_attr( $passed_data['wpv-dps-ajax-results-after'] );
		} else {
			$view_array['dps']['ajax_results_after'] = '';
		}
		if ( isset( $passed_data['wpv-dps-ajax-results-submit'] ) && in_array( $passed_data['wpv-dps-ajax-results-submit'], array( 'ajaxed', 'reload' ) ) ) {
			$view_array['dps']['ajax_results_submit'] = $passed_data['wpv-dps-ajax-results-submit'];
		} else {
			$view_array['dps']['ajax_results_submit'] = 'reload';
		}
		// Enable dependency and input defaults
		if ( isset( $passed_data['wpv-dps-enable'] ) && $passed_data['wpv-dps-enable'] == 'disable' ) {
			$view_array['dps']['enable_dependency'] = 'disable';
		} else {
			$view_array['dps']['enable_dependency'] = 'enable';
		}
		if ( isset( $passed_data['wpv-dps-empty-select'] ) && $passed_data['wpv-dps-empty-select'] == 'disable' ) {
			$view_array['dps']['empty_select'] = 'disable';
		} else {
			$view_array['dps']['empty_select'] = 'hide';
		}
		if ( isset( $passed_data['wpv-dps-empty-multi-select'] ) && $passed_data['wpv-dps-empty-multi-select'] == 'disable' ) {
			$view_array['dps']['empty_multi_select'] = 'disable';
		} else {
			$view_array['dps']['empty_multi_select'] = 'hide';
		}
		if ( isset( $passed_data['wpv-dps-empty-radios'] ) && $passed_data['wpv-dps-empty-radios'] == 'disable' ) {
			$view_array['dps']['empty_radios'] = 'disable';
		} else {
			$view_array['dps']['empty_radios'] = 'hide';
		}
		if ( isset( $passed_data['wpv-dps-empty-checkboxes'] ) && $passed_data['wpv-dps-empty-checkboxes'] == 'disable' ) {
			$view_array['dps']['empty_checkboxes'] = 'disable';
		} else {
			$view_array['dps']['empty_checkboxes'] = 'hide';
		}
		/*
		Spinners - DEPRECATED, so we might want to clean; keep it for now for backwards compatibility
		$view_array['dps']['spinner'] = 'none';
		$view_array['dps']['spinner_image_uploaded'] = '';
		$view_array['dps']['spinner_image'] = '';
		*/
	} else {
		
	}
	update_post_meta( $_POST["id"], '_wpv_settings', $view_array );
	echo $_POST['id'];
	die();
}

add_action( 'wp_ajax_wpv_get_dps_related', 'wpv_get_dps_related' );

function wpv_get_dps_related() {
	$nonce = $_POST["nonce"];
	if ( ! wp_verify_nonce( $nonce, 'wpv_view_edit_general_nonce' ) ) {
		die( "Security check" );
	}
	$return_result = array(
		'existence' => '',
		'intersection' => '',
		'missing' => ''
	);
	if ( isset( $_POST['id'] ) ) {
		global $WP_Views;
		$view_id = (int) $_POST['id'];
		$view_settings = $WP_Views->get_view_settings( $view_id );
		$controls_per_kind = wpv_count_filter_controls( $view_settings );
		$controls_count = $controls_per_kind['cf'] + $controls_per_kind['tax'] + $controls_per_kind['pr'];
		$no_intersection = array();				
		if ( isset( $controls_per_kind['cf'] ) && $controls_per_kind['cf'] > 1 && ( !isset( $view_settings['custom_fields_relationship'] ) || $view_settings['custom_fields_relationship'] != 'AND' ) ) {
			$no_intersection[] = __( 'custom field', 'wpv-views' );
		}
		if ( isset( $controls_per_kind['tax'] ) && $controls_per_kind['tax'] > 1 && ( !isset( $view_settings['taxonomy_relationship'] ) || $view_settings['taxonomy_relationship'] != 'AND' ) ) {
			$no_intersection[] = __( 'taxonomy', 'wpv-views' );
		}
		// Existence
		if ( $controls_count == 0 ) {
			$return_result['existence'] = '<p>' . __('Remember to add filters here. Right now, this parametric search has no filter items.', 'wpv-views') . '</p>';
		}
		// Intersection
		if ( count( $no_intersection ) > 0 ) {
			$glue = __( ' and ', 'wpv-views' );
			$no_intersection_text = implode( $glue , $no_intersection );
			$return_result['intersection'] = sprintf( __( 'Your %s filters are using an internal "OR" kind of relationship, and dependant parametric search for those filters needs "AND" relationships.', 'wpv-views' ), $no_intersection_text );
			$return_result['intersection'] .= '<br /><br />';
			$return_result['intersection'] .= '<button class="button-secondary js-make-intersection-filters" data-nonce="' . wp_create_nonce( 'wpv_view_make_intersection_filters' ) .'"';
			if ( in_array( 'cf', $no_intersection ) ) {
				$return_result['intersection'] .= ' data-cf="true"';
			} else {
				$return_result['intersection'] .= ' data-cf="false"';
			}
			if ( in_array( 'tax', $no_intersection ) ) {
				$return_result['intersection'] .= ' data-tax="true"';
			} else {
				$return_result['intersection'] .= ' data-tax="false"';
			}
			$return_result['intersection'] .= '>';
				$return_result['intersection'] .= __('Fix filters relationship', 'wpv-views');
			$return_result['intersection'] .= '</button>';
		}
		// Missing
		if ( isset( $controls_per_kind['missing'] ) && is_array( $controls_per_kind['missing'] ) && !empty( $controls_per_kind['missing'] ) ) {
			$return_result['missing'] = '<div class="toolset-help-content">';
			$return_result['missing'] .= __( 'This View has some query filters that are missing from the form. Maybe you have removed them:', 'wpv-views' );
			$return_result['missing'] .= '<ul class="js-wpv-filter-missing">';
			foreach ( $controls_per_kind['missing'] as $missed ) {
				$return_result['missing'] .= '<li class="js-wpv-missing-filter" data-type="' . $missed['type'] . '" data-name="' . $missed['name'] . '">';
				$return_result['missing'] .= sprintf( __( 'Filter by <strong>%s</strong>', 'wpv-views' ), $missed['name'] );
				$return_result['missing'] .= '</li>';
			}
			$return_result['missing'] .= '</ul>';
			$return_result['missing'] .= __( 'Can they also be removed from the query filtering?', 'wpv-views' );
			$return_result['missing'] .= '<p>';
				$return_result['missing'] .= '<a href="#" class="button button-primary js-wpv-filter-missing-delete" data-nonce="' . wp_create_nonce( 'wpv_view_filter_missing_delete' ) . '">' . __( 'Yes (recommended)', 'wpv-views' ) . '</a> <a href="#" class="button button-secondary js-wpv-filter-missing-close">' . __( 'No', 'wpv-views' ) . '</a>';
			$return_result['missing'] .= '</p>';
			$return_result['missing'] .= '</div>';
			$return_result['missing'] .= '<div class="toolset-help-sidebar"><div class="toolset-help-sidebar-ico"></div></div>';
		}
	}
	echo json_encode( $return_result );
	die();
}
