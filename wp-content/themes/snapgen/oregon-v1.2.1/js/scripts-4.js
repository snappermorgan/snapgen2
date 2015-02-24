/*----------------------------------------
    CONTACT FORM
----------------------------------------*/
$('.contact-form form input[type="text"], .contact-form form textarea').on('focus', function() {
    $('.contact-form form input[type="text"], .contact-form form textarea').removeClass('contact-error');
});
    $('.contact-form form').submit(function(e) {
        e.preventDefault();
    $('.contact-form form input[type="text"], .contact-form form textarea').removeClass('contact-error');
    var postdata = $('.contact-form form').serialize();
    $.ajax({
        type: 'POST',
        url: 'php/contact-4.php',
        data: postdata,
        dataType: 'json',
        success: function(json) {
            if(json.emailMessage != '') {
                $('.contact-form form .contact-email').addClass('contact-error');
            }
            if(json.nameMessage != '') {
                $('.contact-form form .contact-name').addClass('contact-error');
            }
            if(json.emailMessage == '' && json.nameMessage == '') {
                $('.contact-form form').fadeOut('fast', function() {
                    $('.contact-form').append('<p>Thanks for contacting us!<br>We will get back to you very soon.</p>');
                });
            }
        }
    });
});
