<?php

if ( ! class_exists( 'Site_Copier_Terms' ) ) {
	class Site_Copier_Terms extends Site_Copier {

		public function get_default_args() {
			return array(
				'update_relationships' => false, // all or array with post, page or any post_type
				'posts_mapping' => array()
			);
		}


		public function copy() {
			global $wpdb;

			if ( ! function_exists( 'wp_delete_link' ) )
				include_once( ABSPATH . 'wp-admin/includes/bookmark.php' );

			// Remove current terms
			$taxonomies = get_taxonomies();
			if ( isset( $taxonomies['nav_menu'] ) )
				unset( $taxonomies['nav_menu'] );

			$all_terms = get_terms( $taxonomies, array( 'hide_empty' => false ) );
			foreach ( $all_terms as $term )
				$result = wp_delete_term( $term->term_id, $term->taxonomy );

			unset( $all_terms );

			// Remove current links
			$all_links = get_bookmarks();
			foreach ( $all_links as $link )
				wp_delete_link( $link->link_id );


			switch_to_blog( $this->source_blog_id );

			// Need to check what post types are in the source blog
			$exclude_post_types = '("' . implode( '","', array( 'nav_menu_item' ) ) . '")';
			$post_types = $wpdb->get_col( "SELECT DISTINCT post_type FROM $wpdb->posts WHERE post_type NOT IN $exclude_post_types" );

			$source_posts_ids = get_posts( array(
				'ignore_sticky_posts' => true,
				'posts_per_page' => -1,
				'post_type' => $post_types,
				'fields' => 'ids',
				'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private', 'inherit' ),
			) );

			$_taxonomies = $wpdb->get_col( "SELECT DISTINCT taxonomy FROM $wpdb->term_taxonomy" );
	        $taxonomies = array();
	        foreach ( $_taxonomies as $taxonomy )
	            $taxonomies[ $taxonomy ] = $taxonomy;

	        if ( isset( $taxonomies['nav_menu'] ) )
				unset( $taxonomies['nav_menu'] );

			$source_terms = $this->get_terms( $taxonomies );

			$_source_links = get_bookmarks();
			$source_links = array();
			foreach ( $_source_links as $source_link ) {
				$item = $source_link;
				$object_terms = $this->get_object_terms( $source_link->link_id, array( 'link_category' ) );
				if ( ! empty( $object_terms ) && ! is_wp_error( $object_terms ) )
					$item->terms = $object_terms;

				$source_links[] = $item;

			}

			restore_current_blog();

			// Now insert the links
			$mapped_links = array();
			foreach ( $source_links as $link ) {
				$new_link = (array)$link;
				unset( $new_link['link_id'] );

				$new_link_id = wp_insert_link( $new_link );
				if ( ! is_wp_error( $new_link_id ) )
					$mapped_links[ $link->link_id ] = $new_link_id;
				
			}

			// Deprecated
			do_action( 'blog_templates-copy-links', $this->template, get_current_blog_id(), $this->user_id );

			/**
             * Fires after the links have been copied.
             *
             * @param Integer $user_id Blog Administrator ID.
             * @param Integer $source_blog_id Source Blog ID from where we are copying the links.
             * @param Array $template Only applies when using New Blog Templates. Includes the template attributes.
             */
			do_action( 'wpmudev_copier-copy-links', $this->user_id, $this->source_blog_id, $this->template );
			
		
			// Insert the terms
			$mapped_terms = array();
			foreach ( $source_terms as $term ) {

				$term_args = array(
					'description' => $term->description,
					'slug' => $term->slug
				);

				$new_term = wp_insert_term( $term->name, $term->taxonomy, $term_args );
				
				if ( is_wp_error( $new_term ) ) {
					// Usually Uncategorized cannot be deleted, we need to check if it's already in the destination blog and map it
					$error_code = $new_term->get_error_code();
					if ( 'term_exists' === $error_code ) {
						$new_term = get_term_by( 'slug', $term->slug, $term->taxonomy );
						if ( ! is_object( $new_term ) || is_wp_error( $new_term ) )
							continue;

						$new_term = array(
							'term_id' => $new_term->term_id
						);
					}

				}

				if ( ! is_wp_error( $new_term ) ) {
					// Check if the term has a parent
					$mapped_terms[ $term->term_id ] = absint( $new_term['term_id'] );
				}

			}

			// Now update terms parents
			foreach ( $source_terms as $term )
				if ( ! empty( $term->parent ) && isset( $mapped_terms[ $term->parent ] ) && isset( $mapped_terms[ $term->term_id ] ) )
					wp_update_term( $mapped_terms[ $term->term_id ], $term->taxonomy, array( 'parent' => $mapped_terms[ $term->parent ] ) );

			// Deprecated
			do_action( 'blog_templates-copy-terms', $this->template, get_current_blog_id(), $this->user_id );

			// Update posts term relationships
			if ( $this->args['update_relationships'] ) {

				// Assign link categories
				if ( isset( $taxonomies['link_category'] ) ) {
					// There are one or more link categories
					// Let's assigned them					
					if ( ! empty( $source_links ) )
						$this->assign_terms_to_links( $source_links, $mapped_terms, $mapped_links );
				}


				if ( is_array( $this->args['update_relationships'] ) )
					$args['post_type'] = $this->args['update_relationships'];

				if ( ! empty( $source_posts_ids ) ) {

					// Remove the link categories for posts
					$posts_taxonomies = $taxonomies;
					if ( isset( $posts_taxonomies['link_category'] ) )
						unset( $posts_taxonomies['link_category'] );

					$this->assign_terms_to_objects( $source_posts_ids, $posts_taxonomies, $mapped_terms );

				}

				
				// Deprecated
				do_action( 'blog_templates-copy-term_relationships', $this->template, get_current_blog_id(), $this->user_id );
				
			}

			/**
             * Fires after the terms have been copied.
             *
             * @param Integer $user_id Blog Administrator ID.
             * @param Integer $source_blog_id Source Blog ID from where we are copying the terms.
             * @param Array $template Only applies when using New Blog Templates. Includes the template attributes.
             */
			do_action( 'wpmudev_copier-copy-terms', $this->user_id, $this->source_blog_id, $this->template );

			// If there's a links widget in the sidebar we may need to set the new category ID
	        $widget_links_settings = get_blog_option( $this->source_blog_id, 'widget_links' );

	        if ( is_array( $widget_links_settings ) ) {

		        $new_widget_links_settings = $widget_links_settings;

		        foreach ( $widget_links_settings as $widget_key => $widget_settings ) {
		            if ( ! empty( $widget_settings['category'] ) && isset( $mapped_terms[ $widget_settings['category'] ] ) ) {

		                $new_widget_links_settings[ $widget_key ]['category'] = $mapped_terms[ $widget_settings['category'] ];
		            }
		        }

	        	$updated = update_option( 'widget_links', $new_widget_links_settings );
	        }

	        return true;
		}

		public function assign_terms_to_objects( $source_objects_ids, $taxonomies, $mapped_terms ) {

			$objects_ids = array();
			foreach ( $source_objects_ids as $source_object_id ) {
				if ( isset( $this->args['posts_mapping'][$source_object_id] ) )
					$objects_ids[] = $this->args['posts_mapping'][$source_object_id];
			}

			if ( empty( $objects_ids ) )
				return;

			$source_objects_terms = array();
			switch_to_blog( $this->source_blog_id );
			foreach ( $source_objects_ids as $object_id ) {
				$object_terms = $this->get_object_terms( $object_id, $taxonomies );
				if ( ! empty( $object_terms ) && ! is_wp_error( $object_terms ) )
					$source_objects_terms[ $object_id ] = $object_terms;
			}
			restore_current_blog();
			if ( ! empty( $source_objects_terms ) ) {
				// We just need to set the object terms with the remapped terms IDs
				foreach ( $source_objects_terms as $object_id => $source_object_terms ) {
					if ( ! isset( $this->args['posts_mapping'][ $object_id ] ) )
						continue;

					$new_object_id = $this->args['posts_mapping'][ $object_id ];
					
					$taxonomies = array_unique( wp_list_pluck( $source_object_terms, 'taxonomy' ) );

					foreach ( $taxonomies as $taxonomy ) {
						$source_terms_ids = wp_list_pluck( wp_list_filter( $source_object_terms, array( 'taxonomy' => $taxonomy ) ), 'term_id' );

						$new_terms_ids = array();
						foreach ( $source_terms_ids as $source_term_id ) {
							if ( isset( $mapped_terms[ $source_term_id ] ) )
								$new_terms_ids[] = $mapped_terms[ $source_term_id ];
						}

						// Set post terms
						$this->set_object_terms( $new_object_id, $new_terms_ids, $taxonomy );
					}
					
				}
			}
		}

		public function assign_terms_to_links( $source_links, $mapped_terms, $mapped_links ) {

			foreach ( $source_links as $source_link ) {
				$source_link_id = $source_link->link_id;
				$source_terms_ids = wp_list_pluck( $source_link->terms, 'term_id' );

				if ( ! isset( $mapped_links[ $source_link_id ] ) )
					continue;

				$new_link_id = $mapped_links[ $source_link_id ];

				$new_terms_ids = array();
				foreach ( $source_terms_ids as $source_term_id ) {
					if ( isset( $mapped_terms[ $source_term_id ] ) )
						$new_terms_ids[] = $mapped_terms[ $source_term_id ];
				}

				$this->set_object_terms( $new_link_id, $new_terms_ids, 'link_category' );
			}

		}

		/**
		 * We need this function because WP checks if the taxonomy exist
		 * When switching between blogs, this will not work
		 * 
		 * @param type $object_id 
		 * @param type $terms_ids 
		 * @return array|WP_Error Affected Term IDs
		 */
		private function get_object_terms( $object_ids, $taxonomies ) {
			global $wpdb;

			if ( empty( $object_ids ) || empty( $taxonomies ) )
				return array();

			if ( !is_array($taxonomies) )
				$taxonomies = array($taxonomies);

			if ( !is_array($object_ids) )
				$object_ids = array($object_ids);
			$object_ids = array_map('intval', $object_ids);

			$defaults = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'all');
			$args = wp_parse_args( array(), $defaults );

			$terms = array();
			if ( count($taxonomies) > 1 ) {
				foreach ( $taxonomies as $index => $taxonomy ) {
					$t = get_taxonomy($taxonomy);
					if ( isset($t->args) && is_array($t->args) && $args != array_merge($args, $t->args) ) {
						unset($taxonomies[$index]);
						$terms = array_merge($terms, wp_get_object_terms($object_ids, $taxonomy, array_merge($args, $t->args)));
					}
				}
			} else {
				$t = get_taxonomy( $taxonomies[ key( $taxonomies ) ] );
				if ( isset($t->args) && is_array($t->args) )
					$args = array_merge($args, $t->args);
			}

			extract($args, EXTR_SKIP);

			if ( 'count' == $orderby )
				$orderby = 'tt.count';
			else if ( 'name' == $orderby )
				$orderby = 't.name';
			else if ( 'slug' == $orderby )
				$orderby = 't.slug';
			else if ( 'term_group' == $orderby )
				$orderby = 't.term_group';
			else if ( 'term_order' == $orderby )
				$orderby = 'tr.term_order';
			else if ( 'none' == $orderby ) {
				$orderby = '';
				$order = '';
			} else {
				$orderby = 't.term_id';
			}

			// tt_ids queries can only be none or tr.term_taxonomy_id
			if ( ('tt_ids' == $fields) && !empty($orderby) )
				$orderby = 'tr.term_taxonomy_id';

			if ( !empty($orderby) )
				$orderby = "ORDER BY $orderby";

			$order = strtoupper( $order );
			if ( '' !== $order && ! in_array( $order, array( 'ASC', 'DESC' ) ) )
				$order = 'ASC';

			$taxonomies = "'" . implode("', '", $taxonomies) . "'";
			$object_ids = implode(', ', $object_ids);

			$select_this = '';
			if ( 'all' == $fields )
				$select_this = 't.*, tt.*';
			else if ( 'ids' == $fields )
				$select_this = 't.term_id';
			else if ( 'names' == $fields )
				$select_this = 't.name';
			else if ( 'slugs' == $fields )
				$select_this = 't.slug';
			else if ( 'all_with_object_id' == $fields )
				$select_this = 't.*, tt.*, tr.object_id';

			$query = "SELECT $select_this FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN ($taxonomies) AND tr.object_id IN ($object_ids) $orderby $order";

			if ( ! isset( $taxonomy ) )
				$taxonomy = $taxonomies[0];

			if ( 'all' == $fields || 'all_with_object_id' == $fields ) {
				$_terms = $wpdb->get_results( $query );
				foreach ( $_terms as $key => $term ) {
					$_terms[$key] = sanitize_term( $term, $taxonomy, 'raw' );
				}
				$terms = array_merge( $terms, $_terms );
				update_term_cache( $terms );
			} else if ( 'ids' == $fields || 'names' == $fields || 'slugs' == $fields ) {
				$_terms = $wpdb->get_col( $query );
				$_field = ( 'ids' == $fields ) ? 'term_id' : 'name';
				foreach ( $_terms as $key => $term ) {
					$_terms[$key] = sanitize_term_field( $_field, $term, $term, $taxonomy, 'raw' );
				}
				$terms = array_merge( $terms, $_terms );
			} else if ( 'tt_ids' == $fields ) {
				$terms = $wpdb->get_col("SELECT tr.term_taxonomy_id FROM $wpdb->term_relationships AS tr INNER JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tr.object_id IN ($object_ids) AND tt.taxonomy IN ($taxonomies) $orderby $order");
				foreach ( $terms as $key => $tt_id ) {
					$terms[$key] = sanitize_term_field( 'term_taxonomy_id', $tt_id, 0, $taxonomy, 'raw' ); // 0 should be the term id, however is not needed when using raw context.
				}
			}

			if ( ! $terms )
				$terms = array();

			return apply_filters( 'wp_get_object_terms', $terms, $object_ids, $taxonomies, $args );
		}

		/**
		 * We need this function because WP checks if the taxonomy exist
		 * We are going to force the insertion
		 * 
		 * @param type $object_id 
		 * @param type $terms_ids 
		 * @return array|WP_Error Affected Term IDs
		 */
		private function set_object_terms( $object_id, $terms, $taxonomy ) {
			global $wpdb;

			$object_id = (int) $object_id;

			$old_tt_ids =  wp_list_pluck( $this->get_object_terms( $object_id, $taxonomy, array( 'fields' => 'tt_ids', 'orderby' => 'none' ) ), 'term_id' );

			$tt_ids = array();
			$term_ids = array();
			$new_tt_ids = array();

			foreach ( (array) $terms as $term ) {
				
				if ( ! $term_info = term_exists( $term, $taxonomy ) )
					continue;

				if ( is_wp_error( $term_info ) )
					continue;

				$term_ids[] = $term_info['term_id'];
				$tt_id = $term_info['term_taxonomy_id'];
				$tt_ids[] = $tt_id;

				if ( $wpdb->get_var( $wpdb->prepare( "SELECT term_taxonomy_id FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id = %d", $object_id, $tt_id ) ) )
					continue;


				do_action( 'add_term_relationship', $object_id, $tt_id );
				$wpdb->insert( $wpdb->term_relationships, array( 'object_id' => $object_id, 'term_taxonomy_id' => $tt_id ) );


				do_action( 'added_term_relationship', $object_id, $tt_id );
				$new_tt_ids[] = $tt_id;
			}

			if ( $new_tt_ids )
				wp_update_term_count( $new_tt_ids, $taxonomy );

			$delete_tt_ids = array_diff( $old_tt_ids, $tt_ids );

			if ( $delete_tt_ids ) {
				$in_delete_tt_ids = "'" . implode( "', '", $delete_tt_ids ) . "'";
				$delete_term_ids = $wpdb->get_col( $wpdb->prepare( "SELECT tt.term_id FROM $wpdb->term_taxonomy AS tt WHERE tt.taxonomy = %s AND tt.term_taxonomy_id IN ($in_delete_tt_ids)", $taxonomy ) );
				$delete_term_ids = array_map( 'intval', $delete_term_ids );

				$remove = $this->remove_object_terms( $object_id, $delete_term_ids, $taxonomy );
			}


			wp_cache_delete( $object_id, $taxonomy . '_relationships' );

			do_action( 'set_object_terms', $object_id, $terms, $tt_ids, $taxonomy, false, $old_tt_ids );
			return $tt_ids;
		}


		public function get_terms( $taxonomies = array() ) {
			global $wpdb;

			$empty_array = array();

			$single_taxonomy = ! is_array( $taxonomies ) || 1 === count( $taxonomies );

			$defaults = array('orderby' => 'name', 'order' => 'ASC',
				'hide_empty' => true, 'exclude' => array(), 'exclude_tree' => array(), 'include' => array(),
				'number' => '', 'fields' => 'all', 'slug' => '', 'parent' => '',
				'hierarchical' => true, 'child_of' => 0, 'get' => 'all', 'name__like' => '', 'description__like' => '',
				'pad_counts' => false, 'offset' => '', 'search' => '', 'cache_domain' => 'core' );

			$args = wp_parse_args( array(), $defaults );
			$args['number'] = absint( $args['number'] );
			$args['offset'] = absint( $args['offset'] );
			if ( !$single_taxonomy || ! is_taxonomy_hierarchical( reset( $taxonomies ) ) ||
				( '' !== $args['parent'] && 0 !== $args['parent'] ) ) {
				$args['child_of'] = 0;
				$args['hierarchical'] = false;
				$args['pad_counts'] = false;
			}

			if ( 'all' == $args['get'] ) {
				$args['child_of'] = 0;
				$args['hide_empty'] = 0;
				$args['hierarchical'] = false;
				$args['pad_counts'] = false;
			}

			$args = apply_filters( 'get_terms_args', $args, $taxonomies );

			extract($args, EXTR_SKIP);

			if ( $child_of ) {
				$hierarchy = _get_term_hierarchy( reset( $taxonomies ) );
				if ( ! isset( $hierarchy[ $child_of ] ) )
					return $empty_array;
			}

			if ( $parent ) {
				$hierarchy = _get_term_hierarchy( reset( $taxonomies ) );
				if ( ! isset( $hierarchy[ $parent ] ) )
					return $empty_array;
			}

			// $args can be whatever, only use the args defined in defaults to compute the key
			$filter_key = ( has_filter('list_terms_exclusions') ) ? serialize($GLOBALS['wp_filter']['list_terms_exclusions']) : '';
			$key = md5( serialize( compact(array_keys($defaults)) ) . serialize( $taxonomies ) . $filter_key );
			
			$last_changed = microtime();
			$cache_key = "get_terms:$key:$last_changed";
			$cache = false;

			$_orderby = strtolower($orderby);
			if ( 'count' == $_orderby )
				$orderby = 'tt.count';
			else if ( 'name' == $_orderby )
				$orderby = 't.name';
			else if ( 'slug' == $_orderby )
				$orderby = 't.slug';
			else if ( 'term_group' == $_orderby )
				$orderby = 't.term_group';
			else if ( 'none' == $_orderby )
				$orderby = '';
			elseif ( empty($_orderby) || 'id' == $_orderby )
				$orderby = 't.term_id';
			else
				$orderby = 't.name';

			$orderby = apply_filters( 'get_terms_orderby', $orderby, $args, $taxonomies );

			if ( !empty($orderby) )
				$orderby = "ORDER BY $orderby";
			else
				$order = '';

			$order = strtoupper( $order );
			if ( '' !== $order && !in_array( $order, array( 'ASC', 'DESC' ) ) )
				$order = 'ASC';

			$where = "tt.taxonomy IN ('" . implode("', '", $taxonomies) . "')";
			$inclusions = '';
			if ( ! empty( $include ) ) {
				$exclude = '';
				$exclude_tree = '';
				$inclusions = implode( ',', wp_parse_id_list( $include ) );
			}

			if ( ! empty( $inclusions ) ) {
				$inclusions = ' AND t.term_id IN ( ' . $inclusions . ' )';
				$where .= $inclusions;
			}

			$exclusions = '';
			if ( ! empty( $exclude_tree ) ) {
				$exclude_tree = wp_parse_id_list( $exclude_tree );
				$excluded_children = $exclude_tree;
				foreach ( $exclude_tree as $extrunk ) {
					$excluded_children = array_merge(
						$excluded_children,
						(array) get_terms( $taxonomies[0], array( 'child_of' => intval( $extrunk ), 'fields' => 'ids', 'hide_empty' => 0 ) )
					);
				}
				$exclusions = implode( ',', array_map( 'intval', $excluded_children ) );
			}

			if ( ! empty( $exclude ) ) {
				$exterms = wp_parse_id_list( $exclude );
				if ( empty( $exclusions ) )
					$exclusions = implode( ',', $exterms );
				else
					$exclusions .= ', ' . implode( ',', $exterms );
			}

			if ( ! empty( $exclusions ) )
				$exclusions = ' AND t.term_id NOT IN (' . $exclusions . ')';

			$exclusions = apply_filters( 'list_terms_exclusions', $exclusions, $args, $taxonomies );

			if ( ! empty( $exclusions ) )
				$where .= $exclusions;

			if ( !empty($slug) ) {
				$slug = sanitize_title($slug);
				$where .= " AND t.slug = '$slug'";
			}

			if ( !empty($name__like) ) {
				$name__like = like_escape( $name__like );
				$where .= $wpdb->prepare( " AND t.name LIKE %s", '%' . $name__like . '%' );
			}

			if ( ! empty( $description__like ) ) {
				$description__like = like_escape( $description__like );
				$where .= $wpdb->prepare( " AND tt.description LIKE %s", '%' . $description__like . '%' );
			}

			if ( '' !== $parent ) {
				$parent = (int) $parent;
				$where .= " AND tt.parent = '$parent'";
			}

			if ( 'count' == $fields )
				$hierarchical = false;

			if ( $hide_empty && !$hierarchical )
				$where .= ' AND tt.count > 0';

			// don't limit the query results when we have to descend the family tree
			if ( $number && ! $hierarchical && ! $child_of && '' === $parent ) {
				if ( $offset )
					$limits = 'LIMIT ' . $offset . ',' . $number;
				else
					$limits = 'LIMIT ' . $number;
			} else {
				$limits = '';
			}

			if ( ! empty( $search ) ) {
				$search = like_escape( $search );
				$where .= $wpdb->prepare( ' AND ((t.name LIKE %s) OR (t.slug LIKE %s))', '%' . $search . '%', '%' . $search . '%' );
			}

			$selects = array();
			switch ( $fields ) {
				case 'all':
					$selects = array( 't.*', 'tt.*' );
					break;
				case 'ids':
				case 'id=>parent':
					$selects = array( 't.term_id', 'tt.parent', 'tt.count' );
					break;
				case 'names':
					$selects = array( 't.term_id', 'tt.parent', 'tt.count', 't.name' );
					break;
				case 'count':
					$orderby = '';
					$order = '';
					$selects = array( 'COUNT(*)' );
					break;
				case 'id=>name':
					$selects = array( 't.term_id', 't.name' );
					break;
				case 'id=>slug':
					$selects = array( 't.term_id', 't.slug' );
					break;
			}

			$_fields = $fields;

			$fields = implode( ', ', apply_filters( 'get_terms_fields', $selects, $args, $taxonomies ) );

			$join = "INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id";

			$pieces = array( 'fields', 'join', 'where', 'orderby', 'order', 'limits' );

			$clauses = apply_filters( 'terms_clauses', compact( $pieces ), $taxonomies, $args );
			foreach ( $pieces as $piece )
				$$piece = isset( $clauses[ $piece ] ) ? $clauses[ $piece ] : '';

			$query = "SELECT $fields FROM $wpdb->terms AS t $join WHERE $where $orderby $order $limits";

			$fields = $_fields;

			if ( 'count' == $fields ) {
				$term_count = $wpdb->get_var($query);
				return $term_count;
			}

			$terms = $wpdb->get_results($query);
			if ( 'all' == $fields ) {
				update_term_cache($terms);
			}

			if ( empty($terms) ) {

				/** This filter is documented in wp-includes/taxonomy.php */
				$terms = apply_filters( 'get_terms', array(), $taxonomies, $args );
				return $terms;
			}

			if ( $child_of ) {
				$children = _get_term_hierarchy( reset( $taxonomies ) );
				if ( ! empty( $children ) )
					$terms = _get_term_children( $child_of, $terms, reset( $taxonomies ) );
			}

			// Update term counts to include children.
			if ( $pad_counts && 'all' == $fields )
				_pad_term_counts( $terms, reset( $taxonomies ) );

			// Make sure we show empty categories that have children.
			if ( $hierarchical && $hide_empty && is_array( $terms ) ) {
				foreach ( $terms as $k => $term ) {
					if ( ! $term->count ) {
						$children = get_term_children( $term->term_id, reset( $taxonomies ) );
						if ( is_array( $children ) ) {
							foreach ( $children as $child_id ) {
								$child = get_term( $child_id, reset( $taxonomies ) );
								if ( $child->count ) {
									continue 2;
								}
							}
						}

						// It really is empty
						unset($terms[$k]);
					}
				}
			}
			reset( $terms );

			$_terms = array();
			if ( 'id=>parent' == $fields ) {
				while ( $term = array_shift( $terms ) )
					$_terms[$term->term_id] = $term->parent;
			} elseif ( 'ids' == $fields ) {
				while ( $term = array_shift( $terms ) )
					$_terms[] = $term->term_id;
			} elseif ( 'names' == $fields ) {
				while ( $term = array_shift( $terms ) )
					$_terms[] = $term->name;
			} elseif ( 'id=>name' == $fields ) {
				while ( $term = array_shift( $terms ) )
					$_terms[$term->term_id] = $term->name;
			} elseif ( 'id=>slug' == $fields ) {
				while ( $term = array_shift( $terms ) )
					$_terms[$term->term_id] = $term->slug;
			}

			if ( ! empty( $_terms ) )
				$terms = $_terms;

			if ( $number && is_array( $terms ) && count( $terms ) > $number )
				$terms = array_slice( $terms, $offset, $number );

			/** This filter is documented in wp-includes/taxonomy */
			$terms = apply_filters( 'get_terms', $terms, $taxonomies, $args );
			return $terms;
		}


		function remove_object_terms( $object_id, $terms, $taxonomy ) {
			global $wpdb;

			$object_id = (int) $object_id;

			if ( ! is_array( $terms ) ) {
				$terms = array( $terms );
			}

			$tt_ids = array();

			foreach ( (array) $terms as $term ) {
				if ( ! strlen( trim( $term ) ) ) {
					continue;
				}

				if ( ! $term_info = term_exists( $term, $taxonomy ) ) {
					// Skip if a non-existent term ID is passed.
					if ( is_int( $term ) ) {
						continue;
					}
				}

				if ( is_wp_error( $term_info ) ) {
					return $term_info;
				}

				$tt_ids[] = $term_info['term_taxonomy_id'];
			}

			if ( $tt_ids ) {
				$in_tt_ids = "'" . implode( "', '", $tt_ids ) . "'";

				/**
				 * Fires immediately before an object-term relationship is deleted.
				 *
				 * @since 2.9.0
				 *
				 * @param int   $object_id Object ID.
				 * @param array $tt_ids    An array of term taxonomy IDs.
				 */
				do_action( 'delete_term_relationships', $object_id, $tt_ids );
				$deleted = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)", $object_id ) );

				/**
				 * Fires immediately after an object-term relationship is deleted.
				 *
				 * @since 2.9.0
				 *
				 * @param int   $object_id Object ID.
				 * @param array $tt_ids    An array of term taxonomy IDs.
				 */
				do_action( 'deleted_term_relationships', $object_id, $tt_ids );
				wp_update_term_count( $tt_ids, $taxonomy );

				return (bool) $deleted;
			}

			return false;
		}

	}


}