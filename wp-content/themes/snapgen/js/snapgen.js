/*----------------------------------------
    BACK TO TOP
----------------------------------------*/
$(document).ready(function(){
     $(window).scroll(function () {
            if ($(this).scrollTop() > 50) {
                $('#back-to-top').fadeIn();
            } else {
                $('#back-to-top').fadeOut();
            }
        });
        // scroll body to 0px on click
        $('#back-to-top').click(function () {
            $('#back-to-top').tooltip('hide');
            $('body,html').animate({
                scrollTop: 0
            }, 800);
            return false;
        });
        
        $('#back-to-top').tooltip('show');

});

/*----------------------------------------
    PAGE SCROLL
----------------------------------------*/
$(function() {
    $('.page-scroll a').bind('click', function(event) {
        var $anchor = $(this);
        $('html, body').stop().animate({
            scrollTop: $($anchor.attr('href')).offset().top
        }, 1500, 'easeInOutExpo');
        event.preventDefault();
    });
});

/*----------------------------------------
    PRELOADER
----------------------------------------*/
jQuery(document).ready(function($) {  
     $(window).load(function(){
          $('#preloader').fadeOut('slow',function(){$(this).remove();});
     });
});


/*----------------------------------------
    NAVBAR HIDE ON CLICK
----------------------------------------*/
$('.navbar-collapse').click('li', function() {
    $('.navbar-collapse').collapse('hide');
});

/*----------------------------------------
    RESIZE FUNCTION
----------------------------------------*/
$(window).resize(function(){
        scrollSpyRefresh();
        waypointsRefresh();
});