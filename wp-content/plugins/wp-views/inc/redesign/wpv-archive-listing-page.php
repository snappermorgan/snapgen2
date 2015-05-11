<?php

/**
* wpv_admin_archive_listing_page
*
* Creates the main structure of the WPA admin listing page: wrapper and header
*
*/
function wpv_admin_archive_listing_page() {
	global $WPV_view_archive_loop;
	?>
	<div class="wrap toolset-views">

        <div class="wpv-views-listing-page wpv-views-listing-archive-page" data-none-message="<?php _e("This WordPress Archive isn't being used for any loops.",'wpv-views') ?>">
		<?php
			$has_items = wpv_check_views_exists('archive');
			$search_term = isset( $_GET["s"] ) ? urldecode( sanitize_text_field($_GET["s"]) ) : '';
			wp_nonce_field( 'work_views_listing', 'work_views_listing' );
			wp_nonce_field( 'wpv_remove_view_permanent_nonce', 'wpv_remove_view_permanent_nonce' );
		?>
		<div id="icon-views" class="icon32"></div>
        	<h2><!-- classname wpv-page-title removed -->
        		<?php _e('WordPress Archives', 'wpv-views') ?>
        		<?php
					if ( $WPV_view_archive_loop->check_archive_loops_exists() && $has_items ) {
						$data_target = add_query_arg( array( 'action' => 'wpv_create_wp_archive_button' ), admin_url('admin-ajax.php') );
						?>
						<a href="#"	data-target="<?php echo $data_target; ?>"
								class="add-new-h2 js-wpv-views-archive-add-new wpv-views-archive-add-new">
							<?php _e('Add new WordPress Archive','wpv-views') ?>
						</a>
						<?php
					}

					if ( !empty( $search_term ) ) {
						$search_message = __('Search results for "%s"','wpv-views');
						if ( isset( $_GET["status"] ) && 'trash' == sanitize_text_field( $_GET["status"] ) ) {
							$search_message = __('Search results for "%s" in trashed WordPress Archives', 'wpv-views');
						}
						?>
							<span class="subtitle">
								<?php echo sprintf( $search_message, $search_term ); ?>
							</span>
						<?php
					}
				?>
        	</h2>

			<?php // Messages: trashed, untrashed, deleted
				if ( isset( $_GET['trashed'] ) && is_numeric( $_GET['trashed'] ) ) {
					?>
					<div id="message" class="updated below-h2">
						<p>
							<?php
								_e( 'WordPress Archive moved to the Trash.', 'wpv-views' );
								printf(
										' <a href="%s" class="js-wpv-untrash" data-id="%s" data-nonce="%s">%s</a>',
										add_query_arg(
												array(
														'page' => 'view-archives',
														'untrashed' => '1' ),
												admin_url( 'admin.php' ) ),
										$_GET['trashed'],
										wp_create_nonce( 'wpv_view_listing_actions_nonce' ),
										__( 'Undo', 'wpv-views' ) );
							?>
						</p>
					</div>
					<?php
				}

				if ( isset( $_GET['untrashed'] ) && is_numeric( $_GET['untrashed'] ) && (int)$_GET['untrashed'] == 1 ) {
					?>
					<div id="message" class="updated below-h2">
						<p>
							<?php _e('WordPress Archive restored from the Trash', 'wpv-views'); ?>
						</p>
					</div>
					<?php
				}

				if ( isset( $_GET['deleted'] ) && is_numeric( $_GET['deleted'] ) && (int)$_GET['deleted'] == 1 ) {
					?>
					<div id="message" class="updated below-h2">
						<p>
							<?php _e('WordPress Archive permanently deleted', 'wpv-views'); ?>
						</p>
					</div>
					<?php
				}
			?>

            <?php
				if ( isset( $_GET["arrangeby"] ) && sanitize_text_field( $_GET["arrangeby"] ) == 'usage' ) {

					// Show table arranged by Usage
					wp_nonce_field( 'wpv_wp_archive_arrange_usage', 'wpv_wp_archive_arrange_usage' );

					if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) {
						?>
						<p id="js-wpv-no-archive" class="toolset-alert toolset-alert-info update below-h2">
							<?php _e('All loops have a WordPress Archive assigned','wpv-views'); ?>
						</p>
						<?php
					}

					?>
					<div class="wpv-views-listing-arrange js-wpv-views-listing-arrange">
						<p><?php _e('Arrange by','wpv-views'); ?>: </p>
						<ul>
						<li data-sortby="name"><a href="<?php echo add_query_arg( array( 'page' => 'view-archives' ), admin_url('admin.php') ); ?>"><?php _e('Name','wpv-views') ?></a></li>
						<li data-sortby="usage" class="active"><?php _e('Usage','wpv-views') ?></li>
						</ul>
					</div>
					<?php

					wpv_admin_wordpress_archives_listing_table_by_usage();

					if ( $WPV_view_archive_loop->check_archive_loops_exists() ) {
						?>
						<p class="add-new-view js-add-new-view">
							<a class="button js-wpv-views-archive-add-new wpv-views-archive-add-new"
									data-target="<?php echo add_query_arg( array( 'action' => 'wpv_create_wp_archive_button' ), admin_url('admin-ajax.php') ); ?>"
									href="<?php echo add_query_arg( array( 'page' => 'view-archives-new' ), admin_url( 'admin.php' ) ); ?>">
								<i class="icon-plus"></i><?php _e('Add new WordPress Archive','wpv-views') ?>
							</a>
						</p>
						<?php
					}

				} else {

					// Show table arranged by item names
					if ( $has_items ) {

						wpv_admin_wordpress_archives_listing_table_by_name($has_items);

					} else {

						// No items are present.
						if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) {
							?>
							<p id="js-wpv-no-archive" class="toolset-alert toolset-alert-info">
								<?php _e('All loops have a WordPress Archive assigned','wpv-views'); ?>
							</p>
							<?php
						}

						?>
						<div class="wpv-view-not-exist js-wpv-view-not-exist">
							<p><?php _e('WordPress Archives let you customize the output of standard Archive pages.');?></p>
							<p>
							<a class="button js-wpv-views-archive-create-new"
									data-target="<?php echo add_query_arg( array( 'action' => 'wpv_create_wp_archive_button' ), admin_url( 'admin-ajax.php' ) ); ?>"
									href="<?php echo add_query_arg( array( 'page' => 'view-archives-new' ), admin_url( 'admin.php' ) ); ?>">
								<i class="icon-plus"></i>
								<?php _e('Create your first WordPress Archive');?>
							</a>
							</p>
						</div>
						<?php
					}
				}
			?>
        </div> <!-- .wpv-settings-container" -->
	</div>
	<?php
}


