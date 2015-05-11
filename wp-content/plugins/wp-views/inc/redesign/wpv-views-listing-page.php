<?php

/**
* wpv_admin_menu_views_listing_page
*
* Creates the main structure of the Views admin listing page: wrapper and header
*
*/

function wpv_admin_menu_views_listing_page() {
	?>
	<div class="wrap toolset-views">

		<div class="wpv-views-listing-page">
			<?php
				// $has_views holds an array with all the Views IDs or false if there isn't any
				$has_views = wpv_check_views_exists('normal');

				$search_term = isset( $_GET["s"] ) ? urldecode( sanitize_text_field( $_GET["s"] ) ) : '';

				// general nonce
				// TODO please do NOT use this general nonce
				wp_nonce_field( 'work_views_listing', 'work_views_listing' );
			?>
			<div id="icon-views" class="icon32"></div>
			<h2><!-- classname wpv-page-title removed -->
				<?php
					_e('Views', 'wpv-views');
					if ( $has_views ) {
						printf( '<a href="#" class="add-new-h2 js-wpv-views-add-new-top">%s</a>', __( 'Add new View', 'wpv-views' ) );
					}

					// TODO maybe have this nonce as a data attribute for all buttons opening the popup
					wp_nonce_field('wp_nonce_create_view_wrapper', 'wp_nonce_create_view_wrapper');

					if ( !empty( $search_term ) ) {
						$search_message = __( 'Search results for "%s"', 'wpv-views' );
						if ( isset( $_GET["status"] )
								&& 'trash' == sanitize_text_field( $_GET["status"] ) ) {
							$search_message = __( 'Search results for "%s" in trashed Views', 'wpv-views' );
						}
						?>
							<span class="subtitle">
								<?php echo sprintf( $search_message, $search_term ); ?>
							</span>
						<?php
					}
				?>
			</h2>

			<?php
				// Messages: trashed, untrashed, deleted
				if ( isset( $_GET['trashed'] ) && is_numeric( $_GET['trashed'] ) ) {
					?>
					<div id="message" class="updated below-h2">
						<p>
							<?php _e('View moved to the Trash', 'wpv-views'); ?>.
							<a href="<?php echo add_query_arg( array( 'page' => 'views', 'untrashed' => '1' ), admin_url( 'admin.php' ) ); ?>"
									class="js-wpv-untrash" data-id="<?php echo $_GET['trashed']; ?>"
									data-nonce="<?php echo wp_create_nonce( 'wpv_view_listing_actions_nonce' ); ?>">
								<?php _e('Undo', 'wpv-views'); ?>
							</a>
						</p>
					</div>
					<?php
				}

				if ( isset( $_GET['untrashed'] ) && is_numeric( $_GET['untrashed'] ) && (int)$_GET['untrashed'] == 1 ) {
					?>
					<div id="message" class="updated below-h2">
						<p>
							<?php _e( 'View restored from the Trash', 'wpv-views' ); ?>
						</p>
					</div>
					<?php
				}

				if ( isset( $_GET['deleted'] ) && is_numeric( $_GET['deleted'] ) && (int)$_GET['deleted'] == 1 ) {
					?>
					<div id="message" class="updated below-h2">
						<p>
							<?php _e('View permanently deleted', 'wpv-views'); ?>
						</p>
					</div>
					<?php
				}

				if ( $has_views ) {
					// Display the rest of the content if there are Views to list
					wpv_admin_view_listing_table($has_views);
				} else {
					// Show a message in any other case
					?>
						<div class="wpv-view-not-exist js-wpv-view-not-exist">
							<p><?php _e('Views load content from the database and display on the site.'); ?></p>
							<p>
								<a class="button js-wpv-views-add-first" href="#">
									<i class="icon-plus"></i>
									<?php _e('Create your first View','wpv-views');?>
								</a>
							</p>
						</div>
					<?php
				}

			?>

		</div> <!-- .wpv-views-listing-page" -->

	</div> <!-- .toolset-views" -->

	<div class="popup-window-container"> <!-- placeholder for static colorbox popups -->

		<!-- popup: create View -->
		<div class="wpv-dialog create-view-form-dialog js-create-view-form-dialog">
			<?php
				wp_nonce_field('wp_nonce_create_view', 'wp_nonce_create_view');
				printf(
						'<input class="js-view-new-redirect" name="view_creation_redirect" type="hidden" value="%s" />',
						// Careful, it is expected that this value really ends with "view_id=". View ID gets appended to it in JS.
						admin_url( 'admin.php?page=views-editor&amp;view_id=') );
			?>
			<div class="wpv-dialog-header">
				<h2><?php _e('Add a new View','wpv-views') ?></h2>
				<i class="icon-remove js-dialog-close"></i>
			</div>
			<div class="wpv-dialog-content no-scrollbar">
				<p>
					<?php _e('A View loads content from the database and displays with your HTML.', 'wpv-views'); ?>
				</p>
				<p>
					<strong><?php _e(' What kind of display do you want to create?','wpv-views'); ?></strong>
				</p>
				<ul>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_all" value="all" />
							<label for="view_purpose_all"><?php _e('Display all results','wpv-views'); ?></label>
							<span class="helper-text"><?php _e('The View will output all the results returned from the query section.', 'wpv-views'); ?></span>
						</p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_pagination" value="pagination" />
							<label for="view_purpose_pagination"><?php _e('Display the results with pagination','wpv-views'); ?></label>
							<span class="helper-text"><?php _e('The View will display the query results in pages.', 'wpv-views'); ?></span>
						</p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_slider" value="slider" />
							<label for="view_purpose_slider"><?php _e('Display the results as a slider','wpv-views'); ?></label>
							<span class="helper-text"><?php _e('The View will display the query results as slides.', 'wpv-views'); ?></span>
						</p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_parametric" value="parametric" />
							<label for="view_purpose_parametric"><?php _e('Display the results as a parametric search','wpv-views'); ?></label>
							<span class="helper-text"><?php _e('Visitors will be able to search through your content using different search criteria.', 'wpv-views'); ?></span>
						</p>
					</li>
					<li>
						<p>
							<input type="radio" name="view_purpose" class="js-view-purpose" id="view_purpose_full" value="full" />
							<label for="view_purpose_full"><?php _e('Full custom display mode','wpv-views'); ?></label>
							<span class="helper-text"><?php _e('See all the View controls open and set up things manually..', 'wpv-views'); ?></span>
						</p>
					</li>
				</ul>

				<p>
					<strong><label for="view_new_name"><?php _e('Name this View','wpv-views'); ?></label></strong>
				</p>
				<p>
					<input type="text" name="view_new_name" id="view_new_name" class="js-new-post_title"
							placeholder="<?php echo htmlentities( __('Enter title here', 'wpv-views'), ENT_QUOTES ); ?>"
							data-highlight="<?php echo htmlentities( __('Now give this View a name', 'wpv-views'), ENT_QUOTES ); ?>" />
				</p>
				<div class="js-error-container"></div>
			</div>

			<div class="wpv-dialog-footer">
				<?php wp_nonce_field('wp_nonce_create_view', 'wp_nonce_create_view'); ?>
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-primary js-create-new-view"><?php _e('Create View','wpv-views') ?></button>
			</div>
		</div> <!-- .create-view-form-dialog -->

		<!-- popup: delete View - confirmation -->

		<div class="wpv-dialog js-delete-view-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Delete View','wpv-views') ?></h2>
			</div>
			<div class="wpv-dialog-content">
				<p><?php _e('Are you sure want delete this View? ','wpv-views') ?></p>
				<p><?php _e('Please use the Scan button first to be sure that it is not used anywhere.','wpv-views') ?></p>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-primary js-remove-view-permanent"
						data-nonce="<?php echo wp_create_nonce( 'wpv_remove_view_permanent_nonce' ); ?>">
					<?php _e('Delete','wpv-views') ?>
				</button>
			</div>
		</div> <!-- .js-delete-view-dialog -->

		<!-- popup: duplicate View - take name for the new one -->

		<div class="wpv-dialog js-duplicate-view-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Duplicate View','wpv-views') ?></h2>
			</div>
			<div class="wpv-dialog-content">
                <p>
                    <label for="duplicated_view_name"><?php _e('Name this View','wpv-views'); ?></label>
                    <input type="text" value="" class="js-duplicated-view-name"
							placeholder="<?php _e('Enter name here','wpv-views') ?>" name="duplicated_view_name" />
                </p>
                <div class="js-view-duplicate-error"></div>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-secondary js-duplicate-view" disabled="disabled"
						data-nonce="<?php echo wp_create_nonce( 'wpv_duplicate_view_nonce' ); ?>"
						data-error="<?php echo htmlentities( __('A View with that name already exists. Please use another name.', 'wpv-views'), ENT_QUOTES ); ?>">
					<?php _e('Duplicate','wpv-views') ?>
				</button>
			</div>
		</div> <!-- .js-duplicate-view-dialog -->

	</div> <!-- .popup-window-container" -->
	<?php
}

