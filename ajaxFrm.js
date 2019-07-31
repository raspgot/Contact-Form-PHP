function check_grecaptcha() {
    grecaptcha.ready(function () {
        grecaptcha.execute('6Leffq4UAAAAAKLIsZ4HLYVmrC3okzORHiAlObYe', {
            action: 'ajaxForm'
        }).then(function (token) {
            $('[name="recaptcha-token"]').val(token);
        });
    });
}

function hideOnFocus($param) {
    $("input, textarea").focus(function () {
        $param.fadeOut();
    });
}

// Recaptcha check
$(function () {
    check_grecaptcha()
});

// Submit form
$(' #ajaxForm ').on('submit', function (e) {
    e.preventDefault();

    var $form = $(this);
    var $token = $('[name="recaptcha-token"]').val();
    var $responseSuccess = $form.find('.response-success');
    var $responseError = $form.find('.response-error');

    $.ajax({
        type: 'POST',
        url: $form.attr('action'),
        data: $form.serialize() + '&token=' + $token,

        success: function (response) {
            response = JSON.parse(response);

            if (response.type && response.type === 'success') {
                $responseSuccess.removeClass("d-none");
                $responseSuccess.hide().html(response.response).fadeIn();
                $form[0].reset();
                check_grecaptcha()
                hideOnFocus($responseSuccess);
            } else {
                $responseError.removeClass("d-none");
                $responseError.hide().html(response.response).fadeIn();
                hideOnFocus($responseError);
            }
        },

        error: function (response) {
            $responseError.removeClass("d-none");
            $responseError.html(response.responseText).show();
            hideOnFocus($responseError);
        }
    });

});