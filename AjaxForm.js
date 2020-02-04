// $.post (https://api.jquery.com/jQuery.post)
// reCaptcha v3 (https://developers.google.com/recaptcha/docs/v3=)
// @author Raspgot

const publicKey = "PUBLIC_KEY";

$(document).ready(function () {
    // Recaptcha init
    check_grecaptcha();

    // Submitting the form
    $("form").submit(function (e) {
        var form = $(this);
        var token = $("[name='recaptcha-token']").val();
        var btn_val = $("#submit-btn");
        var init_btn_val = btn_val.html();

        btn_val.prop("disabled", true);
        btn_val.html("<i class='fa fa-circle-o-notch fa-spin'></i>")

        $.post(form.attr("action"), form.serialize() + "&token=" + token)

            .done(function (response) {
                response = JSON.parse(response);

                btn_val.prop("disabled", false);
                btn_val.html(init_btn_val);

                if (typeof response.type !== "undefined" && response.type === "success") {
                    set_alert(response);
                    form[0].reset();
                    check_grecaptcha();
                } else {
                    set_alert(response);
                }
            })

            .fail(function (response) {
                set_alert(response);
            });

        e.preventDefault();
    });
});

// Custom alert on ajax callback
function set_alert(response) {
    var type;
    var status = $("#status");

    switch (response.type) {
        case "success":
            type = "alert-success p-2";
            break;
    
        case "error":
            type = "alert-danger p-2";
            break;
        
        default:
            type = "alert-secondary p-2";
            break;
    }
    status.html(response.response).addClass(type);
    hideOnFocus(status);
}

// Get token from API
function check_grecaptcha() {
    grecaptcha.ready(function () {
        grecaptcha.execute(publicKey, {
            action: "ajaxForm"
        }).then(function (token) {
            $("[name='recaptcha-token']").val(token);
        });
    });
}

// Hide alert on focus fields
function hideOnFocus(param) {
    $("input, textarea").focus(function () {
        param.fadeOut();
    });
}