/**
* wpv_admin_view_listing_table
*
* @param $view_ids array() of View IDs
*
* Displays the content of the Views admin listing page: status, table and pagination
*
*/
function wpv_admin_view_listing_table( $views_ids ) {

	global $wpdb;

	// array of URL modifiers
	$mod_url = array(
		'orderby' => '',
		'order' => '',
		's' => '',
		'items_per_page' => '',
		'paged' => '',
		'status' => ''
	);

	// array of WP_Query parameters
	$wpv_args = array(
		'post_type' => 'view',
		'post__in' => $views_ids,
		'posts_per_page' => WPV_ITEMS_PER_PAGE,
		'order' => 'ASC',
		'orderby' => 'title',
		'post_status' => 'publish'
	);

	// apply post_status coming from the URL parameters
	if ( isset( $_GET["status"] ) && '' != $_GET["status"] ) {
		$wpv_args['post_status'] = sanitize_text_field( $_GET["status"] );
		$mod_url['status'] = sanitize_text_field( $_GET["status"] );
	}

	// perform the search in Views titles and decriptions and return an array to be used in post__in
	if ( isset( $_GET["s"] ) && '' != $_GET["s"] ) {
		$s_param = urldecode( sanitize_text_field( $_GET["s"] ) );
		$new_args = $wpv_args;
		$unique_ids = array();

		$new_args['posts_per_page'] = '-1';
		$new_args['s'] = $s_param;
		$query_1 = new WP_Query( $new_args );

		while ( $query_1->have_posts() ) {
			$query_1->the_post();
			$unique_ids[] = get_the_id();
		}

		unset( $new_args['s'] );

		$new_args['meta_query'] = array(
			array(
				'key' => '_wpv_description',
				'value' => $s_param,
				'compare' => 'LIKE'
			)
		);
		$query_2 = new WP_Query( $new_args );

		while ( $query_2->have_posts() ) {
			$query_2->the_post();
			$unique_ids[] = get_the_id();
		}

		$unique = array_unique( $unique_ids );

		if ( count( $unique ) == 0 ) {
			$wpv_args['post__in'] = array('0');
		} else {
			$wpv_args['post__in'] = $unique;
		}

		$mod_url['s'] = sanitize_text_field( $_GET["s"] );
	}

	// apply posts_per_page coming from the URL parameters
	if ( isset( $_GET["items_per_page"] ) && '' != $_GET["items_per_page"] ) {
		$wpv_args['posts_per_page'] = (int) $_GET["items_per_page"];
		$mod_url['items_per_page'] = (int) $_GET["items_per_page"];
	}

	// apply orderby coming from the URL parameters
	if ( isset( $_GET["orderby"] ) && '' != $_GET["orderby"] ) {
		$wpv_args['orderby'] = sanitize_text_field( $_GET["orderby"] );
		$mod_url['orderby'] = sanitize_text_field( $_GET["orderby"] );

		// apply order coming from the URL parameters
		if ( isset( $_GET["order"] ) && '' != $_GET["order"] ) {
			$wpv_args['order'] = sanitize_text_field( $_GET["order"] );
			$mod_url['order'] = sanitize_text_field( $_GET["order"] );
		}
	}

	// apply paged coming from the URL parameters
	if ( isset( $_GET["paged"] ) && '' != $_GET["paged"] ) {
		$wpv_args['paged'] = (int) $_GET["paged"];
		$mod_url['paged'] = (int) $_GET["paged"];
	}

	$wpv_query = new WP_Query( $wpv_args );
	$wpv_count_posts = $wpv_query->post_count;
	$wpv_found_posts = $wpv_query->found_posts;
	$wpv_total_views_list = implode( "','", $views_ids );

	// to hold the number of Views in each status
	$wpv_views_status = array();
	$wpv_views_status['publish'] = $wpdb->get_var( "SELECT COUNT(ID) from $wpdb->posts WHERE post_status = 'publish' AND ID IN ('$wpv_total_views_list')" );
	$wpv_views_status['trash'] = ( sizeof( $views_ids ) - $wpv_views_status['publish'] );

	?>
		<ul class="subsubsub"><!-- links to lists Views in different statuses -->
			<?php
				// "publish" status
				$is_current = ( $wpv_args['post_status'] == 'publish' && !isset( $_GET["s"] ) );
				printf(
						'<li><a href="%s" %s >%s</a> (%s) | </li>',
						add_query_arg(
								array( 'page' => 'views', 'status' => 'publish' ),
								admin_url('admin.php') ),
						$is_current ? ' class="current" ' : '',
						__( 'Published', 'wpv-views' ),
						$wpv_views_status['publish'] );

				// "trash" status
				$is_current = ( $wpv_args['post_status'] == 'trash' && !isset( $_GET["s"] ) );
				printf(
						'<li><a href="%s" %s >%s</a> (%s)</li>',
						add_query_arg(
								array( 'page' => 'views', 'status' => 'trash' ),
								admin_url('admin.php') ),
						$is_current ? ' class="current" ' : '',
						__( 'Trash', 'wpv-views' ),
						$wpv_views_status['trash'] );
			?>
		</ul>
	<?php

	if ( $wpv_found_posts > 0 ) {
		?>
		<form id="posts-filter" action="" method="get"><!-- form to search Views-->
			<p class="search-box">
				<label class="screen-reader-text" for="post-search-input"><?php _e('Search Views','wpv-views'); ?>:</label>
				<input type="search" id="post-search-input" name="s" value="<?php echo isset( $s_param ) ? $s_param : ''; ?>" />
				<input type="submit" name="" id="search-submit" class="button" value="<?php echo htmlentities( __('Search Views','wpv-views'), ENT_QUOTES ); ?>" />
				<input type="hidden" name="paged" value="1" />
			</p>
		</form>
		<?php
	}

	// if this page has more than one View
	if ( $wpv_count_posts > 0 ) {
		?>
		<table class="wpv-views-listing js-wpv-views-listing widefat">
			<thead>
				<?php
					/* To avoid code duplication, table header is stored in output buffer and echoed twice - within
					 * thead and tfoot tags. */
					ob_start();
				?>
				<tr>
					<?php
						$column_active = '';
						$column_sort_to = 'ASC';
						$column_sort_now = 'ASC';
						if ( $wpv_args['orderby'] === 'title' ) {
							$column_active = ' views-list-sort-active';
							$column_sort_to = ( $wpv_args['order'] === 'ASC' ) ? 'DESC' : 'ASC';
							$column_sort_now = $wpv_args['order'];
						}
					?>
					<th class="wpv-admin-listing-col-title">
						<?php
							// "sort by title" link
							printf(
									'<a href="%s" class="%s" data-orderby="title">%s <i class="%s"></i></a>',
									wpv_maybe_add_query_arg(
											array(
													'page' => 'views',
													'orderby' => 'title',
													'order' => $column_sort_to,
													's' => $mod_url['s'],
													'items_per_page' => $mod_url['items_per_page'],
													'paged' => $mod_url['paged'],
													'status' => $mod_url['status'] ),
											admin_url( 'admin.php' ) ),
									'js-views-list-sort views-list-sort ' . $column_active,
									__( 'Title','wpv-views' ),
									( 'DESC' === $column_sort_now ) ? 'icon-sort-by-alphabet-alt' : 'icon-sort-by-alphabet' );
						?>
					</th>

					<th class="wpv-admin-listing-col-summary js-wpv-col-two"><?php _e('Content to load','wpv-views') // TODO review this classname ?></th>
					<th class="wpv-admin-listing-col-scan"><?php _e('Used on','wpv-views') ?></th>

					<?php
						$column_active = '';
						$column_sort_to = 'DESC';
						$column_sort_now = 'DESC';
						if ( $wpv_args['orderby'] === 'date' ) {
							$column_active = ' views-list-sort-active';
							$column_sort_to = ( $wpv_args['order'] === 'ASC' ) ? 'DESC' : 'ASC';
							$column_sort_now = $wpv_args['order'];
						}
					?>
					<th class="wpv-admin-listing-col-date">
						<?php
							// "sort by date" link
							printf(
									'<a href="%s" class="%s" data-orderby="date">%s <i class="%s"></i></a>',
									add_query_arg(
											array(
													'page' => 'views',
													'orderby' => 'date',
													'order' => $column_sort_to,
													's' => $mod_url['s'],
													'items_per_page' => $mod_url['items_per_page'],
													'paged' => $mod_url['paged'],
													'status' => $mod_url['status'] ),
											admin_url( 'admin.php' ) ),
									'js-views-list-sort views-list-sort ' . $column_active,
									__( 'Date', 'wpv-views' ),
									( 'DESC' === $column_sort_now ) ? 'icon-sort-by-attributes-alt' : 'icon-sort-by-attributes' );
						?>
					</th>
				</tr>
				<?php
					// Get table header from output buffer and stop buffering
					$table_header = ob_get_contents();
					ob_end_clean();

					echo $table_header;
				?>
			</thead>
			<tfoot>
				<?php
					echo $table_header;
				?>
			</tfoot>

			<tbody class="js-wpv-views-listing-body">
				<?php
					$alternate = '';
					while ( $wpv_query->have_posts() ) :
						$wpv_query->the_post();
						$post_id = get_the_id();
						$post = get_post( $post_id );
						$meta = get_post_meta( $post_id, '_wpv_settings' );
						$view_description = get_post_meta( $post_id, '_wpv_description', true );
						$alternate = ' alternate' == $alternate ? '' : ' alternate';

						?>
						<tr id="wpv_view_list_row_<?php echo $post->ID; ?>" class="js-wpv-view-list-row<?php echo $alternate; ?>">
							<td class="wpv-admin-listing-col-title">
								<span class="row-title">
								<?php
									if ( $wpv_args['post_status'] == 'trash' ) {
										echo $post->post_title;
									} else {
										printf( '<a href="%s">%s</a>',
												add_query_arg(
														array( 'page' => 'views-editor', 'view_id' => $post->ID ),
														admin_url( 'admin.php' ) ),
												$post->post_title );
									}
								?>
								</span>
								<?php
									if ( isset( $view_description ) && '' != $view_description ) {
										?>
										<p class="desc">
											<?php echo nl2br($view_description); ?>
										</p>
										<?php
									}

									/* Generate and show row actions.
									 * Note that we want to add also 'simple' action names to the action list because
									 * they get echoed as a class of the span tag and get styled from WordPress core css
									 * accordingly (e.g. trash in different colour than the rest) */
									$row_actions = array( );

									if ( $wpv_args['post_status'] == 'publish' ) {
										$row_actions['edit'] = sprintf(
												'<a href="%s">%s</a>',
												add_query_arg(
													array( 'page' => 'views-editor', 'view_id' => $post->ID ),
													admin_url( 'admin.php' ) ),
												__( 'Edit', 'wpv-views' ) );
										$row_actions['duplicate js-views-actions-duplicate'] = sprintf( '<a href="#">%s</a>', __( 'Duplicate', 'wpv-views' ) );
										$row_actions['trash js-views-actions-trash'] = sprintf( '<a href="#">%s</a>', __( 'Move to trash', 'wpv-views' ) );
									} else if ( $wpv_args['post_status'] == 'trash' ) {
										$row_actions['restore-from-trash js-views-actions-restore-from-trash'] = sprintf( '<a href="#">%s</a>', __( 'Restore from trash', 'wpv-views' ) );
										$row_actions['delete js-views-actions-delete'] = sprintf( '<a href="#">%s</a>', __( 'Delete', 'wpv-views' ) );
									}

									echo wpv_admin_table_row_actions( $row_actions,	array(
											"data-view-id" => $post->ID,
											"data-viewactionnonce" => wp_create_nonce( 'wpv_view_listing_actions_nonce' ) ) );
								?>
							</td>
							<td class="wpv-admin-listing-col-summary">
								<?php echo wpv_create_content_summary_for_listing( $post->ID ); ?>
							</td>
							<td class="wpv-admin-listing-col-scan">
								<button class="button js-scan-button" data-view-id="<?php echo $post->ID; ?>">
									<?php _e( 'Scan', 'wp-views' ); ?>
								</button>
								<span class="js-nothing-message hidden"><?php _e( 'Nothing found', 'wpv-views' ); ?></span>
							</td>
							<td class="wpv-admin-listing-col-date">
								<?php echo get_the_time( get_option( 'date_format' ), $post->ID ); ?>
							</td>
						</tr>
					<?php
					endwhile;
				?>
			</tbody>
		</table>

		<p class="add-new-view" >
			<a class="button js-wpv-views-add-new" href="#">
				<i class="icon-plus"></i><?php _e('Add new View','wpv-views') ?>
			</a>
		</p>

		<?php
			wpv_admin_listing_pagination( 'views', $wpv_found_posts, $wpv_args["posts_per_page"], $mod_url );
		?>
		<?php

	} else {

		// No Views matches the criteria
		?>
		<div class="wpv-views-listing views-empty-list">
			<?php
				if ( isset( $_GET["status"] ) && $_GET["status"] == 'trash' && isset( $_GET["s"] ) && $_GET["s"] != '' ) {
					printf(
							'<p>%s <a class="button-secondary" href="%s">%s</a></p>',
							__( 'No Views in trash matched your criteria.', 'wpv-views' ),
							wpv_maybe_add_query_arg(
									array(
											'page' => 'views',
											'orderby' => $mod_url['orderby'],
											'order' => $mod_url['order'],
											'items_per_page' => $mod_url['items_per_page'],
											'paged' => '1',
											'status' => 'trash' ),
									admin_url( 'admin.php' ) ),
							__( 'Return', 'wpv-views' ) );
				} else if ( isset( $_GET["status"] ) && $_GET["status"] == 'trash' ) {
					printf(
							'<p>%s <a class="button-secondary" href="%s">%s</a></p>',
							__( 'No Views in trash.', 'wpv-views' ),
							wpv_maybe_add_query_arg(
									array(
											'page' => 'views',
											'orderby' => $mod_url['orderby'],
											'order' => $mod_url['order'],
											'items_per_page' => $mod_url['items_per_page'],
											'paged' => '1' ),
									admin_url( 'admin.php' ) ),
							__( 'Return', 'wpv-views' ) );
				} else if ( isset( $_GET["s"] ) && $_GET["s"] != '' ) {
					printf(
							'<p>%s <a class="button-secondary" href="%s">%s</a></p>',
							__( 'No Views matched your criteria.', 'wpv-views' ),
							wpv_maybe_add_query_arg(
									array(
											'page' => 'views',
											'orderby' => $mod_url['orderby'],
											'order' => $mod_url['order'],
											'items_per_page' => $mod_url['items_per_page'],
											'paged' => '1' ),
									admin_url( 'admin.php' ) ),
							__( 'Return', 'wpv-views' ) );
				} else {
					?>
					<div class="wpv-view-not-exist js-wpv-view-not-exist">
						<p><?php _e('Views load content from the database and display on the site.'); ?></p>
						<p><a class="button js-wpv-views-add-first" href="#"><i class="icon-plus"></i><?php _e('Create your first View','wpv-views');?></a></p>
					</div>
					<?php
				}
			?>
		</div>
		<?php
	}
}


