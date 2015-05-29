;// Themify Theme Scripts - http://themify.me/

// Initialize object literals
var FixedHeader = {},
	ThemeTransition = {};

/////////////////////////////////////////////
// jQuery functions					
/////////////////////////////////////////////
(function($){

// Initialize carousels //////////////////////////////
function createCarousel(obj) {
	obj.each(function() {
		var $this = $(this);
		$this.carouFredSel({
			responsive : true,
			prev : '#' + $this.data('id') + ' .carousel-prev',
			next : '#' + $this.data('id') + ' .carousel-next',
			pagination : {
				container : '#' + $this.data('id') + ' .carousel-pager'
			},
			circular : true,
			infinite : true,
			scroll : {
				items : 1,
				wipe : true,
				fx : $this.data('effect'),
				duration : parseInt($this.data('speed'))
			},
			auto : {
				play : !!('off' != $this.data('autoplay')),
				pauseDuration : 'off' != $this.data('autoplay') ? parseInt($this.data('autoplay')) : 0
			},
			items : {
				visible : {
					min : 1,
					max : 1
				},
				width : 222
			},
			onCreate : function() {
				$this.closest('.slideshow-wrap').css({
					'visibility' : 'visible',
					'height' : 'auto'
				});
				var $testimonialSlider = $this.closest('.testimonial.slider');
				if( $testimonialSlider.length > 0 ) {
					$testimonialSlider.css({
						'visibility' : 'visible',
						'height' : 'auto'
					});
				}
				$(window).resize();
			}
		});
	});
}

// Test if touch event exists //////////////////////////////
function is_touch_device() {
	return 'true' == themifyScript.isTouch;
}

// Fixed Header /////////////////////////
FixedHeader = {
	init: function() {
		var cons = 10;
		this.headerHeight = $('#headerwrap').height() - cons;
		this.headerOffset = $('#headerwrap').offset().top;
		$(window)
		.on('scroll', this.activate)
		.on('touchstart.touchScroll', this.activate)
		.on('touchmove.touchScroll', this.activate);
		if ( is_touch_device() ) {
			$('body').addClass('mobile-body');
		}
	},

	activate: function() {
		var $window = $(window),
			scrollTop = $window.scrollTop();

		if( scrollTop > ( FixedHeader.headerHeight + FixedHeader.headerOffset ) ) {
			FixedHeader.scrollEnabled();

			if ( is_touch_device() ) {
				setTimeout(function(){
					FixedHeader.scrollEnabled();
				}, 100);
			}
		} else {
			FixedHeader.scrollDisabled();
			if ( is_touch_device() ) {
				setTimeout(function(){
					FixedHeader.scrollDisabled();
				}, 100);
			}
		}
	},

	scrollDisabled: function() {
		$('#headerwrap').removeClass('fixed-header');
		$('#header').removeClass('header-on-scroll');
		$('#pagewrap').css('padding-top', '');
		$('body').removeClass('fixed-header-on');
	},

	scrollEnabled: function() {
		$('#headerwrap').addClass('fixed-header');
		$('#header').addClass('header-on-scroll');
		$('#pagewrap').css('padding-top', FixedHeader.headerHeight);
		$('body').addClass('fixed-header-on');
	}
};

// Theme Transition Animation /////////////////////////
ThemeTransition = {
	init: function() {
		this.setup();
	},
	setup: function() {
		if ( typeof s !== 'undefined') {
			// shortcode columns add class
			$('.col2-1.first, .col3-1.first, .col3-2.first, .col4-1.first, .col4-2.first, .col4-3.first').each(function(){
				var $this = $(this);
				if($this.hasClass('col2-1')) {
					$this.next('.col2-1').addClass('last');
					$this.next('.col4-1').addClass('third').next('.col4-1').addClass('last');
				} else if($this.hasClass('col3-1')) {
					$this.next('.col3-1').addClass('second').next('.col3-1').addClass('last');
					$this.next('.col3-2').addClass('last');
				} else if($this.hasClass('col3-2')) {
					$this.next('.col3-1').addClass('last');
				} else if($this.hasClass('col4-1')) {
					$this.next('.col4-1').addClass('second').next('.col4-1').addClass('third').next('.col4-1').addClass('last');
				} else if($this.hasClass('col4-2')) {
					$this.next('.col4-2').addClass('last');
					$this.next('.col4-1').addClass('third').next('.col4-1').addClass('last');
				} else if($this.hasClass('col4-3')) {
					$this.next('.col4-1').addClass('last');
				}
			});
			var col_nums = 1;
			$('.col-full').each(function(){
				var $this = $(this);
				$this.removeClass('first last');
				if( col_nums % 2 == 0) {
					$this.addClass('last');
				} else {
					$this.addClass('first');
				}
				col_nums += 1;
			});
			// Global Animation
			$.each(themifyScript.transitionSetup.selectors, function(key, val){
				$(val).addClass(themifyScript.transitionSetup.effect);
			});
			// Specific Animation
			$.each(themifyScript.transitionSetup.specificSelectors, function(selector, effect){
				$(selector).addClass(effect);
			});
		}
	}
};

// Scroll to Element //////////////////////////////
function themeScrollTo(offset) {
	$('body,html').animate({ scrollTop: offset }, 800);
}

// DOCUMENT READY
$(document).ready(function() {

	var $body = $('body'), $placeholder = $('[placeholder]'), $charts = $('.chart'), $skills = $('.progress-bar');

	// Fixed header
	if( ('' != themifyScript.fixedHeader && ! themifyScript.scrollingEffectOn) || (! is_touch_device() && '' != themifyScript.fixedHeader) ){
		FixedHeader.init();
	}

	/////////////////////////////////////////////
	// Chart Initialization
	/////////////////////////////////////////////
	if( typeof $.fn.easyPieChart !== 'undefined' ) {
		$charts.each(function(){
			var $self = $(this),
				barColor = $self.data('color'),
				percent = $self.data('percent');
			$.each(themifyScript.chart, function(index, value){
				if( 'false' == value || 'true' == value ){
					themifyScript.chart[index] = 'false'!=value;
				} else if( parseInt(value) ){
					themifyScript.chart[index] = parseInt(value);
				} else if( parseFloat(value) ){
					themifyScript.chart[index] = parseFloat(value);
				}
			});
			if( typeof barColor !== 'undefined' ) 
				themifyScript.chart.barColor = '#' + barColor.toString().replace('#', '');
			$self.easyPieChart( themifyScript.chart );
			$self.data('easyPieChart').update(0);
			if( typeof $.waypoints !== 'undefined' && themifyScript.scrollingEffectOn ) {
				$self.waypoint(function(direction){
					$self.data('easyPieChart').update(percent);
				}, {offset: '80%'});
				$self.waypoint(function(direction){
					if(direction === 'up') {
						$self.data('easyPieChart').update(0);
					}
				}, {offset: '92%'});
			} else {
				$self.data('easyPieChart').update(percent);	
			}
		});
	}

	/////////////////////////////////////////////
	// Skillset Animation
	/////////////////////////////////////////////
	if( themifyScript.scrollingEffectOn ) {
		$skills.each(function(){
			var $self = $(this).find('span'),
				percent = $self.data('percent');
			
			$self.width(0).on('inview', function(event, isInView, visiblePartX, visiblePartY) {
				if (isInView) {
					if($(this).hasClass('animated')) return;

					$(this).animate({width: percent}, 800,function(){
						$(this).addClass('animated');
					});
				} else {
					// element has gone out of viewport
					$(this).removeClass('animated').width(0);
				}
			});
		});
	}


	/////////////////////////////////////////////
	// Scroll to top
	/////////////////////////////////////////////
	$('.back-top a').on('click', function(e){
		e.preventDefault();
		themeScrollTo(0);
	});

	// anchor scrollTo
	$body.on('click', 'a[href*=#]', function(e){
		var url = $(this).prop('href'),
			idx = url.indexOf('#'),
			hash = idx != -1 ? url.substring(idx+1) : '',
			offset = 0;

		if(hash.length > 1 && $('#' + hash).length > 0 && hash !== 'header') {
			offset = $('#' + hash).offset().top;
			// If header is set to fixed, calculate this
			if ( $('.fixed-header' ).length > 0 ) {
				offset += $( '#headerwrap' ).outerHeight();
			}

			themeScrollTo(offset);
			e.preventDefault();
		}
		
		// close mobile menu
		if($(window).width() <= 780 && $('#main-nav').is(':visible')){
			$('#menu-icon').trigger('click');
		}
	});

	/////////////////////////////////////////////
	// Toggle main nav on mobile
	/////////////////////////////////////////////
	$body.on('click', '#menu-icon', function(e){
		e.preventDefault();
		$('#main-nav').fadeToggle();
		$('#top-nav', $('#headerwrap')).hide();
		$(this).toggleClass('active');
	});

	$body.on('touchstart touchmove touchend', '#main-nav', function(e) {
		e.stopPropagation();
	});

	/////////////////////////////////////////////
	// Add class "first" to first elements
	/////////////////////////////////////////////
	$('.highlight-post:odd').addClass('odd');

	/////////////////////////////////////////////
	// Lightbox / Fullscreen initialization
	/////////////////////////////////////////////
	if(typeof ThemifyGallery !== 'undefined') {
		ThemifyGallery.init({'context': $(themifyScript.lightboxContext)});
	}

	// Transition Effect
	if ( themifyScript.scrollingEffectOn ) {
		ThemeTransition.init();
	}

});

// WINDOW LOAD
$(window).load(function() {
	// scrolling nav
	if ( typeof($.fn.themifySectionHighlight) !== 'undefined' && themifyScript.scrollingEffectOn ) {
		$('body').themifySectionHighlight();
	}

	/////////////////////////////////////////////
	// Carousel initialization
	/////////////////////////////////////////////
	if( typeof $.fn.carouFredSel !== 'undefined' ) {
		createCarousel($('.slideshow'));
	}

});
	
})(jQuery);