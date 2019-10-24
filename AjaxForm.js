// $.post (https://api.jquery.com/jQuery.post)
// reCaptcha v3 (https://developers.google.com/recaptcha/docs/v3=)
// @author Raspgot

const publicKey = '';

function check_grecaptcha() {
    grecaptcha.ready(function () {
        grecaptcha.execute(publicKey, {
            action: 'ajaxForm'
        }).then(function (token) {
            $('[name="recaptcha-token"]').val(token);
        });
    });
}

// Hide alert <div> on focus fields
function hideOnFocus($param) {
    $("input, textarea").focus(function () {
        $param.fadeOut();
    });
}

// Recaptcha check on DOM ready
$(function () {
    check_grecaptcha()
});

// Submit the form
$(' #ajaxForm ').on('submit', function (e) {
    e.preventDefault();

    let $form = $(this);
    let $token = $('[name="recaptcha-token"]').val();
    let $respError   = $form.find('.response-error');
    let $respSuccess = $form.find('.response-success');

    $.post(
        $form.attr('action'),
        $form.serialize() + '&token=' + $token
    )

    .done(function (response) {
        response = JSON.parse(response);

        if (response.type && response.type === 'success') {
            $respSuccess.removeClass("d-none");
            $respSuccess.hide().html(response.response).fadeIn();
            $form[0].reset();
            check_grecaptcha()
            hideOnFocus($respSuccess);
        } else {
            $respError.removeClass("d-none");
            $respError.hide().html(response.response).fadeIn();
            hideOnFocus($respError);
        }
    })

    .fail(function (response) {
        $respError.removeClass("d-none");
        $respError.html(response.responseText).show();
        hideOnFocus($respError);
    });

});