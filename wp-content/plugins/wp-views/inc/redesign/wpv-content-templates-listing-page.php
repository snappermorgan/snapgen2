<?php

function wpv_admin_menu_content_templates_listing_page() {
	?>
	<div class="wrap toolset-views">
		<div class="wpv-views-listing-page">
			<?php
				wp_nonce_field( 'work_view_template', 'work_view_template' );
				$search_term = isset( $_GET["s"] ) ? urldecode( sanitize_text_field($_GET["s"]) ) : '';
			?>
			<div id="icon-views" class="icon32"></div>
			<h2><!-- classname wpv-page-title removed -->
				<?php
					_e( 'Content Templates', 'wpv-views' );

					printf(
							' <a href="#" class="add-new-h2 js-add-new-content-template" data-target="%s">%s</a>',
							add_query_arg( array( 'action' => 'wpv_ct_create_new' ), admin_url( 'admin-ajax.php' ) ),
							__( 'Add new Content Template', 'wpv-views' ) );

					if ( !empty( $search_term ) ) {
						$search_message = __('Search results for "%s"','wpv-views');
						if ( isset( $_GET["status"] ) && 'trash' == sanitize_text_field( $_GET["status"] ) ) {
							$search_message = __('Search results for "%s" in trashed Content Templates', 'wpv-views');
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
								printf(
										'%s. <a href="%s" class="js-wpv-untrash" data-id="%s" data-nonce="%s">%s</a>',
										__( 'Content Template moved to the Trash', 'wpv-views' ),
										add_query_arg(
												array( 'page' => 'view-templates', 'untrashed' => '1' ),
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
							<?php _e('Content Template restored from the Trash', 'wpv-views'); ?>
						</p>
					</div>
					<?php
				}

				if ( isset( $_GET['deleted'] ) && is_numeric( $_GET['deleted'] ) && (int)$_GET['deleted'] == 1 ) {
					?>
					<div id="message" class="updated below-h2">
						<p>
							<?php _e('Content Template permanently deleted', 'wpv-views'); ?>
						</p>
					</div>
					<?php
				}

			if ( isset( $_GET["arrangeby"] ) && sanitize_text_field( $_GET["arrangeby"] ) == 'usage' ) {
				$usage = 'single';
				if ( isset( $_GET["usage"] ) ) {
					$usage = sanitize_text_field($_GET["usage"]);
				}
				wpv_admin_content_template_listing_usage($usage);
			} else {
				wpv_admin_content_template_listing_name();
			}

			?>
		</div> <!-- .wpv-views-listing-page -->
	</div> <!-- .toolset-views -->
	<?php
}


function wpv_admin_content_template_listing_name() {

	$mod_url = array( // array of URL modifiers
		'orderby' => '',
		'order' => '',
		's' => '',
		'items_per_page' => '',
		'paged' => '',
		'status' => ''
	);

	$wpv_args = array(
		'post_type' => 'view-template',
		'posts_per_page' => WPV_ITEMS_PER_PAGE,
		'order' => 'ASC',
		'orderby' => 'title',
		'post_status' => 'publish'
	);

	if ( isset( $_GET["status"] ) && '' != $_GET["status"] ) { // apply post_status coming from the URL parameters
		$wpv_args['post_status'] = sanitize_text_field( $_GET["status"] );
		$mod_url['status'] = sanitize_text_field( $_GET["status"] );
	}

	if ( isset( $_GET["s"] ) && '' != $_GET["s"] ) {
		$s_param = urldecode(sanitize_text_field($_GET["s"]));
		$new_args = $wpv_args;
		$unique_ids = array();

		$new_args['posts_per_page'] = '-1';
		$new_args['s'] = $s_param;
		$query_1 = new WP_Query( $new_args );

		while ($query_1->have_posts()) :
			$query_1->the_post();
			$unique_ids[] = get_the_id();
		endwhile;

		unset($new_args['s']);

		$new_args['meta_query'] =array(
			array(
				'key' => '_wpv-content-template-decription',
				'value' => $s_param,
				'compare' => 'LIKE'
			)
		);
		$query_2 = new WP_Query( $new_args );

		while ($query_2->have_posts()) :
			$query_2->the_post();
			$unique_ids[] = get_the_id();
		endwhile;

		$unique = array_unique($unique_ids);

		if ( count($unique) == 0 ){
			$wpv_args['post__in'] = array('0');
		}else{
			$wpv_args['post__in'] = $unique;
		}

		$mod_url['s'] = sanitize_text_field($_GET["s"]);
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

	$query = new WP_Query( $wpv_args );
	$wpv_count_posts = $query->post_count;
	$wpv_found_posts = $query->found_posts;
	$all_posts = wp_count_posts('view-template');
	$wpv_views_status = array(); // to hold the number of Views in each status
	$wpv_views_status['publish'] = $all_posts->publish;
	$wpv_views_status['trash'] = $all_posts->trash;
	?>

	<?php
		if ( $wpv_views_status['publish'] > 0 || $wpv_views_status['trash'] > 0 ) {
			?>
			<div class="wpv-views-listing-arrange" style="clear:none;float:left">
				<p style="margin-bottom:0"><?php _e('Arrange by','wpv-views'); ?>: </p>
				<ul>
					<li data-sortby="name" class="active"><?php _e('Name','wpv-views') ?></li>
					<li data-sortby="usage-single">
						<?php
							printf(
								'<a href="%s">%s</a>',
								add_query_arg(
									array(
											'page' => 'view-templates',
											'arrangeby' => 'usage',
											'usage' => 'single' ),
									admin_url( 'admin.php' ) ),
								__( 'Usage for single page', 'wpv-views' ) );
						?>
					</li>
					<li data-sortby="usage-post-archives">
						<?php
							printf(
								'<a href="%s">%s</a>',
								add_query_arg(
									array(
											'page' => 'view-templates',
											'arrangeby' => 'usage',
											'usage' => 'post-archives' ),
									admin_url( 'admin.php' ) ),
								__( 'Usage for custom post archives', 'wpv-views' ) );
						?>
					</li>
					<li data-sortby="usage-taxonomy-archives">
						<?php
							printf(
								'<a href="%s">%s</a>',
								add_query_arg(
									array(
											'page' => 'view-templates',
											'arrangeby' => 'usage',
											'usage' => 'taxonomy-archives' ),
									admin_url( 'admin.php' ) ),
								__( 'Usage for taxonomy archives', 'wpv-views' ) );
						?>
					</li>
				</ul>
			</div>

			<ul class="subsubsub" style="clear:left"><!-- links to lists WPA in different statuses -->
				<li>
					<?php
						$is_plain_publish_current_status = ( $wpv_args['post_status'] == 'publish' && !isset( $_GET["s"] ) );
						printf(
								'<a href="%s" %s>%s</a> (%s) | ',
								add_query_arg(
										array( 'page' => 'view-templates', 'status' => 'publish' ),
										admin_url( 'admin.php' ) ),
								$is_plain_publish_current_status ?  ' class="current" ' : '',
								__( 'Published', 'wpv-views' ),
								$wpv_views_status['publish'] );

					?>
				</li>
				<li>
					<?php
						$is_plain_trash_current_status = ( $wpv_args['post_status'] == 'trash' && !isset( $_GET["s"] ) );
						printf(
								'<a href="%s" %s>%s</a> (%s)',
								add_query_arg(
										array( 'page' => 'view-templates', 'status' => 'trash' ),
										admin_url( 'admin.php' ) ),
								$is_plain_trash_current_status ?  ' class="current" ' : '',
								__( 'Trash', 'wpv-views' ),
								$wpv_views_status['trash'] );
					?>
				</li>
			</ul>

			<?php
				if ( $wpv_found_posts > 0 ) {
					?>
					<form id="posts-filter" action="" method="get">
						<p class="search-box">
							<label class="screen-reader-text" for="post-search-input"><?php _e('Search Views:', 'wpv-views') ?></label>
							<?php $search_term = isset( $_GET["s"] ) ? urldecode( sanitize_text_field($_GET["s"]) ) : ''; ?>
							<input type="search" id="ct-post-search-input" name="s" value="<?php echo $search_term; ?>">
							<input type="submit" name="" id="ct-search-submit" class="button" value="<?php echo htmlentities( __('Search Content Templates', 'wpv-views'), ENT_QUOTES ); ?>">
							<input type="hidden" name="paged" value="1" />
						</p>
					</form>
					<?php
				}
			?>
			<?php
		} else {
			?>
			<p class="wpv-view-not-exist">
			<?php _e('Content Templates let you design single pages.','wpv-views'); ?>
			</p>
			<p class="add-new-view">
				<button class="button js-add-new-content-template"
				data-target="<?php echo add_query_arg( array( 'action' => 'wpv_ct_create_new' ), admin_url( 'admin-ajax.php' ) ); ?>">
					<i class="icon-plus"></i><?php _e('Add new Content Template','wpv-views') ?>
				</button>
			</p><?php
		}

		if ( $wpv_count_posts == 0 && ( $wpv_views_status['publish'] > 0 || $wpv_views_status['trash'] > 0 ) ) {
			// When no posts found
			if ( isset( $_GET["s"] ) && '' != $_GET["s"] ) {
				if ( isset( $_GET["status"] ) && $_GET["status"] == 'trash' ) {
					// Searching in trash
					?>
					<div class="wpv-views-listing views-empty-list">
						<p>
							<?php
								_e( 'No Content Templates in trash matched your criteria.', 'wpv-views' );
								printf(
										'<a class="button-secondary" href="%s">%s</a>',
										wpv_maybe_add_query_arg(
												array(
														'page' => 'view-templates',
														'orderby' => $mod_url['orderby'],
														'order' => $mod_url['order'],
														'items_per_page' => $mod_url['items_per_page'],
														'paged' => '1',
														'status' => 'trash' ),
												admin_url( 'admin.php' ) ),
										__( 'Return', 'wpv-views' ) );
							?>
						</p>
					</div>
					<?php
				} else {
					// Normal search
					?>
					<div class="wpv-views-listing views-empty-list">
						<p>
							<?php
								_e( 'No Content Templates matched your criteria.', 'wpv-views' );
								printf(
										'<a class="button-secondary" href="%s">%s</a>',
										wpv_maybe_add_query_arg(
												array(
														'page' => 'view-templates',
														'orderby' => $mod_url['orderby'],
														'order' => $mod_url['order'],
														'items_per_page' => $mod_url['items_per_page'],
														'paged' => '1' ),
												admin_url( 'admin.php' ) ),
										__( 'Return', 'wpv-views' ) );
							?>
						</p>
					</div>
					<?php
				}
			} else {
				if ( isset( $_GET["status"] ) && $_GET["status"] == 'trash' ) {
					// No items in trash
					?>
					<div class="wpv-views-listing views-empty-list">
						<p>
							<?php
								_e( 'No Content Templates in trash.', 'wpv-views' );
								printf(
										'<a class="button-secondary" href="%s">%s</a>',
										wpv_maybe_add_query_arg(
												array(
														'page' => 'view-templates',
														'orderby' => $mod_url['orderby'],
														'order' => $mod_url['order'],
														'items_per_page' => $mod_url['items_per_page'],
														'paged' => '1' ),
												admin_url( 'admin.php' ) ),
										__( 'Return', 'wpv-views' ) );
							?>
						</p>
					</div>
					<?php
				} else {
					?>
					<p class="wpv-view-not-exist">
						<?php _e('Content Templates let you design single pages.','wpv-views'); ?>
					</p>
					<p class="add-new-view">
						<button class="button js-add-new-content-template"
								data-target="<?php echo add_query_arg( array( 'action' => 'wpv_ct_create_new' ), admin_url( 'admin-ajax.php' ) ); ?>">
							<i class="icon-plus"></i><?php _e( 'Add new Content Template', 'wpv-views') ?>
						</button>
					</p>
					<?php
				}
			}
		} else if ( $wpv_count_posts != 0 ) {
			global $wpdb;

			?>
		<table class="wpv-views-listing widefat">

		<!-- section for: sort by name -->
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
						$status = '';
						if ( $wpv_args['orderby'] === 'title' ) {
							$column_active = ' views-list-sort-active';
							$column_sort_to = ( $wpv_args['order'] === 'ASC' ) ? 'DESC' : 'ASC';
							$column_sort_now = $wpv_args['order'];
						}
						if ( isset($_GET['status']) && $_GET['status'] == 'trash' ){
							$status = 'trash';
						}
					?>
					<th class="wpv-admin-listing-col-title">
						<?php
							printf(
									'<a href="%s" class="%s" data-orderby="title">%s <i class="%s"></i></a>',
									wpv_maybe_add_query_arg(
											array(
													'page' => 'view-templates',
													'status' => $status,
													'orderby' => 'title',
													'order' => $column_sort_to,
													's' => $mod_url['s'],
													'items_per_page' => $mod_url['items_per_page'],
													'paged' => $mod_url['paged'] ),
											admin_url( 'admin.php' ) ),
									'js-views-list-sort views-list-sort ' . $column_active,
									__( 'Title', 'wpv-views' ),
									( 'DESC'  === $column_sort_now ) ? 'icon-sort-by-alphabet-alt' : 'icon-sort-by-alphabet' );
						?>
					</th>
					<th class="wpv-admin-listing-col-usage js-wpv-col-two"><?php _e('Used on','wpv-views') ?></th>
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
							printf(
									'<a href="%s" class="%s" data-orderby="date">%s <i class="%s"></i></a>',
									wpv_maybe_add_query_arg(
											array(
													'page' => 'view-templates',
													'status' => $status,
													'orderby' => 'date',
													'order' => $column_sort_to,
													's' => $mod_url['s'],
													'items_per_page' => $mod_url['items_per_page'],
													'paged' => $mod_url['paged'] ),
											admin_url( 'admin.php' ) ),
									'js-views-list-sort views-list-sort ' . $column_active,
									__( 'Date', 'wpv-views' ),
									( 'DESC'  === $column_sort_now ) ? 'icon-sort-by-attributes-alt' : 'icon-sort-by-attributes' );
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
				while ( $query->have_posts() ) :
					$query->the_post();
					$post = get_post( get_the_id() );
					$wpv_content_template_decription  = get_post_meta( $post->ID, '_wpv-content-template-decription', true );
					$alternate = ( ' alternate' == $alternate ) ? '' : ' alternate';

					?>
					<tr id="wpv_ct_list_row_<?php echo $post->ID; ?>" class="js-wpv-ct-list-row<?php echo $alternate; ?>">
						<td class="wpv-admin-listing-col-title post-title page-title column-title">
							<span class="row-title">
								<?php
									if ( $wpv_args['post_status'] == 'trash' ) {
										echo $post->post_title;
									} else {
										printf(
												'<a href="%s">%s</a>',
												add_query_arg(
														array( 'action' => 'edit', 'post' => $post->ID ),
														admin_url( 'post.php' ) ),
												$post->post_title );
									}
								?>
							</span>
							<?php
								if ( !empty( $wpv_content_template_decription ) ) {
									?>
									<p class="desc">
										<?php echo nl2br($wpv_content_template_decription)?>
									</p>
									<?php
								}

								/* Generate and show row actions.
								 * Note that we want to add also 'simple' action names to the action list because
								 * they get echoed as a class of the span tag and get styled from WordPress core css
								 * accordingly (e.g. trash in different colour than the rest) */
								$row_actions = array();

								$template_id = $post->ID;
								$asigned_count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} JOIN {$wpdb->posts} p WHERE
										meta_key='_views_template' AND meta_value='{$template_id}' AND post_id = p.ID AND p.post_status NOT IN  ('auto-draft') AND p.post_type != 'revision'" );

								if ( 'publish' == $wpv_args['post_status'] ) {
									$row_actions['edit'] = sprintf(
											'<a href="%s">%s</a>',
											add_query_arg(
													array( 'action' => 'edit', 'post' => $post->ID ),
													admin_url( 'post.php' ) ),
											__( 'Edit', 'wpv-views' ) );
									/* Note that hash in <a href="#"> is present so the link behaves like a link.
									 * <a href=""> causes problems with colorbox and with mere <a> the mouse cursor
									 * doesn't change when hovering over the link. */
									$row_actions['change js-list-ct-action-change'] = sprintf( '<a href="#">%s</a>', __( 'Change template usage', 'wpv-views' ) );
									$row_actions['duplicate js-list-ct-action-duplicate'] = sprintf( '<a href="#">%s</a>', __( 'Duplicate', 'wpv-views' ) );
									$row_actions['trash js-list-ct-action-trash'] = sprintf( '<a href="#">%s</a>', __( 'Move to trash', 'wpv-views' ) );
								} else if ( 'trash' == $wpv_args['post_status'] ) {
									$row_actions['restore-from-trash js-list-ct-action-restore-from-trash'] = sprintf( '<a href="#">%s</a>', __( 'Restore from trash', 'wpv-views' ) );
									$row_actions['delete js-list-ct-action-delete'] = sprintf( '<a href="#">%s</a>', __( 'Delete', 'wpv-views' ) );
								}

								echo wpv_admin_table_row_actions( $row_actions,	array(
											"data-ct-id" => $post->ID,
											"data-postcount" => $asigned_count,
											"data-ct-name" => htmlentities( $post->title, ENT_QUOTES ),
											"data-viewactionnonce" => wp_create_nonce( 'wpv_view_listing_actions_nonce' ),
											// Used by the "duplicate" action
											"data-msg" => htmlentities( __( 'Enter new title','wpv-views'), ENT_QUOTES ) ) );
							?>
						</td>
						<td class="wpv-admin-listing-col-usage">
							<?php echo wpv_content_template_used_for_list( $post->ID ); ?>
						</td>
						<td class="wpv-admin-listing-col-date">
							<?php echo get_the_time(get_option('date_format'), $post->ID); ?>
						</td>
					</tr>
					<?php
				endwhile;
				?>
			</tbody>
		</table>

		<p class="add-new-view">
			<button class="button js-add-new-content-template"
					data-target="<?php echo add_query_arg( array( 'action' => 'wpv_ct_create_new' ), admin_url( 'admin-ajax.php' ) ); ?>">
				<i class="icon-plus"></i><?php _e( 'Add new Content Template','wpv-views' ) ?>
			</button>
		</p>

		<?php
	}

	wpv_admin_listing_pagination( 'view-templates', $wpv_found_posts, $wpv_args["posts_per_page"], $mod_url );

	?>
	<div class="popup-window-container">

		<div class="wpv-dialog js-remove-content-template-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Delete Content Template','wpv-views'); ?></h2>
			</div>
			<div class="wpv-dialog-content">
				<p><?php echo sprintf( __('There are %s single posts that are currently using this template.','wpv-views'), '<span class="js-ct-single-postcount"></span>'); ?></p>
				<p><?php _e('Are you sure you want to delete it?', 'wpv-views');?>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views'); ?></button>
				<button class="button button-primary js-remove-template-permanent"><?php _e('Delete','wpv-views'); ?></button>
			</div>
		</div>

		<div class="wpv-dialog js-duplicate-ct-dialog">
			<div class="wpv-dialog-header">
				<h2><?php _e('Duplicate Content Template','wpv-views') ?></h2>
			</div>
			<div class="wpv-dialog-content">
		<p>
			<label for="duplicated_ct_name"><?php _e('Name this Content Template','wpv-views'); ?></label>
			<input type="text" value="" class="js-duplicated-ct-name" placeholder="<?php _e('Enter name here','wpv-views') ?>" name="duplicated_ct_name">
		</p>
		<div class="js-ct-duplicate-error"></div>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-secondary js-duplicate-ct" disabled><?php _e('Duplicate','wpv-views') ?></button>
			</div>
		</div> <!-- .js-duplicate-view-dialog -->

	</div>
	<?php
}

function wpv_admin_content_template_listing_usage( $usage = 'single' ) {
	?>
	<div class="wpv-views-listing-arrange">
		<p><?php _e('Arrange by','wpv-views'); ?>: </p>
		<ul>
			<li data-sortby="name">
				<a href="<?php echo add_query_arg( array( 'page' => 'view-templates' ), admin_url( 'admin.php' ) ); ?>"><?php _e('Name','wpv-views'); ?></a>
			</li>

			<?php $is_single_usage = ( $usage == 'single' ); ?>
			<li data-sortby="usage-single" <?php if( $is_single_usage ) { echo 'class="active"'; } ?> >
				<?php
					if( $is_single_usage ) {
						_e( 'Usage for single page', 'wpv-views' );
					} else {
						printf( '<a href="%s">%s</a>',
								add_query_arg(
										array(
												'page' => 'view-templates',
												'arrangeby' => 'usage',
												'usage' => 'single' ),
										admin_url( 'admin.php' ) ),
								__( 'Usage for single page', 'wpv-views' ) );
					}
				?>
			</li>

			<?php $is_post_archives_usage = ( $usage == 'post-archives' ); ?>
			<li data-sortby="usage-post-archives" <?php if ( $is_post_archives_usage ) { echo 'class="active"'; } ?> >
				<?php
					if( $is_post_archives_usage ) {
						_e( 'Usage for custom post archives', 'wpv-views' );
					} else {
						printf( '<a href="%s">%s</a>',
								add_query_arg(
										array(
												'page' => 'view-templates',
												'arrangeby' => 'usage',
												'usage' => 'post-archives' ),
										admin_url( 'admin.php' ) ),
								__( 'Usage for custom post archives', 'wpv-views' ) );
					}
				?>
			</li>

			<?php $is_taxonomy_archive_usage = ( $usage == 'taxonomy-archives' ); ?>
			<li data-sortby="usage-taxonomy-archives" <?php if ( $is_taxonomy_archive_usage ) { echo 'class="active"'; } ?> >
				<?php
					if( $is_taxonomy_archive_usage ) {
						_e( 'Usage for taxonomy archives', 'wpv-views' );
					} else {
						printf( '<a href="%s">%s</a>',
								add_query_arg(
										array(
												'page' => 'view-templates',
												'arrangeby' => 'usage',
												'usage' => 'taxonomy-archives' ),
										admin_url( 'admin.php' ) ),
								__( 'Usage for taxonomy archives', 'wpv-views' ) );
					}
				?>
			</li>
		</ul>
	</div>

	<table class="wpv-views-listing widefat">

		<thead>
			<tr>
				<th class="wpv-admin-listing-col-usage"><?php _e('Used on','wpv-views') ?></th>
				<th class="wpv-admin-listing-col-used-title"><?php _e('Template used','wpv-views') ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th class="wpv-admin-listing-col-usage"><?php _e('Used on','wpv-views') ?></th>
				<th class="wpv-admin-listing-col-used-title"><?php _e('Template used','wpv-views') ?></th>
			</tr>
		</tfoot>
		<!-- / section for: sort by name -->

		<tbody class="js-wpv-views-listing-body">
			<?php
			echo wpv_admin_menu_content_template_listing_by_type_row('usage-' . $usage);
			?>
		</tbody>
	</table>

	<div class="popup-window-container"> <!-- placeholder for static colorbox popups -->

		<!-- popup: unlink Template -->

		<div class="wpv-dialog js-single-unlink-template-dialog">
			<div class="wpv-dialog-header">
				<h2><?php echo sprintf( __('Clear single %s','wpv-views'), '<strong class="js-single-unlink-label"></strong>'); ?></h2>
			</div>
			<div class="wpv-dialog-content">
				<p><?php echo sprintf( __('There is no general Content Template asigned to single %s, but %s individual %s have a Content Template asigned to them.','wpv-views'), '<strong class="js-single-unlink-label"></strong>', '<strong class="js-single-unlink-number"></strong>', '<strong class="js-single-unlink-label"></strong>'); ?></p>
				<p><?php echo __('Would you like to clear them?','wpv-views'); ?></p>
			</div>
			<div class="wpv-dialog-footer">
				<button class="button js-dialog-close"><?php _e('Cancel','wpv-views') ?></button>
				<button class="button button-primary js-single-unlink-template-ok" data-slug="" data-nonce="<?php echo wp_create_nonce( 'wpv_single_unlink_template_nonce' ); ?>"><?php _e('Clear','wpv-views') ?></button>
			</div>
		</div> <!-- .js-delete-view-dialog -->

	</div>
	<?php
}

function wpv_content_template_used_for_list( $ct_id ){
	global $WP_Views, $wpdb;
	$list = '';
	$show_single = $show_loop = $show_tax = 0;
	$options = $WP_Views->get_options();
	$post_types_array = wpv_get_pt_tax_array();

	for ( $i=0; $i<count($post_types_array['single_post']); $i++ ) {
		$type = $post_types_array['single_post'][$i][0];
		$label = $post_types_array['single_post'][$i][1];
		if ( isset($options['views_template_for_' . $type]) && $options['views_template_for_' . $type] == $ct_id)   {
			$list .= '<li>' . $label . __(' (single)', 'wpv-views');
				$posts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE post_type='{$type}' AND post_status!='auto-draft'" );
				$count = sizeof( $posts );
				if ( $count > 0 ) {
					$posts = "'" . implode( "','", $posts ) . "'";
					$set_count = $wpdb->get_var( "SELECT COUNT(post_id) FROM {$wpdb->postmeta} WHERE
					meta_key='_views_template' AND meta_value='{$options['views_template_for_' . $type]}'
					AND post_id IN ({$posts})" );
					if ( ( $count - $set_count ) > 0 ) {
						$list .= sprintf(
								'<span class="%s"><a class="%s" data-target="%s"> %s</a></span>',
								'js-alret-icon-hide-' . $type,
								'button button-small button-leveled icon-warning-sign js-apply-for-all-posts js-alret-icon-hide-' . $type,
								add_query_arg(
										array(
												'action' => 'wpv_ct_update_posts',
												'type' => $type,
												'tid' => $ct_id,
												'wpnonce' => wp_create_nonce( 'work_view_template' ) ),
										admin_url( 'admin-ajax.php' ) ),
								sprintf( __( 'Bind %u %s ', 'wpv-views' ), $count - $set_count, $label ) );
					}
				}
			$list .= '</li>';
		}
	}

	for ($i=0;$i<count($post_types_array['archive_post']);$i++){
		$type = $post_types_array['archive_post'][$i][0];
		$label = $post_types_array['archive_post'][$i][1];
		if ( isset($options['views_template_archive_for_' . $type]) && $options['views_template_archive_for_' . $type] == $ct_id)   {
			$list .= '<li>' . $label . __(' (post type archive)','wpv-views') . '</li>';
		 }
	}

	for ($i=0;$i<count($post_types_array['taxonomy_post']);$i++){
		$type = $post_types_array['taxonomy_post'][$i][0];
		$label = $post_types_array['taxonomy_post'][$i][1];
		if ( isset($options['views_template_loop_' . $type]) && $options['views_template_loop_' . $type] == $ct_id)   {
			$list .= '<li>' . $label . __(' (taxonomy archive)','wpv-views') . '</li>';
		 }
	}
	if ( !empty($list) ){
		$list = '<ul class="wpv-taglike-list">' . $list . '</ul>';
	}
	else{
	   $list = '<span>' . __('No Post types/Taxonomies assigned','wpv-views') . '</span>';
	}
	return $list;
}

function wpv_get_pt_tax_array(){
   static $post_types_array;
   static $taxonomies_array;
   static $wpv_posts_array;
   if ( !is_array($post_types_array) ){
	   $post_types = get_post_types( array('public' => true), 'objects' );
   }
   if ( !is_array($taxonomies_array) ){
	   $taxonomies = get_taxonomies( '', 'objects' );
   }
   $exclude_tax_slugs = array();
	$exclude_tax_slugs = apply_filters( 'wpv_admin_exclude_tax_slugs', $exclude_tax_slugs );

   if ( is_array($wpv_posts_array) ){
	   return $wpv_posts_array;
   }
	$wpv_posts_array['single_post'] = array();
	$wpv_posts_array['archive_post'] = array();
   foreach ( $post_types as $post_type ) {
		$wpv_posts_array['single_post'][] = array( $post_type->name, $post_type->label );
		if (!in_array($post_type->name, array('post', 'page', 'attachment')) && $post_type->has_archive ) {
			// take out Posts, Pages and Attachments for post types archive loops; take out posts without archives too
			$wpv_posts_array['archive_post'][] = array( $post_type->name, $post_type->label );
		}
   }
	$wpv_posts_array['taxonomy_post'] = array();
   foreach ( $taxonomies as $category_slug => $category ) {
	   if ( in_array($category_slug, $exclude_tax_slugs ) ) {
				continue;
	   }
	   if ( !$category->show_ui ) {
			continue; // Only show taxonomies with show_ui set to TRUE
		}
		$wpv_posts_array['taxonomy_post'][] = array( $category->name, $category->labels->name );
   }

   return $wpv_posts_array;
}

// TODO check if the action URL parameter is needed when creating a CT
function wpv_admin_menu_content_template_listing_by_type_row( $sort, $page = 0 ) {
	global $WP_Views, $post, $wpdb;
	$options = $WP_Views->get_options();
	// $post_types = get_post_types( array('public' => true), 'objects' );
	$post_types_array = wpv_get_pt_tax_array();

	ob_start();
	if ( $sort == 'usage-single' ){

		$counter = count( $post_types_array['single_post'] );
		$alternate = '';
		for ( $i = 0; $i < $counter; ++$i ) {
			$type = $post_types_array['single_post'][ $i ][0];
			$label = $post_types_array['single_post'][ $i ][1];
			$alternate = ' alternate' == $alternate ? '' : ' alternate';

			?>
			<tr id="wpv_ct_list_row_<?php echo $type; ?>" class="js-wpv-ct-list-row<?php echo $alternate; ?>">

				<td class="wpv-admin-listing-col-usage post-title page-title column-title">
					<span class="row-title">
						<?php echo $label;?>
					</span>
					<?php
						$row_actions = array(
								"change_pt js-list-ct-action-change-pt" => sprintf( '<a href="#">%s</a>', __('Change Content Template','wpv-views') ) );

						echo wpv_admin_table_row_actions( $row_actions,	array(
								"data-msg" => 1,
								"data-sort" => $sort,
								"data-pt" => 'views_template_for_' . $type ) );
					?>
				</td>

				<td class="wpv-admin-listing-col-used-title">
					<ul>
						<?php
							$add_button = sprintf(
									'<a class="button button-small" data-disabled="1"
											href="%s">
										<i class="icon-plus"></i>
										%s
									</a>',
									add_query_arg(
											array(
													'post_type' => 'view-template',
													'action' => 'wpv_ct_create_new',
													'post_title' => urlencode( __( 'Content template for ','wpv-views' ) . $label ),
													'ct_selected' => 'views_template_for_' . $type,
													'toggle' => '1,0,0' ),
											admin_url( 'post-new.php' ) ),
									sprintf( __( 'Create a Content Template for single %s', 'wpv-views' ), $label ) );

							// TODO get_posts or explanation why is it done this way (optimalization?)
							$posts = $wpdb->get_col( "SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE post_type='{$type}' AND post_status!='auto-draft'" );
							$count = sizeof( $posts );
							$posts_ids = "'" . implode( "','", $posts ) . "'";

							if ( isset( $options[ 'views_template_for_' . $type ] ) ) {
								if ( $options[ 'views_template_for_' . $type ] != 0 ) {
									$template = get_post( $options[ 'views_template_for_' . $type ] );
									if ( is_object( $template ) ) {
										printf(
												'<a href="%s">%s</a>',
												add_query_arg( array( 'post' => $template->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ),
												$template->post_title );
										if ( $count > 0 ) {
											$set_count = $wpdb->get_var(
													"SELECT COUNT(post_id) FROM {$wpdb->postmeta}
													WHERE meta_key='_views_template'
														AND meta_value='{$options['views_template_for_' . $type]}'
														AND post_id IN ({$posts_ids})" );
											if ( ( $count - $set_count ) > 0 ) {
												?>
												<span class="js-alret-icon-hide-<?php echo $type; ?>">
													<?php
														printf(
																'<a class="%s" data-target="%s"> %s</a>',
																'button button-small button-leveled icon-warning-sign js-apply-for-all-posts',
																add_query_arg(
																		array(
																				'action' => 'wpv_ct_update_posts',
																				'type' => $type,
																				'tid' => $template->ID,
																				'wpnonce' => wp_create_nonce( 'work_view_template' ) ),
																		admin_url( 'admin-ajax.php' ) ),
																sprintf( __( 'Bind %u %s ', 'wpv-views' ), $count - $set_count, $label ) );
													?>
												</span>
												<?php
											}
										}
									} else {
										echo $add_button;
									}
								} else {
									echo $add_button;

									$set_count = $wpdb->get_var(
											"SELECT COUNT(post_id) FROM {$wpdb->postmeta}
											WHERE meta_key='_views_template'
												AND meta_value!='0'
												AND post_id IN ({$posts_ids})" );
									if ( $set_count > 0) {
										?>
										<a class="button button-small js-single-unlink-template-open-dialog" href="#"
												data-unclear="<?php echo $set_count; ?>"
												data-slug="<?php echo $type; ?>"
												data-label="<?php echo htmlentities( $label, ENT_QUOTES ); ?>">
											<i class="icon-unlink"></i>
											<?php echo sprintf( __('Clear %d %s', 'wpv-views'), $set_count, $label ); ?>
										</a>
										<?php
									}
								}
							} else {
								echo $add_button;

								$set_count = $wpdb->get_var(
										"SELECT COUNT(post_id) FROM {$wpdb->postmeta}
										WHERE meta_key='_views_template'
											AND meta_value!='0'
											AND post_id IN ({$posts_ids})" );
								if ( $set_count > 0 ) {
									?>
									<a class="button button-small js-single-unlink-template-open-dialog" href="#"
											data-unclear="<?php echo $set_count; ?>"
											data-slug="<?php echo $type; ?>"
											data-label="<?php echo htmlentities( $label, ENT_QUOTES ); ?>">
										<i class="icon-unlink"></i>
										<?php echo sprintf( __('Clear %d %s', 'wpv-views'), $set_count, $label ); ?>
									</a>
									<?php
								}
							}
						?>
					</ul>
				</td>
			</tr>
			<?php
		}

	} else if ( $sort == 'usage-post-archives' ){

		$alternate = '';
		$counter = count( $post_types_array['archive_post'] );
		for ( $i = 0; $i < $counter; ++$i ) {

			$type = $post_types_array['archive_post'][ $i ][0];
			$label = $post_types_array['archive_post'][ $i ][1];
			$add_button = sprintf(
					'<a class="button button-small" data-disabled="1" href="%s"><i class="icon-plus"></i> %s</a>',
					add_query_arg(
							array(
									'post_type' => 'view-template',
									'action' => 'wpv_ct_create_new',
									'post_title' => urlencode( __( 'Content template for ', 'wpv-views' ) . $label ),
									'ct_selected' => 'views_template_archive_for_' . $type,
									'toggle' => '0,1,0' ),
							admin_url( 'post-new.php' ) ),
					__( 'Add a new Content Template for this post type', 'wpv-views' ) );

			$alternate = ' alternate' == $alternate ? '' : ' alternate';
			?>
			<tr id="wpv_ct_list_row_<?php echo $type; ?>" class="js-wpv-ct-list-row<?php echo $alternate; ?>">
				<td class="post-title page-title column-title">
					<span class="row-title">
						<?php echo $label; ?>
					</span>
					<?php
						$row_actions = array(
								"change_pt js-list-ct-action-change-pt" => sprintf( '<a href="#">%s</a>', __( 'Change Content Template', 'wpv-views' ) ) );

						echo wpv_admin_table_row_actions( $row_actions,	array(
								"data-msg" => 1,
								"data-sort" => $sort,
								"data-pt" => 'views_template_archive_for_' . $type ) );
					?>
				</td>
				<td>
					<ul>
						<?php
							if ( isset( $options[ 'views_template_archive_for_' . $type ] )
									&& $options[ 'views_template_archive_for_' . $type ] != 0) {
								$post = get_post( $options[ 'views_template_archive_for_' . $type ] );
								if ( is_object( $post ) ) {
									printf(
											'<a href="%s">%s</a>',
											add_query_arg(
													array( 'post' => $post->ID, 'action' => 'edit' ),
													admin_url( 'post.php' ) ),
											$post->post_title );
								} else {
									echo $add_button;
								}
							} else {
								echo $add_button;
							}
						?>
					</ul>
				</td>
			</tr>
			<?php
		}

	} else if ( $sort == 'usage-taxonomy-archives' ){

		$counter = count( $post_types_array['taxonomy_post'] );
		$alternate = '';

		for ( $i = 0; $i < $counter; ++$i ) {
			$type = $post_types_array['taxonomy_post'][ $i ][0];
			$label = $post_types_array['taxonomy_post'][ $i ][1];

			$add_button = sprintf(
					'<a class="button button-small js-wpv-ct-create-new-for-usage" data-disabled="1"
							data-title="%s" data-usage="%s" href="%s">
						<i class="icon-plus"></i>
						%s
					</a>',
					urlencode( __( 'Content template for ', 'wpv-views' ) . $label ),
					'views_template_loop_' . $type,
					add_query_arg(
							array(
									'post_type' => 'view-template',
									'action' => 'wpv_ct_create_new',
									'post_title' => urlencode( __( 'Content template for ', 'wpv-views' ) . $label ),
									'ct_selected' => 'views_template_loop_' . $type,
									'toggle' => '0,0,1' ),
							admin_url( 'post-new.php' ) ),
					__( 'Add a new Content Template for this taxonomy', 'wpv-views' ) );

			$alternate = ' alternate' == $alternate ? '' : ' alternate';

			?>
			<tr id="wpv_ct_list_row_<?php echo $type; ?>" class="js-wpv-ct-list-row<?php echo $alternate; ?>">
				<td class="post-title page-title column-title">
					<span class="row-title">
						<?php echo $label;?>
					</span>
					<?php
						$row_actions = array(
								"change_pt js-list-ct-action-change-pt" => sprintf( '<a href="#">%s</a>', __( 'Change Content Template', 'wpv-views' ) ) );

						echo wpv_admin_table_row_actions( $row_actions,	array(
								"data-msg" => 2,
								"data-sort" => $sort,
								"data-pt" => 'views_template_loop_' . $type ) );
					?>
				</td>
				<td>
					<ul>
						<?php
							if ( isset( $options[ 'views_template_loop_' . $type ] )
									&& $options[ 'views_template_loop_' . $type ] != 0 ) {
								$post = get_post( $options['views_template_loop_' . $type] );
								if ( is_object( $post ) ) {
									printf(
											'<a href="%s">%s</a>',
											add_query_arg(
													array( 'post' => $post->ID, 'action' => 'edit' ),
													admin_url( 'post.php' ) ),
											$post->post_title );
								} else {
									echo $add_button;
								}
							} else {
								echo $add_button;
							}
						?>
					</ul>
				</td>
			</tr>
			<?php
		}
	}

	$row = ob_get_contents();
	ob_end_clean();

	return $row;
}