function wpv_admin_menu_views_listing_row($post_id) { // DEPRECATED

	ob_start();
	$post = get_post($post_id);
	$meta = get_post_meta($post_id, '_wpv_settings');
	$view_description = get_post_meta($post_id, '_wpv_description', true);
	?>
	<tr id="wpv_view_list_row_<?php echo $post->ID; ?>" class="js-wpv-view-list-row">
		<td class="post-title page-title column-title">
			<span class="row-title">
				<a href="admin.php?page=views-editor&amp;view_id=<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></a>
			</span>
			<?php if (isset($view_description) && '' != $view_description): ?>
				<p class="desc">
                    <?php echo nl2br($view_description)?>
                </p>
			<?php endif; ?>
		</td>
		<td>
			<?php echo wpv_create_content_summary_for_listing($post->ID); ?>
		</td>
		<td>
			<select class="js-views-actions" name="list_views_action_<?php echo $post->ID; ?>" id="list_views_action_<?php echo $post->ID; ?>" data-view-id="<?php echo $post->ID; ?>">
				<option value="0"><?php _e('Choose','wpv-views') ?>&hellip;</option>
				<option value="delete"><?php _e('Delete','wpv-views') ?></option>
				<option value="duplicate"><?php _e('Duplicate','wpv-views') ?></option>
			</select>
		</td>
		<td>
			<button class="button js-scan-button" data-view-id="<?php echo $post->ID; ?>"><?php _e('Scan','wp-views') ?></button>
            <span class="js-nothing-message hidden"><?php _e('Nothing found','wpv-views');?></span>
		</td>
		<td>
			<?php echo get_the_time(get_option('date_format'), $post->ID); ?>
		</td>
	</tr>
	<?php
	$row = ob_get_contents();
	ob_end_clean();

	return $row;

}
