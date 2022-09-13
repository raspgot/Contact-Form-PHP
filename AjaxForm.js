/**
 * .validate (https://jqueryvalidation.org)
 * .post (https://api.jquery.com/jQuery.post/)
 * reCaptcha v3 (https://developers.google.com/recaptcha/docs/v3)
 * @author Raspgot
 */

const publicKey = ""; // GOOGLE public key

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

// Show response in .alert
function alertShowing(response) {
    $("#response-alert").html(JSON.parse(response));
    $("#response-alert").removeClass("d-none");
    $("#response-alert").addClass("d-block");
}

$(function () {
    check_grecaptcha();
    // If you add field, add rule and error message in validate function
    $("#contactform").validate({
        // Form fields rules
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
        // Error messages
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
        // Dynamic validation classes
        highlight: function (element) {
            // Invalid
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            // Valid
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        // Action on submit
        submitHandler: function (form) {
            $(".spinner-border").removeClass("d-none");
            $("#sendtext").addClass("d-none");
            $.post(form.action, $(form).serialize())
                .done(function (response) {
                    alertShowing((response));
                    $(".spinner-border").addClass("d-none");
                    $("#sendtext").removeClass("d-none");
                    $("#submit-btn").prop("disabled", true);
                    check_grecaptcha();

                    // Timeout to reset form
                    setTimeout(function () {
                        $("#submit-btn").prop("disabled", false);
                        $("form").trigger("reset");
                        $("form").each(function () {
                            $(this).find(".form-control").removeClass("is-valid")
                        })
                    }, 2000);
                })
                .fail(function (response) {
                    alertShowing((response));
                    $(".spinner-border").addClass("d-none");
                    $("#sendtext").removeClass("d-none");
                });
        }
    });
});