(function($) {
  "use strict";
	jQuery(window).scroll(function(){
		var $hideOnMobile = jQuery('.ult-no-mobile').length;
		if(! /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ){
			animate_block();
		} else  {
			if($hideOnMobile >= 1) 
				jQuery(".ult-animation").css("opacity",1);
			else
				animate_block();
		}
		jQuery('.vc-row-fade').vc_fade_row();
		jQuery('.vc-row-translate').vc_translate_row();
	});
	
	jQuery(document).ready(function() {
		var $hideOnMobile = jQuery('.ult-no-mobile').length;
		//console.log(navigator.userAgent);
		//console.log("On Mobile - "+$hideOnMobile);
		if(! /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) 
		{
			animate_block();
		} else  {
			if($hideOnMobile >= 1) 
				jQuery(".ult-animation").css("opacity",1);
			else
				animate_block();
		}
	});

	// CSS3 Transitions.
	function animate_block(){
		jQuery('.ult-animation').each(function(){
			if(jQuery(this).attr('data-animate')) {
				//var child = jQuery(this).children('div');
				var child2 = jQuery(this).children('*');
				//var child = jQuery('.ult-animation > *');
				//console.log(child);
				var animationName = jQuery(this).attr('data-animate'),
					animationDuration = jQuery(this).attr('data-animation-duration')+'s',
					animationIteration = jQuery(this).attr('data-animation-iteration'),
					animationDelay = jQuery(this).attr('data-animation-delay');
				var style = 'opacity:1;-webkit-animation-delay:'+animationDelay+'s;-webkit-animation-duration:'+animationDuration+';-webkit-animation-iteration-count:'+animationIteration+'; -moz-animation-delay:'+animationDelay+'s;-moz-animation-duration:'+animationDuration+';-moz-animation-iteration-count:'+animationIteration+'; animation-delay:'+animationDelay+'s;animation-duration:'+animationDuration+';animation-iteration-count:'+animationIteration+';';
				var container_style = 'opacity:1;-webkit-transition-delay: '+(animationDelay)+'s; -moz-transition-delay: '+(animationDelay)+'s; transition-delay: '+(animationDelay)+'s;';
				if(isAppear(jQuery(this))){
					var p_st = jQuery(this).attr('style');
					if(typeof(p_st) == 'undefined'){
						p_st = 'test';
					}
					p_st = p_st.replace(/ /g,'');
					if(p_st == 'opacity:0;'){
						if( p_st.indexOf(container_style) !== 0 ){
							jQuery(this).attr('style',container_style);
						}
					}
				}
				//jQuery(this).bsf_appear(function() {
				jQuery.each(child2,function(index,value){
					var $this = jQuery(value);
					var prev_style = $this.attr('style');
					if(typeof(prev_style) == 'undefined'){
						prev_style = 'test';
					}
					var new_style = '';
					if( prev_style.indexOf(style) == 0 ){
						new_style = prev_style;
					} else {
						new_style = style+prev_style;
					}
					$this.attr('style',new_style);
					if(isAppear($this)){
						$this.addClass('animated').addClass(animationName);
					}
				});
			} 
		});
	}

	function isAppear(id){
		var window_scroll = jQuery(window).scrollTop();
		var window_height = jQuery(window).height();
		
		if(jQuery(id).hasClass('ult-animate-viewport'))
			var start_effect = jQuery(id).data('opacity_start_effect');
		
		if(typeof(start_effect) === 'undefined' || start_effect == '')
			var percentage = 2;
		else
			var percentage = 100 - start_effect;
				
		var element_height = jQuery(id).outerHeight();
		var element_top = jQuery(id).offset().top;
		var position = element_top - window_scroll;

		var cut = window_height - (window_height * (percentage/100));
	
		if(position <= cut)
			return true;
		else
			return false;
	};

})(jQuery);
//ready