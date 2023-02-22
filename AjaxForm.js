/**
 * .validate (https://jqueryvalidation.org)
 * .post (https://api.jquery.com/jQuery.post/)
 * reCaptcha v3 (https://developers.google.com/recaptcha/docs/v3)
 * @author Raspgot
 */

const publicKey = ''; // GOOGLE public key

$(function () {
    check_grecaptcha();

    // If you add field, add rule and error message in validate function
    $('#contactform').validate({
        // Form fields rules
        rules: {
            name: {
                required: true,
                minlength: 3,
            },
            email: {
                required: true,
                email: true,
            },
            message: {
                required: true,
                minlength: 5,
            },
        },
        // Error messages
        messages: {
            name: {
                required: 'Please enter your name.',
                minlength: 'Must be at least 3 characters long.',
            },
            email: 'Please enter a valid email.',
            message: {
                required: 'Please enter your message.',
                minlength: 'Must be at least 5 characters long.',
            },
        },
        errorClass: 'invalid-feedback',
        // Dynamic validation classes
        highlight: function (element) {
            // Invalid
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element) {
            // Valid
            $(element).addClass('is-valid').removeClass('is-invalid');
        },
        // Action on submit
        submitHandler: function (form, event) {
            event.preventDefault();
            $('#sendtext').text('SENDING...');
            $.post(form.action, $(form).serialize())
                .done(function (response) {
                    alertShowing(JSON.parse(response));
                    $('#sendtext').text('SEND');
                    $('#submit-btn').prop('disabled', true);
                    check_grecaptcha();
                })
                .fail(function (response) {
                    alert(response);
                })
                .always(function () {
                    // Timeout to reset form
                    setTimeout(function () {
                        $('#submit-btn').prop('disabled', false);
                        $('form').trigger('reset');
                        $('form').each(function () {
                            $(this)
                                .find('.form-control')
                                .removeClass('is-valid');
                        });
                    }, 3000);
                });
        },
    });
});

// Get token from API
function check_grecaptcha() {
    grecaptcha.ready(function () {
        grecaptcha
            .execute(publicKey, {
                action: 'ajaxForm',
            })
            .then(function (token) {
                $("[name='recaptcha-token']").val(token);
            });
    });
}

// Show response in .alert
function alertShowing(response) {
    // Apply class alert
    if (response.error == true) {
        $('#response-alert').addClass('alert-danger');
        $('#response-alert').removeClass('alert-success');
    } else {
        $('#response-alert').addClass('alert-success');
        $('#response-alert').removeClass('alert-danger');
    }
    // Display alert with message
    $('#response-alert').html(response.message);
    $('#response-alert').removeClass('d-none');
    $('#response-alert').addClass('d-block');
}