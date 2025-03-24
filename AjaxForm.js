/**
 * .checkValidity | https://getbootstrap.com/docs/5.3/forms/validation
 * FormData       | https://developer.mozilla.org/en-US/docs/Web/API/FormData
 * fetch          | https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
 * reCaptcha v3   | https://developers.google.com/recaptcha/docs/v3
 *
 * Author: Raspgot
 */

const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY'; // GOOGLE public key

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const form = document.querySelector('.needs-validation');
    if (!form) return; // Stop execution if no form is found

    const spinner = document.getElementById('loading-spinner');
    const submitButton = document.querySelector('button[type="submit"]');
    const alertContainer = document.getElementById('alert-status');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        form.classList.add('was-validated');
        if (!form.checkValidity()) return;

        // Toggles the loading state (shows/hides the spinner and disables/enables the submit button)
        spinner.classList.remove('d-none');
        submitButton.disabled = true;

        try {
            const token = await executeRecaptcha();
            const formData = new FormData(form);
            formData.append('recaptcha_token', token);

            const response = await fetch('AjaxForm.php', {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error(`Network error: ${response.status}`);
            }

            const data = await response.json();
            const message = data.detail ? `${data.message} ${data.detail}` : data.message;
            displayAlert(message, data.success ? 'success' : 'danger');

            if (data.success) {
                form.reset();
                form.classList.remove('was-validated');
            }
        } catch (error) {
            console.error('Error:', error);
            displayAlert('An error occurred, please try again.', 'danger');
        } finally {
            spinner.classList.add('d-none');
            submitButton.disabled = false;
        }
    });

    /**
     * Executes Google reCAPTCHA v3 and returns the token
     * @returns {Promise<string>}
     */
    async function executeRecaptcha() {
        await new Promise((resolve) => grecaptcha.ready(resolve));
        return grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'submit' });
    }

    /**
     * Displays an alert message
     * @param {string} message The message to display
     * @param {string} type The type of alert ('success' or 'danger')
     */
    function displayAlert(message, type) {
        alertContainer.className = `alert alert-${type}`;
        alertContainer.textContent = message;
        alertContainer.classList.remove('d-none');
        alertContainer.scrollIntoView({ behavior: 'smooth' });
    }
});