function wpv_admin_wordpress_archives_listing_table_by_name($has_items) {
	?>
		<div id="js-wpv-archive-tables-containter" class="wpv-archive-tables-containter">
			<?php wpv_admin_archive_listing_name($has_items); ?>
		</div>
	<?php
}


function wpv_admin_wordpress_archives_listing_table_by_usage() {
	?>
	<div id="js-wpv-archive-tables-containter" class="wpv-archive-tables-containter">
		<?php wpv_admin_archive_listing_usage(); ?>
	</div>
	<?php
}


function wpv_admin_archive_listing_name( $views_ids = array() ) {

	global $WP_Views, $WPV_view_archive_loop, $wpdb;

	// array of URL modifiers
	/* TODO i think it would be better to use add_query_arg() for this
	 * http://codex.wordpress.org/Function_Reference/add_query_arg */
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


	if ( isset( $_GET["s"] ) && '' != $_GET["s"] ) {
		$s_param = urldecode( sanitize_text_field( $_GET["s"] ) );
		$new_args = $wpv_args;
		$unique_ids = array();

		$new_args['posts_per_page'] = '-1';
		$new_args['s'] = $s_param;
		$query_1 = new WP_Query( $new_args );

		while ($query_1->have_posts()) {
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

		while ($query_2->have_posts()) {
			$query_2->the_post();
			$unique_ids[] = get_the_id();
		}

		$unique = array_unique($unique_ids);

		if ( count($unique) == 0 ) {
			$wpv_args['post__in'] = array('0');
		} else {
			$wpv_args['post__in'] = $unique;
		}

		$mod_url['s'] = sanitize_text_field( $_GET["s"] );
	}

	if ( isset( $_GET["items_per_page"] ) && '' != $_GET["items_per_page"] ) {
		$wpv_args['posts_per_page'] = (int) $_GET["items_per_page"];
		$mod_url['items_per_page'] = (int) $_GET["items_per_page"];
	}

	if ( isset( $_GET["orderby"] ) && '' != $_GET["orderby"] ) {
		$wpv_args['orderby'] = sanitize_text_field($_GET["orderby"]);
		$mod_url['orderby'] = sanitize_text_field($_GET["orderby"]);
		if ( isset( $_GET["order"] ) && '' != $_GET["order"] ) {
			$wpv_args['order'] = sanitize_text_field($_GET["order"]);
			$mod_url['order'] = sanitize_text_field($_GET["order"]);
		}
	}

	if ( isset( $_GET["paged"] ) && '' != $_GET["paged"]) {
		$wpv_args['paged'] = (int) $_GET["paged"];
		$mod_url['paged'] = (int) $_GET["paged"];
	}

	$wpv_query = new WP_Query( $wpv_args );

	$wpv_count_posts = $wpv_query->post_count;
	$wpv_found_posts = $wpv_query->found_posts;
	$wpv_total_views_list = implode("','", $views_ids);

	// holds the number of Views in each status
	$wpv_views_status = array();
	$wpv_views_status['publish'] = $wpdb->get_var( "SELECT COUNT(ID) from $wpdb->posts WHERE post_status = 'publish' AND ID IN ('$wpv_total_views_list')" );
	$wpv_views_status['trash'] = ( sizeof( $views_ids ) - $wpv_views_status['publish'] );

	?>
	<div class="wpv-views-listing-arrange js-wpv-views-listing-arrange" style="clear:none;float:left">
		<p style="margin-bottom:0"><?php _e('Arrange by','wpv-views'); ?>: </p>
		<ul>
			<li data-sortby="name" class="active"><?php _e('Name','wpv-views') ?></li>
			<li data-sortby="usage">
				<a href="<?php echo add_query_arg( array( 'page' => 'view-archives', 'arrangeby' => 'usage' ), admin_url( 'admin.php' ) ); ?>">
					<?php _e( 'Usage', 'wpv-views' ); ?>
				</a>
			</li>
		</ul>
	</div>

	<!-- links to lists WPA in different statuses -->
	<ul class="subsubsub" style="clear:left">
		<li>
			<?php
				// Show link to published WPA templates.

				// We show this link as current only if there is no search.
				$is_plain_publish_current_status = ( $wpv_args['post_status'] == 'publish' && !isset( $_GET["s"] ) );

				printf(
						'<a href="%s" %s >%s</a> (%s) |',
						add_query_arg( array( 'page' => 'view-archives', 'status' => 'publish' ), admin_url( 'admin.php' ) ),
						$is_plain_publish_current_status ? 'class="current"' : '',
						__( 'Published', 'wpv-views' ),
						$wpv_views_status['publish'] );

			?>
		</li>
		<li>
			<?php
				// Show link to trashed WPA templates.

				// We show this link as current only if there is no search.
				$is_plain_trash_current_status = ( $wpv_args['post_status'] == 'trash' && !isset( $_GET["s"] ) );

				printf(
						'<a href="%s" %s >%s</a> (%s)',
						add_query_arg( array( 'page' => 'view-archives', 'status' => 'trash' ), admin_url( 'admin.php' ) ),
						$is_plain_trash_current_status ? 'class="current"' : '',
						__( 'Trash', 'wpv-views' ),
						$wpv_views_status['trash'] );
			?>
		</li>
	</ul>
	<?php

	if ( $wpv_found_posts > 0 ) {
		?>
			<form id="posts-filter" action="" method="get"
				class="<?php // if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) echo 'hidden'; WHY hide the search when all loops have been asigned? ?>">
				<p class="search-box">
					<label class="screen-reader-text" for="post-search-input"><?php _e('Search WordPress Archives','wpv-views'); ?>:</label>
					<?php $search_term = isset( $_GET["s"] ) ? urldecode( sanitize_text_field($_GET["s"]) ) : ''; ?>
					<input type="search" id="post-search-input" name="s" value="<?php echo $search_term; ?>" />
					<input type="submit" name="" id="search-submit" class="button" value="<?php echo htmlentities( __('Search WordPress Archives','wpv-views'), ENT_QUOTES ); ?>" />
					<input type="hidden" name="paged" value="1" />
				</p>
			</form>
		<?php
	}

	if ( !$WPV_view_archive_loop->check_archive_loops_exists() ) {
		?>
			<p id="js-wpv-no-archive" class="toolset-alert toolset-alert-info">
				<?php _e('All loops have a WordPress Archive assigned','wpv-views'); ?>
			</p>
		<?php
	}

	if ( $wpv_count_posts > 0 ) {
		?>
		<table id="wpv_view_list" class="js-wpv-views-listing wpv-views-listing wpv-views-listing-by-name widefat">
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
									'<a href="%s" class="%s", data-orderby="title">%s <i class="%s"></i></a>',
									wpv_maybe_add_query_arg(
											array(
													'page' => 'view-archives',
													'orderby' => 'title',
													'order' => $column_sort_to,
													's' => $mod_url['s'],
													'items_per_page' => $mod_url['items_per_page'],
													'paged' => $mod_url['paged'],
													'status' => $mod_url['status'] ),
											admin_url( 'admin.php' ) ),
									'js-views-list-sort views-list-sort' . $column_active,
									__( 'Title', 'wpv-views' ),
									( 'DESC' === $column_sort_now ) ? 'icon-sort-by-alphabet-alt' : 'icon-sort-by-alphabet' );
						?>
					</th>
					<th class="wpv-admin-listing-col-usage"><?php _e('Archive usage','wpv-views') ?></th>

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
									wpv_maybe_add_query_arg(
											array(
													'page' => 'view-archives',
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
					$options = $WP_Views->get_options();
					$loops = $WPV_view_archive_loop->_get_post_type_loops();
					$builtin_loops = array(
							'home-blog-page' => __('Home/Blog', 'wpv-views'),
							'search-page' => __('Search results', 'wpv-views'),
							'author-page' => __('Author archives', 'wpv-views'),
							'year-page' => __('Year archives', 'wpv-views'),
							'month-page' => __('Month archives', 'wpv-views'),
							'day-page' => __('Day archives', 'wpv-views') );
					$taxonomies = get_taxonomies('', 'objects');
					$exclude_tax_slugs = array();
					$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
					$alternate = '';

					while ( $wpv_query->have_posts() ) :
						$wpv_query->the_post();
						$post_id = get_the_id();
						$post = get_post($post_id);
						$meta = get_post_meta($post_id, '_wpv_settings');
						$view_description = get_post_meta($post_id, '_wpv_description', true);
						$alternate = ' alternate' == $alternate ? '' : ' alternate';
						?>
						<tr id="wpv_view_list_row_<?php echo $post->ID; ?>" class="js-wpv-view-list-row<?php echo $alternate; ?>" >
							<td  class="wpv-admin-listing-col-title">
								<span class="row-title">
									<?php
										if ( $wpv_args['post_status'] == 'trash' ) {
											echo trim( $post->post_title );
										} else {
											// Title + edit link
											printf(
													'<a href="%s">%s</a>',
													add_query_arg(
															array( 'page' => 'view-archives-editor', 'view_id' => $post->ID ),
															admin_url( 'admin.php' ) ),
													trim( $post->post_title ) );
										}
									?>
								</span>
								<?php
									// Show the description if there is any.
									if ( isset( $view_description ) && '' != $view_description ) {
										?>
										<p class="desc">
											<?php echo nl2br( $view_description ); ?>
										</p>
										<?php
									}

									/* Generate and show row actions.
									 * Note that we want to add also 'simple' action names to the action list because
									 * they get echoed as a class of the span tag and get styled by WordPress core css
									 * accordingly (e.g. trash in different colour than the rest) */
									$row_actions = array();

									if ( 'publish' == $wpv_args['post_status'] ) {
										$row_actions['edit'] = sprintf(
												'<a href="%s">%s</a>',
												add_query_arg(
														array( 'page' => 'view-archives-editor', 'view_id' => $post->ID ),
														admin_url( 'admin.php' ) ),
												__( 'Edit', 'wpv-views' ) );
										/* Note that hash in <a href="#"> is present so the link behaves like a link.
										 * <a href=""> causes problems with colorbox and with mere <a> the mouse cursor
										 * doesn't change when hovering over the link. */
										$row_actions['change js-list-views-action-change'] = sprintf( '<a href="#">%s</a>', __( 'Change archive usage', 'wpv-views' ) );
										$row_actions['trash js-list-views-action-trash'] = sprintf( '<a href="#">%s</a>', __( 'Move to trash', 'wpv-views' ) );
									} else if ( 'trash' == $wpv_args['post_status'] ) {
										$row_actions['restore-from-trash js-list-views-action-restore-from-trash'] = sprintf( '<a href="#">%s</a>', __( 'Restore from trash', 'wpv-views' ) );
										$row_actions['delete js-list-views-action-delete'] = sprintf( '<a href="#">%s</a>', __( 'Delete', 'wpv-views' ) );
									}

									echo wpv_admin_table_row_actions( $row_actions,	array(
											"data-view-id" => $post->ID,
											"data-viewactionnonce" => wp_create_nonce( 'wpv_view_listing_actions_nonce' ) ) );
								?>
							</td>
							<td class="wpv-admin-listing-col-usage">
								<?php
									$selected = array();
									foreach ( $loops as $loop => $loop_name ) {
										if ( isset( $options[ 'view_' . $loop ] ) && $options[ 'view_' . $loop ] == $post->ID ) {
											$not_built_in = '';
											if ( !isset( $builtin_loops[ $loop ] ) ) {
												$not_built_in = __(' (post type archive)', 'wpv-views' );
											}
											$selected[] = '<li>' . $loop_name . $not_built_in . '</li>';
										}
									}

									foreach ( $taxonomies as $category_slug => $category ) {
										if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
											continue;
										}
										// Only show taxonomies with show_ui set to TRUE
										if ( !$category->show_ui ) {
											continue;
										}
										$name = $category->name;
										if ( isset ( $options[ 'view_taxonomy_loop_' . $name ] )
											&& $options[ 'view_taxonomy_loop_' . $name ] == $post->ID )
										{
											$selected[] = '<li>' . $category->labels->name . __(' (taxonomy archive)', 'wpv-views') . '</li>';
										}
									}

									if ( !empty( $selected ) ) {
										?>
										<ul class="wpv-taglike-list js-list-views-loops">
											<?php
												echo implode( $selected );
											?>
										</ul>
										<?php
									} else {
										echo __( "This WordPress Archive isn't being used for any loops.",'wpv-views' );
									}
								?>
								</ul>
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
	<?php
		if ( $WPV_view_archive_loop->check_archive_loops_exists() ) {
			?>
			<p class="add-new-view js-add-new-view">
				<a class="button js-wpv-views-archive-add-new wpv-views-archive-add-new"
						data-target="<?php echo add_query_arg( array( 'action' => 'wpv_create_wp_archive_button' ), admin_url( 'admin-ajax.php' ) );?>"
						href="">
					<i class="icon-plus"></i><?php _e('Add new WordPress Archive','wpv-views') ?>
				</a>
			</p>
			<?php
		}

	wpv_admin_listing_pagination( 'view-archives', $wpv_found_posts, $wpv_args["posts_per_page"], $mod_url );

	} else {
		// No WordPress Archives matches the criteria
		?>
		<div class="wpv-views-listing views-empty-list">
			<?php
				if ( isset( $_GET["status"] ) && $_GET["status"] == 'trash' && isset( $_GET["s"] ) && $_GET["s"] != '' ) {
					printf(
							'<p>%s <a class="button-secondary" href="%s">%s</a></p>',
							__( 'No WordPress Archives in trash matched your criteria.', 'wpv-views' ),
							wpv_maybe_add_query_arg(
									array(
											'page' => 'view-archives',
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
							__( 'No WordPress Archives in trash.', 'wpv-views' ),
							wpv_maybe_add_query_arg(
									array(
											'page' => 'view-archives',
											'orderby' => $mod_url['orderby'],
											'order' => $mod_url['order'],
											'items_per_page' => $mod_url['items_per_page'],
											'paged' => '1' ),
									admin_url( 'admin.php' ) ),
							__( 'Return', 'wpv-views' ) );
				} else if ( isset( $_GET["s"] ) && $_GET["s"] != '' ) {
					printf(
							'<p>%s <a class="button-secondary" href="%s">%s</a></p>',
							__( 'No WordPress Archives matched your criteria.', 'wpv-views' ),
							wpv_maybe_add_query_arg(
									array(
											'page' => 'view-archives',
											'orderby' => $mod_url['orderby'],
											'order' => $mod_url['order'],
											'items_per_page' => $mod_url['items_per_page'],
											'paged' => '1' ),
									admin_url( 'admin.php' ) ),
							__( 'Return', 'wpv-views' ) );
				} else {
					?>
					<p><?php _e('WordPress Archives let you customize the output of standard Archive pages.');?></p>
					<p>
						<?php
							// "Create your first archive" link
							printf(
									'<a data-target="%s" href="%s" class="button js-wpv-views-archive-create-new"><i class="icon-plus"></i> %s</a>',
									add_query_arg( array( 'action' => 'wpv_create_wp_archive_button' ), admin_url( 'admin-ajax.php' ) ),
									add_query_arg( array( 'page' => 'view-archives-new' ), admin_url( 'admin.php' ) ),
									__( 'Create your first WordPress Archive', 'wpv-views' ) );
						?>
					</p>
					<?php
				}
			?>
		</div>
		<?php
	}
}



function wpv_admin_archive_listing_usage() {

    ?>
	<table id="wpv_view_list_usage" class="js-wpv-views-listing wpv-views-listing wpv-views-listing-by-usage widefat">
		<thead>
			<tr>
				<th class="wpv-admin-listing-col-usage js-wpv-col-one"><?php _e('Archive loop','wpv-views') ?></th>
				<th class="wpv-admin-listing-col-title js-wpv-col-two"><?php _e('WordPress Archive used','wpv-views') ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="wpv-admin-listing-col-usage js-wpv-col-one"><?php _e('Used for','wpv-views') ?></th>
				<th class="wpv-admin-listing-col-title js-wpv-col-two"><?php _e('Title','wpv-views') ?></th>
			</tr>
		</tfoot>

		<tbody class="js-wpv-views-listing-body">
			<?php
				global $WP_Views;

				$options = $WP_Views->get_options();
				$alternate = '';

				$loops = array(
						'home-blog-page' => __('Home/Blog', 'wpv-views'),
						'search-page' => __('Search results', 'wpv-views'),
						'author-page' => __('Author archives', 'wpv-views'),
						'year-page' => __('Year archives', 'wpv-views'),
						'month-page' => __('Month archives', 'wpv-views'),
						'day-page' => __('Day archives', 'wpv-views')
				);

				foreach ( $loops as $slug => $name ) {
					$alternate = ' alternate' == $alternate ? '' : ' alternate';
					$post = null;
					if ( isset( $options['view_' . $slug] ) ) {
						$post = get_post( $options['view_' . $slug] );
					}

					?>
					<tr class="js-wpv-view-list-row<?php echo $alternate; ?>">
						<td class="wpv-admin-listing-col-usage">
							<span class="row-title"><?php echo $name ?></span>
							<?php
								if ( ! is_null( $post ) ) {
									echo wpv_admin_table_row_actions(
											array( "change_usage js-list-views-usage-action-change-usage" => sprintf( '<a href="#">%s</a>', __( 'Change' , 'wpv-views' ) ) ),
											array( "id" => "list_views_usage_action_" . $post->ID, "data-view-id" => 'view_' . $slug ) );
								}
							?>
						</td>
						<?php
							if ( is_null( $post ) ) {
								?>
								<td colspan="2">
									<a class="button button-small js-create-view-for-archive" data-forwhom="<?php echo esc_attr( $name ); ?>" href="#"><i class="icon-plus"></i><?php _e('Create a WordPress Archive for this loop');?></a>
								</td>
								<?php
							} else {
								?>
								<td class="wpv-admin-listing-col-title">
									<?php
										printf(
												'<a href="%s">%s</a>',
												add_query_arg(
														array( 'page' => 'view-archives-editor', 'view_id' => $post->ID ),
														admin_url( 'admin.php' ) ),
												$post->post_title );
									?>
								</td>
								<?php
							}
						?>
					</tr>
					<?php
				}

				$pt_loops = array();
				// Only offer loops for post types that already have an archive
				$post_types = get_post_types( array( 'public' => true, 'has_archive' => true), 'objects' );
				foreach ( $post_types as $post_type ) {
					if ( !in_array( $post_type->name, array( 'post', 'page', 'attachment' ) ) ) {
						$type = 'cpt_' . $post_type->name;
						$name = $post_type->labels->name;
						$pt_loops[$type] = $name;
					}
				}

				if ( count( $pt_loops ) > 0 ) {
					foreach ( $pt_loops as $slug => $name ) {
						$alternate = ' alternate' == $alternate ? '' : ' alternate';
						$post = null;
						if ( isset( $options['view_' . $slug] ) ) {
							$post = get_post( $options['view_' . $slug] );
						}
						?>
						<tr class="js-wpv-view-list-row<?php echo $alternate; ?>">
							<td class="wpv-admin-listing-col-usage">
								<span class="row-title"><?php echo $name . __(' (post type archive)', 'wpv-views'); ?></span>
								<?php
								if ( ! is_null( $post ) ) {
									echo wpv_admin_table_row_actions(
											array( "change_usage js-list-views-usage-action-change-usage" => sprintf( '<a href="#">%s</a>', __( 'Change' , 'wpv-views' ) ) ),
											array( "id" => "list_views_usage_action_" . $post->ID, "data-view-id" => 'view_' . $slug ) );
								}
							?>
							</td>
							<?php
								if ( is_null( $post ) ) {
									?>
									<td colspan="2">
										<a class="button button-small js-create-view-for-archive" data-forwhom="<?php echo esc_attr( $name ); ?>" href="#"><i class="icon-plus"></i><?php _e('Create a WordPress Archive for this loop');?></a>
									</td>
									<?php
								} else {
									?>
									<td class="wpv-admin-listing-col-title">
										<?php
											printf(
													'<a href="%s">%s</a>',
													add_query_arg(
															array( 'page' => 'view-archives-editor', 'view_id' => $post->ID ),
															admin_url( 'admin.php' ) ),
													$post->post_title );
										?>
									</td>
									<?php
								}
							?>
						</tr>
						<?php
					}
				}

				$taxonomies = get_taxonomies('', 'objects');
				$exclude_tax_slugs = array();
				$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );
				foreach ( $taxonomies as $category_slug => $category ) {
					if ( in_array( $category_slug, $exclude_tax_slugs ) ) {
						continue;
					}
					// Only show taxonomies with show_ui set to TRUE
					if ( !$category->show_ui ) {
						continue;
					}
					$name = $category->name;
					$alternate = ' alternate' == $alternate ? '' : ' alternate';
					$name = $category->name;
					$label = $category->labels->singular_name;
					$post = null;
					if ( isset( $options['view_taxonomy_loop_'.$name] ) ) {
						$post = get_post( $options['view_taxonomy_loop_' . $name] );
					}
					?>
					<tr class="js-wpv-view-list-row<?php echo $alternate; ?>">
						<td class="wpv-admin-listing-col-usage">
							<span class="row-title"><?php echo $label . __(' (taxonomy archive)', 'wpv-views'); ?></span>
							<?php
								if ( ! is_null( $post ) ) {
									echo wpv_admin_table_row_actions(
											array( "change_usage js-list-views-usage-action-change-usage" => sprintf( '<a href="#">%s</a>', __( 'Change' , 'wpv-views' ) ) ),
											array( "id" => "list_views_usage_action_" . $post->ID, "data-view-id" => 'view_taxonomy_loop_' . $name ) );
								}
							?>
						</td>
						<?php
							if ( is_null( $post ) ) {
								?>
								<td colspan="2">
									<a class="button button-small js-create-view-for-archive" data-forwhom="<?php echo esc_attr( $label ); ?>" href="#"><i class="icon-plus"></i><?php _e('Create a WordPress Archive for this loop');?></a>
								</td>
								<?php
							} else {
								?>
								<td class="wpv-admin-listing-col-title">
									<?php
										printf(
												'<a href="%s">%s</a>',
												add_query_arg(
														array( 'page' => 'view-archives-editor', 'view_id' => $post->ID ),
														admin_url( 'admin.php' ) ),
												$post->post_title );
									?>
								</td>
								<?php
							}
						?>
					</tr>
					<?php
				}
			?>
		</tbody>
	</table>
    <?php
}
