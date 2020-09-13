// .validate (https://jqueryvalidation.org)
// .get (https://api.jquery.com/jQuery.get)
// reCaptcha v3 (https://developers.google.com/recaptcha/docs/v3)
// @author Raspgot

const publicKey = ""; //GOOGLE public key

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

$(function() {
    check_grecaptcha();
    $("form").validate({
        rules: {
            name: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            },
            message: {
                required: true,
                minlength: 5
            }
        },
        // Customize your messages
        messages: {
            name: {
                required: "Please enter your name.",
                minlength: "Must be at least 3 characters long."
            },
            email: "Please enter a valid email.",
            message: {
                required: "Please enter your message.",
                minlength: "Must be at least 5 characters long."
            }
        },
        errorClass: "invalid-feedback",
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        submitHandler: function (form) {
            $(".spinner-border").removeClass("d-none");
            $.get(form.action, $(form).serialize())
                .done(function (response) {
                    $(".toast-body").html(JSON.parse(response));
                    $(".toast").toast('show');
                    $(".spinner-border").addClass("d-none");
                    $("#submit-btn").prop("disabled", true);
                    check_grecaptcha();
                    setTimeout(function () {
                        $("#submit-btn").prop("disabled", false);
                        $("form").trigger("reset");
                        $("form").each(function () {
                            $(this).find(".form-control").removeClass("is-valid")
                        })
                    }, 3000);
                })
                .fail(function (response) {
                    $(".toast-body").html(JSON.parse(response));
                    $(".toast").toast('show');
                    $(".spinner-border").addClass("d-none");
                });
        }
    });
});