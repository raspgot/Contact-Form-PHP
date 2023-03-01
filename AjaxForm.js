/**
 * .checkValidity | https://getbootstrap.com/docs/5.3/forms/validation
 * FormData | https://developer.mozilla.org/en-US/docs/Web/API/FormData
 * fetch | https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
 * reCaptcha v3 | https://developers.google.com/recaptcha/docs/v3
 * 
 * @author Raspgot
 */

const publicKey = 'GOOGLE_PUBLIC_KEY'; // GOOGLE public key

onload = () => {
    'use strict'

    checkRecaptcha();

    let forms = document.querySelectorAll('.needs-validation');
    let spinner = document.getElementById('loading-spinner');

    Array.prototype.filter.call(forms, function (form) {
        form.addEventListener('submit', function (event) {
            if (form.checkValidity() === false) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
            if (form.checkValidity() === true) {
                event.preventDefault();
                form.classList.remove('was-validated');
                spinner.classList.remove('d-none');

                let data = new FormData(form);
                let alertClass = 'alert-danger';

                fetch('AjaxForm.php', {
                    method: 'post',
                    body: data
                }).then((data) => {
                    return data.text();
                }).then((txt) => {
                    txt = JSON.parse(txt);
                    if (txt.error === false) {
                        alertClass = 'alert-success';
                    }
                    let alertBox = '<div class="alert ' + alertClass + '">' + txt.message + '</div>';
                    if (alertClass && txt) {
                        form.querySelector('#alert-statut').insertAdjacentHTML('beforeend', alertBox);
                        form.reset();
                        checkRecaptcha();
                    }
                    spinner.classList.add('d-none');
                    setTimeout(function () {
                        form.querySelector('#alert-statut').innerHTML = '';
                    }, 5000);
                }).catch((err) => {
                    console.log('Error encountered: ' + err);
                    spinner.classList.add('d-none');
                });
            }
        }, false);
    });
};

var checkRecaptcha = () => {
    grecaptcha.ready(function () {
        grecaptcha.execute(publicKey, {
            action: 'submit'
        }).then(function (token) {
            // input with recaptcha-token name take the recaptcha token value
            document.getElementsByName('recaptcha-token')[0].value = token;
        });
    });
};