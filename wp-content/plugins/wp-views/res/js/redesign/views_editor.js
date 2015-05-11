

var WPViews = WPViews || {};

WPViews.ViewEditScreen = function( $ ) {
	var self = this;
	self.view_id = $('.js-post_ID').val();
	self.show_hide_sections =  $( '.js-wpv-show-hide-container' ).find('.js-wpv-show-hide-value').serialize();
	self.show_hide_metasections_help =  $( '.js-wpv-show-hide-container' ).find('.js-wpv-show-hide-help-value').serialize();
	self.purpose = $('.js-view-purpose').val();
	self.query_type = $('.js-wpv-query-type:checked').val();
	self.pag_mode = $( '.js-wpv-pagination-mode:checked' ).val();
	
	// ---------------------------------
	// Screen options and View purpose
	// ---------------------------------
	
	// Screen options - position fix
	
	self.screen_options_fix = function() {
		var views_screen_options = $('.js-screen-meta-links-dup > div'),
		views_screen_options_container = $('.js-screen-meta-dup > div');
		$('#screen-meta-links').append(views_screen_options);
		$('#screen-meta').append(views_screen_options_container);
	};
	
	// Screen options - display help for purpose
	
	self.display_view_howto_help_box = function( purpose ) {
		$( '.js-display-view-howto' ).hide();
		$( '.js-display-view-howto.js-for-view-purpose-' + purpose ).show();
	};
	
	// Screen options - show/hide metasections
	
	self.show_hide_metasections_init = function() {
		$( '.js-wpv-show-hide-section' ).each(function(){
			var metasection = $( this ).data( 'metasection' );
			if (
				0 == $( this ).find( '.js-wpv-show-hide:checked' ).length &&
				$( '.' + metasection ).find( '.wpv-setting-container:not(.js-wpv-settings-container-dps-filter)' ).length == $( this ).find( '.js-wpv-show-hide' ).length
			) {
				$( '.' + metasection ).hide();
			}
		});
	};
	
	// Screen options - help boxes for purposes
	
	self.show_hide_purpose_help_init = function() {
		$( '.js-wpv-show-hide-help' ).each( function() {
			var metasection = $( this ).data( 'metasection' ),
			state = $( this ).attr( 'checked' );
			if ('checked' == state) {
				$( '.js-metasection-help-' + metasection + '.js-for-view-purpose-' + self.purpose ).show();
			} else {
				$( '.js-metasection-help-' + metasection + '.js-for-view-purpose-' + self.purpose ).hide();
			}
		});
	};
	
	// Screen options - update automatically
	
	self.save_view_screen_options = function() {
		var container = $( '.js-wpv-show-hide-container' ),
		wpv_show_hide_sections = container.find('.js-wpv-show-hide-value').serialize(),
		wpv_show_hide_metasections_help = container.find('.js-wpv-show-hide-help-value').serialize(),
		purpose = container.find('.js-view-purpose').val();
		container.find('.toolset-alert').remove();
		
		if ( self.show_hide_sections == wpv_show_hide_sections
			&& self.show_hide_metasections_help == wpv_show_hide_metasections_help
			&& self.purpose == purpose
		) {
			
		} else {
			var manager = container.find( '.js-wpv-show-hide-update' ),
			nonce = manager.data( 'nonce' ),
			data_view_id = self.view_id,
			data = {
				action: 'wpv_save_screen_options',
				id: data_view_id,
				settings: wpv_show_hide_sections,
				helpboxes: wpv_show_hide_metasections_help,
				purpose: purpose,
				wpnonce: nonce
			};
			$.post( ajaxurl, data, function( response ) {
				if ( ( typeof( response ) !== 'undefined') ) {
					if ( 0 != response ) {
						
					} else {
						console.log( "Error: WordPress AJAX returned ", response );
					}
				} else {
					console.log( "Error: AJAX returned ", response );
				}
			})
			.fail( function( jqXHR, textStatus, errorThrown ) {
				console.log( "Error: ", textStatus, errorThrown );
			})
			.always( function() {
				self.show_hide_sections = wpv_show_hide_sections;
				self.show_hide_metasections_help = wpv_show_hide_metasections_help;
				self.purpose = purpose;
			});
		}
		
	};
	
	self.screen_options_debounce_update = _.debounce( self.save_view_screen_options, 1000 );
	
	// Screen options - events
	
	$( document ).on( 'change', '.js-view-purpose', function() {
		self.purpose = $( this ).val();
		self.display_view_howto_help_box( self.purpose );
	});
	
	$( document ).on( 'click', '#screen-meta-links #contextual-help-link', function() {
		// Fix when opening Help section
		// This is caused because we are adding our Screen Options in an artificial way
		// so when opening the Help tab it displays all elements inside the tab container
		$( '.metabox-prefs .js-wpv-show-hide-container' ).hide();
	});
	
	$( document ).on( 'change', '.js-wpv-show-hide-container .js-wpv-show-hide, .js-wpv-show-hide-container .js-wpv-show-hide-help, .js-wpv-show-hide-container .js-view-purpose', function() {
		self.screen_options_debounce_update();
	});
	
	// ---------------------------------
	// Title and description
	// ---------------------------------
	
	// Title placeholder
	
	self.title_placeholder = function() {
		$( '.js-title' ).each( function() {
			var thiz = $( this );
			if ( '' === thiz.val() ) {
				thiz
					.parent()
					.find( '.js-title-reader' )
					.removeClass( 'screen-reader-text' );
			}
			thiz.focus( function() {
				thiz
					.parent()
					.find( '.js-title-reader' )
					.addClass( 'screen-reader-text' );
			});
			thiz.blur( function() {
				if ( '' === thiz.val() ) {
					thiz
						.parent()
						.find( '.js-title-reader' )
						.removeClass( 'screen-reader-text' );
				}
			});
		});
	};
	
	// Description events
	
	$( '.js-wpv-description-toggle' ).on( 'click', function() {
		$( this ).hide();
		$( '.js-wpv-description-container' ).fadeIn( 'fast' );
		$( '#wpv-description' ).focus();
	});
	
	// ---------------------------------
	// Content selection
	// ---------------------------------
	
	// Content selection - mandatory selection
	
	self.content_selection_mandatory = function() {
		var overlay_container = $("<div class='wpv-setting-overlay js-wpv-setting-overlay'><div class='wpv-transparency'></div><i class='icon-lock'></i></div>");
		if (
			( $('.js-wpv-query-post-type:checked').length == 0 && self.query_type === 'posts' )
			|| ( $('.js-wpv-query-taxonomy-type:checked').length == 0 && self.query_type === 'taxonomy' )
			|| ( $('.js-wpv-query-users-type:checked').length == 0 && self.query_type === 'users' )
		) {	
			// Show the warning message
			$( '.js-wpv-content-selection-mandatory-warning' ).show();
			// Disable further Views editing
			$( '.wpv-setting-container:not(.js-wpv-no-lock)' ).prepend( overlay_container );
			// Add glow to inputs
			$( '.js-wpv-query-post-type, .js-wpv-query-taxonomy-type, .js-wpv-query-users-type' ).css( {'box-shadow': '0 0 5px 1px #f6921e'} );
		} else {
			// Hide the warning message
			$( '.js-wpv-content-selection-mandatory-warning' ).hide();
			// Enable further Views editing
			$( '.js-wpv-setting-overlay' ).fadeOut( 500, function() {
				$( '.js-wpv-setting-overlay' ).remove();
			});
			// Remove glow from inputs
			$( '.js-wpv-query-post-type, .js-wpv-query-taxonomy-type, .js-wpv-query-users-type' ).css( {'box-shadow': 'none'} );
		}
	};
	
	// Content selection - change sections based on query type
	
	self.query_type_sections = function() {
		if ('posts' == self.query_type) {
			$( '.wpv-settings-query-type-taxonomy, .wpv-settings-query-type-users' ).hide();
			$( '.wpv-settings-query-type-posts' ).fadeIn( 'fast' );
			$( '.wpv-vicon-for-posts').removeClass( 'hidden' );
			$( '.wpv-vicon-for-taxonomy, .wpv-vicon-for-users' ).addClass( 'hidden' );
		} else if ('taxonomy' == self.query_type) {
			$( '.wpv-settings-query-type-posts, .wpv-settings-query-type-users' ).hide();
			$( '.wpv-settings-query-type-taxonomy' ).fadeIn( 'fast' );
			$( '.wpv-vicon-for-taxonomy' ).removeClass( 'hidden' );
			$( '.wpv-vicon-for-posts, .wpv-vicon-for-users' ).addClass( 'hidden' );
		} else if ('users' == self.query_type) {
			$( '.wpv-settings-query-type-posts, .wpv-settings-query-type-taxonomy' ).hide();
			$( '.wpv-settings-query-type-users' ).fadeIn( 'fast' );
			$( '.wpv-vicon-for-users' ).removeClass( 'hidden' );
			$( '.wpv-vicon-for-posts, .wpv-vicon-for-taxonomy' ).addClass( 'hidden' );
			
		}
	};
	
	// Content selection - update automatically
	
	self.save_view_query_type_options = function() {
		var dataholder = $( '.js-wpv-query-type-update' ),
		update_message = dataholder.data('success'),
		unsaved_message = dataholder.data('unsaved'),
		nonce = dataholder.data('nonce'),
		wpv_query_post_items = [],
		wpv_query_taxonomy_items = [],
		wpv_query_users_items = [],
		spinnerContainer = $('<div class="spinner ajax-loader auto-update">').insertBefore( $( '.js-wpv-settings-content-selection .js-wpv-setting' ) ).show(),
		query_type = $('input:radio.js-wpv-query-type:checked').val();
		$('.js-wpv-query-post-type:checked').each( function() {
			wpv_query_post_items.push( $(this).val() );
		});
		$('.js-wpv-query-taxonomy-type:checked').each( function() {
			wpv_query_taxonomy_items.push( $(this).val() );
		});
		$('.js-wpv-query-users-type:checked').each( function() {
			wpv_query_users_items.push( $(this).val() );
		});
		
		dataholder.parent().find('.toolset-alert-error').remove();
		var data = {
			action: 'wpv_update_query_type',
			id: self.view_id,
			query_type: query_type,
			post_types: wpv_query_post_items,
			taxonomies: wpv_query_taxonomy_items,
			users: wpv_query_users_items,
			wpnonce: nonce
		};
		$.ajax({
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function(response){
				if ( (typeof(response) !== 'undefined') ) {
					decoded_response = $.parseJSON(response);
					if ( decoded_response.success === data.id ) {
						$('.js-screen-options').find('.toolset-alert').remove();
						if ( decoded_response.wpv_update_flatten_types_relationship_tree == 'NONE' ) {
							$('.js-flatten-types-relation-tree').val('NONE');
						} else {
							$('.js-flatten-types-relation-tree').val(decoded_response.wpv_update_flatten_types_relationship_tree);
						}
						$('.js-wpv-content-section-action-wrap').wpvToolsetMessage({
							text:update_message,
							type:'success',
							inline:true,
							stay:false
						});
					}
				} else {
					$('.js-wpv-content-section-action-wrap').wpvToolsetMessage({
						text:unsaved_message,
						type:'error',
						inline:true,
						stay:true
					});
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				$('.js-wpv-content-section-action-wrap').wpvToolsetMessage({
					text:unsaved_message,
					type:'error',
					inline:true,
					stay:true
				});
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
				dataholder.trigger( 'js_event_wpv_query_type_options_saved', [ query_type ] );
			}
		});
	};
	
	self.content_selection_debounce_update = _.debounce( self.save_view_query_type_options, 1000 );
	
	// Content selection - events
	
	$( document ).on( 'change', '.js-wpv-query-type', function() {
		self.query_type = $('.js-wpv-query-type:checked').val();
		self.query_type_sections();
		self.content_selection_mandatory();
		self.content_selection_debounce_update();
	});
	
	$( document ).on('change', '.js-wpv-query-post-type, .js-wpv-query-taxonomy-type, .js-wpv-query-users-type', function(){
		self.content_selection_mandatory();
		self.content_selection_debounce_update();
	});
	
	// ---------------------------------
	// Query options
	// ---------------------------------
	
	// Query options - update automatically
	
	self.save_view_query_options = function() {
		var dataholder = $( '.js-wpv-query-options-update' ),
		update_message = dataholder.data('success'),
		unsaved_message = dataholder.data('unsaved'),
		nonce = dataholder.data('nonce'),
		spinnerContainer = $('<div class="spinner ajax-loader auto-update">').insertBefore( $( '.js-wpv-settings-query-options .js-wpv-settings' ) ).show(),
		view_id = self.view_id;
		dataholder.parent().find('.toolset-alert-error').remove();
		var data = {
			action: 'wpv_update_query_options',
			id: view_id,
			dont: $('.js-wpv-query-options-post-type-dont:checked').length,
			hide: $('.js-wpv-query-options-taxonomy-hide-empty:checked').length,
			empty: $('.js-wpv-query-options-taxonomy-non-empty-decendants:checked').length,
			pad: $('.js-wpv-query-options-taxonomy-pad-counts:checked').length,
			uhide : $('.js-wpv-query-options-users-show-current:checked').length,
			wpnonce: nonce
		};
		$.ajax({
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function( response ) {
				if ( ( typeof( response ) !== 'undefined' ) && ( response === data.id ) ) {
					$('.js-screen-options').find('.toolset-alert').remove();// TODO Review this
					$('.js-wpv-query-options-update').parent().wpvToolsetMessage({
						text:update_message,
						type:'success',
						inline:true,
						stay:false
					});
				} else {
					$('.js-wpv-query-options-update').parent().wpvToolsetMessage({
						text:unsaved_message,
						type:'error',
						inline:true,
						stay:true
					});
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				$('.js-wpv-query-options-update').parent().wpvToolsetMessage({
					text:unsaved_message,
					type:'error',
					inline:true,
					stay:true
				});
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
			}
		});
	};
	
	self.query_options_debounce_update = _.debounce( self.save_view_query_options, 2000 );
	
	// Query options - events
	
	$( document ).on( 'change', '.js-wpv-query-options-users-show-current, .js-wpv-query-options-post-type-dont, .js-wpv-query-options-taxonomy-hide-empty, .js-wpv-query-options-taxonomy-non-empty-decendants, .js-wpv-query-options-taxonomy-pad-counts', function() {
		self.query_options_debounce_update();
	});
	
	// ---------------------------------
	// Sorting
	// ---------------------------------
	
	// Sorting - update automatically
	
	self.save_view_sorting_options = function() {
		var dataholder = $( '.js-wpv-ordering-update' ),
		update_message = dataholder.data( 'success' ),
		unsaved_message = dataholder.data( 'unsaved' ),
		nonce = dataholder.data( 'nonce' ),
		spinnerContainer = $( '<div class="spinner ajax-loader auto-update">' ).insertBefore( $( '.js-wpv-settings-ordering .js-wpv-setting' ) ).show(),
		view_id = self.view_id;
		dataholder.parent().find('.toolset-alert-error').remove();
		var data = {
			action: 'wpv_update_sorting',
			id: view_id,
			orderby: $('.js-wpv-posts-orderby').val(),
			order: $('.js-wpv-posts-order').val(),
			taxonomy_orderby: $('.js-wpv-taxonomy-orderby').val(),
			taxonomy_order: $('.js-wpv-taxonomy-order').val(),
			users_orderby: $('.js-wpv-users-orderby').val(),
			users_order: $('.js-wpv-users-order').val(),
			wpnonce: nonce
		};
		$.ajax({
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function(response){
				if ( (typeof(response) !== 'undefined') && (response === data.id)) {
					$('.js-screen-options').find('.toolset-alert').remove();
					dataholder.parent().wpvToolsetMessage({
						text:update_message,
						type:'success',
						inline:true,
						stay:false
					});
				} else {
					dataholder.parent().wpvToolsetMessage({
						text:unsaved_message,
						type:'error',
						inline:true,
						stay:true
					});
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				dataholder.parent().wpvToolsetMessage({
					text:unsaved_message,
					type:'error',
					inline:true,
					stay:true
				});
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
			}
		});
	};
	
	self.sorting_debounce_update = _.debounce( self.save_view_sorting_options, 2000 );
	
	// Sorting - rand and pagination do not work well together
	
	self.sorting_random_and_pagination = function() {// TODO revisit this, I need to add the same message in both sides, and when both sections change
		$('.js-wpv-settings-posts-order .toolset-alert, .js-wpv-settings-pagination .js-pagination-settings-form .toolset-alert').remove();
		if ( $( '.js-wpv-posts-orderby' ).val() == 'rand' && $( '.js-wpv-pagination-mode:checked' ).val() != 'none' ) {
			$('.js-wpv-settings-posts-order, .js-wpv-settings-pagination .js-pagination-settings-form' ).wpvToolsetMessage({
				text: $( '.js-wpv-posts-orderby' ).data( 'rand' ),
				stay: true,
				close: false,
				type: ''
			});
		}
	};
	
	// Sorting - events
	
	$( document ).on( 'change', '.js-wpv-posts-orderby, .js-wpv-posts-order, .js-wpv-taxonomy-orderby, .js-wpv-taxonomy-order, .js-wpv-users-orderby, .js-wpv-users-order', function() {
		self.sorting_random_and_pagination();
		self.sorting_debounce_update();
	});
	
	// ---------------------------------
	// Limit and offset
	// ---------------------------------
	
	// Limit and offset - update automatically
	
	self.save_view_limit_offset_options = function() {
		var dataholder = $( '.js-wpv-limit-offset-update' ),
		update_message = dataholder.data('success'),
		unsaved_message = dataholder.data('unsaved'),
		nonce = dataholder.data('nonce'),
		spinnerContainer = $('<div class="spinner ajax-loader auto-update">').insertBefore( $( '.js-wpv-settings-limit-offset .js-wpv-setting' ) ).show(),
		view_id = self.view_id;
		dataholder.parent().find('.toolset-alert-error').remove();
		var data = {
			action: 'wpv_update_limit_offset',
			id: view_id,
			limit: $( '.js-wpv-limit' ).val(),
			offset: $( '.js-wpv-offset' ).val(),
			taxonomy_limit: $( '.js-wpv-taxonomy-limit' ).val(),
			taxonomy_offset: $( '.js-wpv-taxonomy-offset' ).val(),
			users_limit: $( '.js-wpv-users-limit' ).val(),
			users_offset: $( '.js-wpv-users-offset' ).val(),
			wpnonce: nonce
		};
		$.ajax({
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function( response ) {
				if ( ( typeof( response ) !== 'undefined') && ( response === data.id ) ) {
					$( '.js-screen-options' ).find( '.toolset-alert' ).remove();
					dataholder.parent().wpvToolsetMessage({
						text:update_message,
						type:'success',
						inline:true,
						stay:false
					});
				} else {
					dataholder.parent().wpvToolsetMessage({
						text:unsaved_message,
						type:'error',
						inline:true,
						stay:true
					});
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				dataholder.parent().wpvToolsetMessage({
					text:unsaved_message,
					type:'error',
					inline:true,
					stay:true
				});
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
			}
		});
	};
	
	self.limit_offset_debounce_update = _.debounce( self.save_view_limit_offset_options, 2000 );
	
	// Limit and offset - events
	
	$( document ).on( 'change', '.js-wpv-limit, .js-wpv-offset, .js-wpv-taxonomy-limit, .js-wpv-taxonomy-offset, .js-wpv-users-limit, .js-wpv-users-offset', function() {
		self.limit_offset_debounce_update();
	});
	
	// ---------------------------------
	// Pagination
	// ---------------------------------
	
	// Pagination - init and change pagination mode
	
	self.pagination_mode = function() {
		$( '.wpv-pagination-paged, .wpv-pagination-rollover, .wpv-pagination-advanced' ).hide();
		if ('paged' == self.pag_mode ) {
			$('.wpv-pagination-rollover, .wpv-pagination-shared, .wpv-pagination-paged-ajax, .wpv-pagination-advanced').hide();
			$('.wpv-pagination-paged, .wpv-pagination-options-box').fadeIn('fast');
			$('.js-pagination-zero').val('enable');
			self.pagination_ajax();
		} else if ('rollover' == self.pag_mode ) {
			$('.wpv-pagination-paged').hide();
			$('.wpv-pagination-rollover').fadeIn('fast');
			$('.wpv-pagination-paged-ajax, .wpv-pagination-advanced').hide();
			$('.wpv-pagination-options-box').fadeIn('fast');
			$('.js-pagination-zero').val('enable');
		} else {
			$('.wpv-pagination-options-box, .wpv-pagination-paged, .wpv-pagination-rollover, .wpv-pagination-shared').hide();
			$('.js-pagination-zero').val('disable');
		}
	};
	
	// Pagination - init and change pagination AJAX settings (show/hide further AJAX settings based on AJAX mode)
	
	self.pagination_ajax = function() {
		$( '.wpv-pagination-advanced' ).hide();
		var paged_mode = $('.js-wpv-ajax_pagination:checked').val();
		if ( 'disable' == paged_mode || undefined === paged_mode ) {
			$( '.wpv-pagination-shared, .wpv-pagination-paged-ajax, .wpv-pagination-advanced, [data-section="ajax_pagination"]' ).hide();
		} else {
			var pag_mode = $( 'input[name="pagination\\[mode\\]"]:checked' ).val();
			if ( 'rollover' != pag_mode ) {
				$('.wpv-pagination-paged-ajax:not(.wpv-pagination-advanced)' ).fadeIn( 'fast' );
			}
			$( '.wpv-pagination-shared, .wpv-pagination-advanced' ).hide();
			$( '[data-section="ajax_pagination"]' ).show();
		}
	};
	
	// Pagination - init and change pagination spinners (show/hide further spinner settings based on spinner mode)
	
	self.pagination_spinners = function() {
		var pagination_spinner_setting = $( '.js-wpv-pagination-spinner:checked' ).val();
		$( '.js-wpv-pagination-spinner-default, .js-wpv-pagination-spinner-uploaded' ).hide();
		if ( pagination_spinner_setting == 'default' || pagination_spinner_setting == 'uploaded' ) {
			$( '.js-wpv-pagination-spinner-' + pagination_spinner_setting ).fadeIn();
		}
	};
	
	// Pagination - update automatically
	
	self.save_view_pagination_options = function() {
		var dataholder = $( '.js-wpv-pagination-update' ),
		update_message = dataholder.data( 'success' ),
		unsaved_message = dataholder.data( 'unsaved' ),
		nonce = dataholder.data('nonce'),
		spinnerContainer = $('<div class="spinner ajax-loader auto-update">').insertBefore( $( '.js-wpv-settings-pagination .js-wpv-setting' ) ).show(),
		view_id = self.view_id,
		settings = $('.js-pagination-settings-form').serialize(),
		show_hint = dataholder.data('showhint');
		dataholder.parent().find('.toolset-alert-error').remove();
		var data = {
			action: 'wpv_update_pagination',
			id: view_id,
			settings : settings,
			wpnonce: nonce
		};
		$.ajax({
			async:false,
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function(response){
				if ( ( typeof( response ) !== 'undefined' ) && ( response === data.id ) ) {
					jQuery( '.js-screen-options' ).find( '.toolset-alert' ).remove();
					dataholder.parent().wpvToolsetMessage({
						text:update_message,
						type:'success',
						inline:true,
						stay:false
					});
				} else {
					dataholder.parent().wpvToolsetMessage({
						text:unsaved_message,
						type:'error',
						inline:true,
						stay:true
					});
					console.log( "Error: AJAX returned ", response );
				}
			},
			error: function (ajaxContext) {
				dataholder.parent().wpvToolsetMessage({
					text:unsaved_message,
					type:'error',
					inline:true,
					stay:true
				});
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete: function() {
				spinnerContainer.remove();
			}
		});
	};
	
	self.pagination_debounce_update = _.debounce( self.save_view_pagination_options, 2000 );
	
	// Pagination - events
	
	$( document ).on( 'change', '.js-wpv-pagination-mode', function() {
		self.pag_mode = $( '.js-wpv-pagination-mode:checked' ).val();
		$( '.js-pagination-advanced' ).each( function() {
			$( this ).data('state','closed').text( $(this).data( 'closed' ) );
		});
		self.pagination_mode();
	});
	
	$( document ).on( 'change', '.js-wpv-ajax_pagination', function() {
		$( '.js-pagination-advanced' ).each( function() {
			$( this ).data( 'state','closed' ).text( $( this ).data( 'closed' ) );
		});
		self.pagination_ajax();
	});
	
	$( document ).on( 'click', '.js-pagination-advanced', function() {
		var state = $(this).data('state'),
		text = '';
		if ( state == 'closed' ) {
			$( this ).data( 'state','opened' ).text( $( this ).data( 'opened' ) );
			$( '.wpv-pagination-advanced' ).fadeIn( 'fast' );
		} else if ( state == 'opened' ) {
			$( this ).data( 'state','closed' ).text( $( this ).data( 'closed' ) );
			$( '.wpv-pagination-advanced' ).hide();
		}
	});
	
	$( document ).on( 'change', '.js-wpv-pagination-spinner', function() {
		self.pagination_spinners();
	});
	
	$( document ).on( 'change keyup input cut paste', '.js-pagination-settings-form input, .js-pagination-settings-form select', function() {
		self.pagination_debounce_update();
	});
	
	// ---------------------------------
	// Parametric search
	// ---------------------------------
	
	// Parametric search - update automatically
	
	self.save_view_parametric_search_options = function() {
		var dataholder = $( '.js-wpv-filter-dps-update' ),
		nonce = dataholder.data('nonce'),
		view_id = self.view_id,
		spinnerContainer = $('<div class="spinner ajax-loader auto-update">').insertBefore( $( '.js-wpv-settings-container-dps-filter .js-wpv-dps-settings' ) ).show(),
		update_message = dataholder.data('success'),
		unsaved_message = dataholder.data('unsaved'),
		dps_data = $('.js-wpv-dps-settings input, .js-wpv-dps-settings select').serialize();
		dataholder.parent().find('.toolset-alert-error').remove();
		var params = {
			action: 'wpv_filter_update_dps_settings',
			id: view_id,
			dpsdata: dps_data,
			nonce: nonce
		}
		$.ajax({
			type:"POST",
			url:ajaxurl,
			data:params,
			success:function(response){
				if ( (typeof(response) !== 'undefined') && (response === params.id)) {
					dataholder.parent().wpvToolsetMessage({
						text:update_message,
						type:'success',
						inline:true,
						stay:false
					});
				} else {
					dataholder.parent().wpvToolsetMessage({
						text:unsaved_message,
						type:'error',
						inline:true,
						stay:true
					});
					console.log( "Error: AJAX returned ", response );
				}
			},
			error:function(ajaxContext){
				dataholder.parent().wpvToolsetMessage({
					 text:unsaved_message,
					 type:'error',
					 inline:true,
					 stay:true
				});
				console.log( "Error: ", ajaxContext.responseText );
			},
			complete:function(){
				spinnerContainer.remove();
			}
		});
	};
	
	self.parametric_search_debounce_update = _.debounce( self.save_view_parametric_search_options, 1000 );
	
	// Parametric search - events
	
	$( document ).on( 'change keypress keyup input cut paste', '.js-wpv-dps-settings input', function() {
		self.parametric_search_debounce_update();
	});
	
	// ---------------------------------
	// Init
	// ---------------------------------
	
	self.init = function(){ // public method
		// Screen options fix - move to the right place in DOM
		self.screen_options_fix();
		// Show or hide metasections in page load, based on screen options
		self.show_hide_metasections_init();
		// Show or hide section help boxes based on purpose
		self.show_hide_purpose_help_init();
		// Show or hide display help box based on purpose
		self.display_view_howto_help_box( self.purpose );
		// Title placeholder
		self.title_placeholder();
		// Content selector section is mandatory
		self.content_selection_mandatory();
		// Random order and pagination incompatible
		self.sorting_random_and_pagination();
		// Init pagination mode
		self.pagination_mode();
		// Init pagination ajax
		self.pagination_ajax();
		// Init pagination spinners
		self.pagination_spinners();
	};
	
	self.init(); // call the init method

};

jQuery( document ).ready( function( $ ) {
    WPViews.view_edit_screen = new WPViews.ViewEditScreen( $ );
});



jQuery(document).ready(function($){

	//editor buttons
	$( '.js-code-editor-button' ).click( function( e ) {
		e.preventDefault();

		var $this = $( this ),
		state = $this.data( 'state' ),
		label = $this.find( '.js-wpv-textarea-button-label'),
		flag = $this.find( '.js-wpv-textarea-full' );
		
		var $editor = $('.js-code-editor').filter(function() {
			return $this.data('target') ===  $(this).data('name');
		});
		
		$editor.toggleClass('closed');

		if ( $this.data('target') == 'filter-css-editor' || $this.data('target') == 'filter-js-editor'
			|| $this.data('target') == 'layout-js-editor' || $this.data('target') == 'layout-css-editor' ) {
			var z_el = $this.data('target');
			var $elem = $this.detach();
			if (state == 'closed') {
				flag.hide();
				$editor.find('.js-code-editor-toolbar ul').append('<li class="wpv-'+ z_el +'-button-moved close-editor"></li>');
				$('.wpv-'+ z_el +'-button-moved').append($elem);
			}
			else{
				$('.js-wpv-'+ z_el +'-old-place').append($elem);
				$editor.find('.js-code-editor-toolbar ul li.wpv-'+ z_el +'-button-moved').remove();
				if ( wpv_extra_textarea_toggle_flag(z_el) ) {
					flag.show();
				} else {
					flag.hide();
				}
			}
		}


		if (state == 'closed') {
			$this.data('state','opened');
			label.text($this.data('opened'));
			$this.prev('input').val('on');
		}
		else if (state == 'opened') {
			$this.data('state','closed');
			label.text($this.data('closed'));
			$this.prev('input').val('off');
		}

		return false;
	});

	if ('on' == $('#wpv_filter_meta_html_state').val()) {
		$('.filter-html-editor').removeClass('closed');
		$('.filter-html-editor-button')
			.data('state','opened')
			.text($('.filter-html-editor-button').data('opened'));
	}
	if ('' != $('#wpv_filter_meta_html_css').val() && 'on' == $('#wpv_filter_meta_html_extra_css_state').val()) {
		$('.filter-css-editor').removeClass('closed');
		$('.filter-css-editor-button')
			.data('state','opened')
			.text($('.filter-css-editor-button').data('opened'));
	}
	if ('' != $('#wpv_filter_meta_html_js').val() && 'on' == $('#wpv_filter_meta_html_extra_js_state').val()) {
		$('.filter-js-editor').removeClass('closed');
		$('.filter-js-editor-button')
			.data('state','opened')
			.text($('.filter-js-editor-button').data('opened'));
	}
	if ('on' == $('#wpv_layout_meta_html_state').val()) {
		$('.layout-html-editor').removeClass('closed');
		$('.layout-html-editor-button')
			.data('state','opened')
			.text($('.layout-html-editor-button').data('opened'));
	}
	if ('' != $('#wpv_layout_meta_html_css').val() && 'on' == $('#wpv_layout_meta_html_extra_css_state').val()) {
		$('.layout-css-editor').removeClass('closed');
		$('.layout-css-editor-button')
			.data('state','opened')
			.text($('.layout-css-editor-button').data('opened'));
	}
	if ('' != $('#wpv_layout_meta_html_js').val() && 'on' == $('#wpv_layout_meta_html_extra_js_state').val()) {
		$('.layout-js-editor').removeClass('closed');
		$('.layout-js-editor-button')
			.data('state','opened')
			.text($('.layout-js-editor-button').data('opened'));
	}

	// wp-pointers

	$('.js-display-tooltip').click(function(){
		var $thiz = $(this);

		// hide this pointer if other pointer is opened.
		$('.wp-pointer').fadeOut(100);

		$(this).pointer({
			content: '<h3>'+$thiz.data('header')+'</h3><p>'+$thiz.data('content')+'</p>',
			position: {
				edge: 'left',
				align: 'center',
				offset: '15 0'
			}
		}).pointer('open');
	});

	if( typeof cred_cred != 'undefined'){
		cred_cred.posts();
	}
	
	if ( $( '.js-wpv-display-in-iframe' ).length == 1 ) {
		if ( $( '.js-wpv-display-in-iframe' ).val() == 'yes' ) {
			$( '.toolset-help a, .wpv-setting a' ).attr("target","_blank");
		}
	}
});

function wpv_extra_textarea_toggle_flag(element) {
	var full = false;
	if ( element == 'filter-css-editor' ) {
		full = ( codemirror_views_query_css.getValue() != '' );
	} else if ( element == 'filter-js-editor' ) {
		full = ( codemirror_views_query_js.getValue() != '' );
	} else if ( element == 'layout-css-editor' ) {
		full = ( codemirror_views_layout_css.getValue() != '' );
	} else if ( element == 'layout-js-editor' ) {
		full = ( codemirror_views_layout_js.getValue() != '' );
	}
	return full;
}

// Change status

jQuery(document).on('click', '.js-wpv-change-view-status', function(e){
	e.preventDefault();
	var newstatus = jQuery(this).data('statusto'),
		    spinnerContainer = jQuery('<div class="spinner ajax-loader">').insertAfter(jQuery(this)).show(),
		    thiz = jQuery(this),
		    update_message = jQuery(this).data('success'),
		    error_message = jQuery(this).data('unsaved'),
		    redirect_url = jQuery(this).data('redirect');
		    thiz.prop('disabled', true).removeClass('button-primary').addClass('button-secondary');
		    if (newstatus == 'trash') {
			    message_where = jQuery('.js-wpv-slug-container');
		    } else {
			    message_where = thiz.parent();
		    }
		    var data = {
			    action: 'wpv_view_change_status',
			    id: jQuery('.js-post_ID').val(),
			    newstatus: newstatus,
			    wpnonce : jQuery(this).data('nonce')
		    };
		    jQuery.ajax({
			    async:false,
		  type:"POST",
		  url:ajaxurl,
		  data:data,
		  success:function(response){
			  if ( (typeof(response) !== 'undefined') && (response == data.id)) {
				  if (newstatus == 'trash') {
					  setConfirmUnload(false);
					  jQuery(location).attr('href',redirect_url);
				  }
			  } else {
				  message_where.wpvToolsetMessage({
					  text:error_message,
				      type:'error',
				      inline:true,
				      stay:true
				  });
				  console.log( "Error: AJAX returned ", response );
			  }
		  },
		  error: function (ajaxContext) {
			  thiz.prop('disabled', false);
			  spinnerContainer.remove();
			  message_where.wpvToolsetMessage({
				  text:error_message,
				  type:'error',
				  inline:true,
				  stay:true
			  });
			  console.log( "Error: ", ajaxContext.responseText );
		  },
		  complete: function() {
			  
		  }
		    });
});

/*
 * Screen options
 */

// Screen options - manage sections checkboxes click

jQuery(document).on('change', '.js-wpv-show-hide', function(){
	wpv_show_hide_section_change(jQuery(this));
});

// Based on the screen option checkbox, show or hide the section

function wpv_show_hide_section_change(checkbox) {
	checkbox.parents('.js-wpv-show-hide-container').find('.toolset-alert').remove();
	var section = checkbox.data('section');
	var state = checkbox.attr('checked');
	var input_value = checkbox.parents('.js-wpv-screen-pref').find('.js-wpv-show-hide-value');
	var section_changed = jQuery('.js-wpv-show-hide-container').data('unclickable');
	if ('checked' == state) {
		var metasection = checkbox.parents('.js-wpv-show-hide-section').data('metasection');
		jQuery('.' + metasection).show();
		jQuery('.js-wpv-settings-' + section).fadeIn('fast');
		if ( 'filter-extra' == section ) {
			jQuery('.js-wpv-settings-container-dps-filter').fadeIn('fast');
		}
		input_value.val('on');
		if ('filter-extra' == section) {
			codemirror_views_query.refresh();
			codemirror_views_query_css.refresh();
			codemirror_views_query_js.refresh();
		}
		if ('content' == section) {
			codemirror_views_content.refresh();
		}
		if ('layout-extra' == section) {
			codemirror_views_layout.refresh();
			codemirror_views_layout_css.refresh();
			codemirror_views_layout_js.refresh();
		}
		if ('pagination' == section) {
			if ('checked' != jQuery('.js-wpv-show-hide-filter-extra').attr('checked')) {
				jQuery('.js-wpv-show-hide-filter-extra').trigger('click');
				jQuery('.js-wpv-show-hide-update').parent().wpvToolsetMessage({
					text:jQuery('.js-wpv-show-hide-container').data('pagneedsfilter'),
											      type:'info',
								  inline:true,
								  stay:true
				});
			}
		}
	} else {
		if(jQuery('.js-wpv-settings-' + section).find('.js-wpv-section-unsaved').length > 0) {
			checkbox.attr('checked', 'checked');
			jQuery('.js-wpv-show-hide-update').parent().wpvToolsetMessage({
				text:section_changed,
				type:'error',
				inline:true,
				stay:true
			});
		} else if ('filter-extra' == section && 'checked' == jQuery('.js-wpv-show-hide-pagination').attr('checked')) {
			jQuery('.js-wpv-show-hide-filter-extra').attr('checked', true);
			jQuery('.js-wpv-show-hide-update').parent().wpvToolsetMessage({
				text:jQuery('.js-wpv-show-hide-container').data('pagneedsfilter'),
										      type:'info',
								 inline:true,
								 stay:true
			});
		} else if ('filter-extra' == section && jQuery('.js-wpv-settings-container-dps-filter').find('.js-wpv-section-unsaved').length > 0 ) {
			jQuery('.js-wpv-show-hide-filter-extra').attr('checked', true);
			jQuery('.js-wpv-show-hide-update').parent().wpvToolsetMessage({
				text:jQuery('.js-wpv-show-hide-container').data('dpsneedsfilter'),
										      type:'info',
								 inline:true,
								 stay:true
			});
		} else {
			jQuery('.js-wpv-settings-' + section).hide();
			if ( 'filter-extra' == section ) {
				jQuery('.js-wpv-settings-container-dps-filter').hide();
			}
			var metasection = checkbox.parents('.js-wpv-show-hide-section').data('metasection');
			if (
				0 == checkbox.parents('.js-wpv-show-hide-section').find('.js-wpv-show-hide:checked').length &&
				jQuery('.' + metasection).find('.wpv-setting-container:not(.js-wpv-settings-container-dps-filter)').length == checkbox.parents('.js-wpv-show-hide-section').find('.js-wpv-show-hide').length
			) {
				jQuery('.' + metasection).hide();
			}
			input_value.val('off');
		}
	}
}

jQuery(document).on('click', '.js-metasection-help-query .js-toolset-help-close-main', function(){
	jQuery('.js-wpv-show-hide-query-help').prop('checked', false);
	jQuery('.js-wpv-show-hide-query-help-value').val('off');
});

jQuery(document).on('click', '.js-metasection-help-filter .js-toolset-help-close-main', function(){
	jQuery('.js-wpv-show-hide-filter-help').prop('checked', false);
	jQuery('.js-wpv-show-hide-filter-help-value').val('off');
});

jQuery(document).on('click', '.js-metasection-help-layout .js-toolset-help-close-main', function(){
	jQuery('.js-wpv-show-hide-layout-help').prop('checked', false);
	jQuery('.js-wpv-show-hide-layout-help-value').val('off');
});

jQuery(document).on('change', '.js-wpv-show-hide-help', function(){
	var state = jQuery(this).attr('checked'),
		    metasection = jQuery(this).data('metasection'),
		    purpose = jQuery('.js-view-purpose').val();
		    if ('checked' == state) {
			    jQuery('.js-for-view-purpose-' + purpose + '.js-metasection-help-' + metasection).show();
			    jQuery('.js-wpv-show-hide-' + metasection + '-help-value').val('on');
		    } else {
			    jQuery('.js-metasection-help-' + metasection).hide();
			    jQuery('.js-wpv-show-hide-' + metasection + '-help-value').val('off');
		    }
});

// Change View purpose

jQuery(document).on('change', '.js-view-purpose', function(){
	var purpose = jQuery(this).val();
	jQuery('.js-wpv-show-hide-help').each(function(){
		var state = jQuery(this).attr('checked'),
			metasection = jQuery(this).data('metasection');
	
		jQuery('.js-metasection-help-' + metasection).hide();
		if ('checked' == state) {
			jQuery('.js-for-view-purpose-' + purpose + '.js-metasection-help-' + metasection).show();
		}
	});
	wpv_set_sections_for_view_purpose(purpose);
});

// Given a View purpose, set the open and closed sections

function wpv_set_sections_for_view_purpose(purpose) {
	var all_sections = Array('query-options', 'limit-offset', 'content', 'pagination', 'filter-extra', 'layout-extra', 'pagination', 'content-filter');
	var hide_sections = Array();
	if ('all' == purpose) {
		hide_sections = Array('pagination', 'filter-extra');
	} else if ('pagination' == purpose) {
		hide_sections = Array('limit-offset');
	} else if ('slider' == purpose) {
		hide_sections = Array('limit-offset');
	} else if ('parametric' == purpose) {
		hide_sections = Array('query-options', 'limit-offset', 'pagination', 'content-filter');
	} else if ('bootstrap-grid' == purpose) {
		hide_sections = Array('layout-extra', 'content');
	} else if ('full' == purpose) {
	}

	var sections_length = all_sections.length;
	for ( var i = 0; i < sections_length; i++ ) {
		var found = false,
		hide_length = hide_sections.length;
		for ( j = 0; j < hide_length; j++ ) {
			if ( all_sections[i] == hide_sections[j] ) {
				found = true;
			}
		}

		var item = jQuery( '.js-wpv-show-hide-' + all_sections[i] );
		item.attr( 'checked', !found ).css( {'box-shadow': '0 0 5px 1px #f6921e'} );
		wpv_show_hide_section_change( item );
	}
	setTimeout( function () {
		jQuery( '.js-wpv-show-hide' ).css( {'box-shadow': 'none'} );
	}, 1000 );
}


// Show or hide layout hint extra text

jQuery(document).on('click', '.js-wpv-layout-help-extra-show', function(e){
	e.preventDefault();
	jQuery('.js-wpv-layout-help-extra').fadeIn('fast');
	jQuery(this).parent().hide();
	return false;
});

jQuery(document).on('click', '.js-wpv-layout-help-extra-hide', function(e){
	e.preventDefault();
	jQuery('.js-wpv-layout-help-extra').hide();
	jQuery('.js-wpv-layout-help-extra-show').parent().show();
	return false;
});

// Layout wizard help

function wpv_layout_wizard_hint() {
	if ( !jQuery('.js-wpv-layout-wizard-hint').hasClass('js-toolset-help-dismissed') ) {
		jQuery('.js-wpv-layout-wizard-hint').fadeIn('fast');
	}
}

jQuery(document).on('click', '.js-wpv-layout-wizard-hint .toolset-help-footer .js-toolset-help-close-forever', function(){
	var data = {
		action: 'wpv_layout_wizard_hint_disable',
		wpnonce: jQuery('.js-wpv-layout-wizard-dismiss').data('nonce')
	};
	jQuery.post(ajaxurl, data, function(response) {
		if ( (typeof(response) !== 'undefined')) {
			if (response == 0) {
				console.log( "Error: WordPress AJAX returned ", response );
			}
		} else {
			console.log( "Error: AJAX returned ", response );
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log( "Error: ", textStatus, errorThrown );
	})
	.always(function() {
		jQuery('.js-wpv-layout-wizard-hint').addClass('js-toolset-help-dismissed').hide();
	});
});

// Inline CT help

function wpv_inline_ct_hint() {
	if ( !jQuery('.js-wpv-content-template-hint').hasClass('js-toolset-help-dismissed') ) {
		if ( jQuery('#wpv-ct-add-to-editor-btn').prop('checked') == true || jQuery('input[name=wpv-ct-type]:checked').val() == 2 ){
			jQuery('.js-wpv-content-template-hint').find('.js-wpv-ct-was-not-inserted').addClass('hidden');
			jQuery('.js-wpv-content-template-hint').find('.js-wpv-ct-was-inserted').removeClass('hidden');
		} else {
			jQuery('.js-wpv-content-template-hint').find('.js-wpv-ct-was-inserted').addClass('hidden');
			jQuery('.js-wpv-content-template-hint').find('.js-wpv-ct-was-not-inserted').removeClass('hidden');
		}
		jQuery('.js-wpv-content-template-hint').fadeIn('fast');
	}
}

jQuery(document).on('click', '.js-wpv-content-template-hint .toolset-help-footer .js-toolset-help-close-forever', function(){
	var data = {
		action: 'wpv_content_template_hint_disable',
		wpnonce: jQuery('.js-wpv-content-template-dismiss').data('nonce')
	};
	jQuery.post(ajaxurl, data, function(response) {
		if ( (typeof(response) !== 'undefined')) {
			if (response == 0) {
				console.log( "Error: WordPress AJAX returned ", response );
			}
		} else {
			console.log( "Error: AJAX returned ", response );
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log( "Error: ", textStatus, errorThrown );
	})
	.always(function() {
		jQuery('.js-wpv-content-template-hint').addClass('js-toolset-help-dismissed').hide();
	});
});