/**
 * .checkValidity | https://getbootstrap.com/docs/5.3/forms/validation
 * FormData | https://developer.mozilla.org/en-US/docs/Web/API/FormData
 * fetch | https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
 * reCaptcha v3 | https://developers.google.com/recaptcha/docs/v3
 *
 * @author Raspgot
 */

const RECAPTCHA_SITE_KEY = 'RECAPTCHA_SITE_KEY'; // GOOGLE public key

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const form = document.querySelector('.needs-validation');
    if (!form) return; // Stop execution if no form is found

    const spinner = document.getElementById('loading-spinner');
    const button = document.querySelector('button[type="submit"]');
    const formAlert = document.getElementById('alert-statut');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        form.classList.add('was-validated');
        if (!form.checkValidity()) return;

        spinner.classList.remove('d-none');
        button.disabled = true;

        try {
            // Retrieve the reCAPTCHA token
            const token = await executeRecaptcha();
            const formData = new FormData(form);
            formData.append('recaptcha_token', token);

            const response = await fetch('AjaxForm.php', {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) throw new Error('Network error');

            const data = await response.json();
            const message = data.detail ? `${data.message} ${data.detail}` : data.message;
            showMessage(message, data.success ? 'success' : 'danger');

            if (data.success) {
                form.reset();
                form.classList.remove('was-validated');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('An error occurred, please try again', 'danger');
        } finally {
            spinner.classList.add('d-none');
            button.disabled = false;
        }
    });

    /**
     * Execute Google reCAPTCHA v3 and return the token
     * @returns {Promise<string>}
     */
    async function executeRecaptcha() {
        await new Promise((resolve) => grecaptcha.ready(resolve));
        return grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'submit' });
    }

    /**
     * Display an alert message
     * @param {string} message The message to display
     * @param {string} type The type of alert ('success' or 'danger')
     */
    function showMessage(message, type) {
        formAlert.className = `alert alert-${type}`;
        formAlert.textContent = message;
        formAlert.classList.remove('d-none');
        formAlert.scrollIntoView({ behavior: 'smooth' });
    }
});
