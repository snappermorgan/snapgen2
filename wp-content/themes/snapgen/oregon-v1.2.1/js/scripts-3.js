/*----------------------------------------
    CONTACT FORM
----------------------------------------*/
$('.contact-form form input[type="text"], .contact-form form input[type="email"]').on('focus', function() {
    $('.contact-form form input[type="text"], .contact-form form input[type="email"]').removeClass('contact-error');
});
    $('.contact-form form').submit(function(e) {
        e.preventDefault();
    $('.contact-form form input[type="text"], .contact-form form input[type="email"]').removeClass('contact-error');
    var postdata = $('.contact-form form').serialize();
    $.ajax({
        type: 'POST',
        url: 'php/contact-3.php',
        data: postdata,
        dataType: 'json',
        success: function(json) {
            if(json.nameMessage != '') {
                $('.contact-form form .contact-name').addClass('contact-error');
            }
            if(json.emailMessage != '') {
                $('.contact-form form .contact-email').addClass('contact-error');
            }
            if(json.phoneMessage != '') {
                $('.contact-form form .contact-phone').addClass('contact-error');
            }
            if(json.emailMessage == '' && json.nameMessage == '' && json.phoneMessage == '') {
                $('.contact-form form').fadeOut('fast', function() {
                    $('.contact-form').append('<p>Thanks for contacting us!<br>We will get back to you very soon.</p>');
                });
            }
        }
    });
});