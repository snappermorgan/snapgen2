
var WPViews = WPViews || {};

WPViews.WPAEditScreen = function( $ ) {
	var self = this; // this is a private variable which scopes the full class to reference the Object in every scope
	self.view_id = $('.js-post_ID').val();
	self.show_hide_sections =  $( '.js-wpv-show-hide-container' ).find('.js-wpv-show-hide-value').serialize();
	self.show_hide_metasections_help =  $( '.js-wpv-show-hide-container' ).find('.js-wpv-show-hide-help-value').serialize();
	
	// ---------------------------------
	// Screen options
	// ---------------------------------
	
	// Screen options - position fix
	
	self.screen_options_fix = function() {
		var views_screen_options = $('.js-screen-meta-links-dup > div'),
		views_screen_options_container = $('.js-screen-meta-dup > div');
		$('#screen-meta-links').append(views_screen_options);
		$('#screen-meta').append(views_screen_options_container);
	};
	
	// Screen options - show/hide metasections
	
	self.show_hide_metasections_init = function() {
		$( '.js-wpv-show-hide-section' ).each( function() {
			var metasection = $( this ).data( 'metasection' );
			if (
				0 == $( this ).find( '.js-wpv-show-hide:checked' ).length &&
				$( '.' + metasection ).find( '.wpv-setting-container' ).length == $( this ).find( '.js-wpv-show-hide' ).length
			) {
				$( '.' + metasection ).hide();
			}
		});
	};
	
	// Screen options - help boxes for purposes
	
	self.show_hide_help_init = function() {
		$('.js-wpv-show-hide-help').each(function(){
			var metasection = $( this ).data( 'metasection' ),
			state = $( this ).attr( 'checked' );
			if ( 'checked' == state ) {
				jQuery( '.js-metasection-help-' + metasection ).show();
			} else {
				jQuery( '.js-metasection-help-' + metasection ).hide();
			}
		});
	};
	
	// Screen options - update automatically
	
	self.save_wpa_screen_options = function() {
		var container = $( '.js-wpv-show-hide-container' ),
		wpv_show_hide_sections = container.find('.js-wpv-show-hide-value').serialize(),
		wpv_show_hide_metasections_help = container.find('.js-wpv-show-hide-help-value').serialize();
		container.find('.toolset-alert').remove();
		
		if ( self.show_hide_sections == wpv_show_hide_sections
			&& self.show_hide_metasections_help == wpv_show_hide_metasections_help
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
			});
		}
		
	};
	
	self.screen_options_debounce_update = _.debounce( self.save_wpa_screen_options, 1000 );
	
	// Screen options - events
	
	$( document ).on( 'click', '#screen-meta-links #contextual-help-link', function() {
		// Fix when opening Help section
		// This is caused because we are adding our Screen Options in an artificial way
		// so when opening the Help tab it displays all elements inside the tab container
		$( '.metabox-prefs .js-wpv-show-hide-container' ).hide();
	});
	
	$( document ).on( 'change', '.js-wpv-show-hide-container .js-wpv-show-hide, .js-wpv-show-hide-container .js-wpv-show-hide-help', function() {
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
	// Loop selection
	// ---------------------------------
	
	// Loop selection - save automatically
	
	self.save_wpa_loop_selection_options = function() {
		view_settings['.js-wpv-loop-selection'] = jQuery('.js-loop-selection-form').serialize();
		
		var dataholder = $( '.js-wpv-loop-selection-update' ),
		update_message = dataholder.data('success'),
		unsaved_message = dataholder.data('unsaved'),
		nonce = dataholder.data('nonce'),
		spinnerContainer = $('<div class="spinner ajax-loader auto-update">').insertBefore( $( '.js-wpv-settings-archive-loop .js-wpv-setting' ) ).show(),
		view_id = self.view_id;
		dataholder.parent().find('.toolset-alert-error').remove();
		var data = {
			action: 'wpv_update_loop_selection',
			id: view_id,
			form: $('.js-loop-selection-form').serialize(),
			wpnonce: nonce
		};
		$.ajax({
			type:"POST",
			url:ajaxurl,
			data:data,
			success:function(response){
				decoded_response = $.parseJSON(response);
				if ( decoded_response.success === data.id ) {
					$('.js-loop-selection-form').html( decoded_response.wpv_settings_archive_loops );
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
	
	self.loop_selection_debounce_update = _.debounce( self.save_wpa_loop_selection_options, 1000 );
	
	// Loop selection - events
	
	$( document ).on( 'change', '.js-loop-selection-form input', function() {
		self.loop_selection_debounce_update();
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
		self.show_hide_help_init();
		// Title placeholder
		self.title_placeholder();
	};
	
	self.init(); // call the init method

};

jQuery( document ).ready( function( $ ) {
    WPViews.wpa_edit_screen = new WPViews.WPAEditScreen( $ );
});


jQuery(function($){

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

	$('.wpv-setting-container .icon-question-sign').click(function(){
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
	if ( element == 'layout-css-editor' ) {
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
		} else {
			jQuery('.js-wpv-settings-' + section).hide();
			var metasection = checkbox.parents('.js-wpv-show-hide-section').data('metasection');
			if (
				0 == checkbox.parents('.js-wpv-show-hide-section').find('.js-wpv-show-hide:checked').length &&
				jQuery('.' + metasection).find('.wpv-setting-container').length == checkbox.parents('.js-wpv-show-hide-section').find('.js-wpv-show-hide').length
			) {
				jQuery('.' + metasection).hide();
			}
			input_value.val('off');
		}
	}
}

// Message boxes display

jQuery(document).on('click', '.js-metasection-help-query .js-toolset-help-close-main', function(){
	jQuery('.js-wpv-show-hide-query-help').prop('checked', false);
	jQuery('.js-wpv-show-hide-query-help-value').val('off');
});

jQuery(document).on('click', '.js-metasection-help-layout .js-toolset-help-close-main', function(){
	jQuery('.js-wpv-show-hide-layout-help').prop('checked', false);
	jQuery('.js-wpv-show-hide-layout-help-value').val('off');
});

jQuery(document).on('change', '.js-wpv-show-hide-help', function(){
	var state = jQuery(this).attr('checked'),
		    metasection = jQuery(this).data('metasection');
	if ('checked' == state) {
		jQuery('.js-metasection-help-' + metasection).show();
		jQuery('.js-wpv-show-hide-' + metasection + '-help-value').val('on');
	} else {
		jQuery('.js-metasection-help-' + metasection).hide();
		jQuery('.js-wpv-show-hide-' + metasection + '-help-value').val('off');
	}
});

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