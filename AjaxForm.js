/**
 * .checkValidity | https://getbootstrap.com/docs/5.3/forms/validation
 * FormData       | https://developer.mozilla.org/en-US/docs/Web/API/FormData
 * fetch          | https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
 * reCaptcha v3   | https://developers.google.com/recaptcha/docs/v3
 *
 * Author: Raspgot
 */

const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY'; // Replace with your reCAPTCHA public key

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const form = document.querySelector('.needs-validation');
    if (!form) return;

    const spinner        = document.getElementById('loading-spinner');
    const submitButton   = form.querySelector('button[type="submit"]');
    const alertContainer = document.getElementById('alert-status');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        form.classList.add('was-validated');

        if (!form.checkValidity()) {
            const firstInvalid = form.querySelector(':invalid');
            if (firstInvalid) firstInvalid.focus();
            return;
        }

        toggleLoading(true);

        try {
            const token = await getRecaptchaToken();
            const formData = new FormData(form);
            formData.append('recaptcha_token', token);

            const response = await fetch('AjaxForm.php', {
                method: 'POST',
                body: formData,
            });

            const data = await handleResponse(response);
            showAlert(data.message, data.success ? 'success' : 'danger');

            if (data.success) {
                form.reset();
                form.classList.remove('was-validated');
            }
        } catch (error) {
            console.error('Submission error:', error);
            showAlert('An error occurred, please try again.', 'danger');
        } finally {
            toggleLoading(false);
        }
    });

    /**
     * Get reCAPTCHA v3 token
     * @returns {Promise<string>}
     */
    function getRecaptchaToken() {
        return new Promise((resolve) => {
            grecaptcha.ready(() => {
                grecaptcha
                    .execute(RECAPTCHA_SITE_KEY, { action: 'submit' })
                    .then(resolve)
                    .catch((error) => {
                        console.error('reCAPTCHA error:', error);
                        showAlert('reCAPTCHA verification failed.', 'danger');
                        toggleLoading(false);
                    });
            });
        });
    }

    /**
     * Handle fetch response
     * @param {Response} response
     * @returns {Promise<Object>}
     */
    async function handleResponse(response) {
        if (!response.ok) {
            throw new Error(`Network error: ${response.status}`);
        }

        const data = await response.json();
        if (!data || typeof data.success === 'undefined') {
            throw new Error('Invalid response format');
        }

        return {
            success: data.success,
            message: data.detail ? `${data.message} ${data.detail}` : data.message,
        };
    }

    /**
     * Toggle spinner and submit button
     * @param {boolean} isLoading
     */
    function toggleLoading(isLoading) {
        spinner.classList.toggle('d-none', !isLoading);
        submitButton.disabled = isLoading;
    }

    /**
     * Show Bootstrap alert
     * @param {string} message
     * @param {string} type 'success' | 'danger'
     */
    function showAlert(message, type = 'danger') {
        alertContainer.className = `alert alert-${type}`;
        alertContainer.textContent = message;
        alertContainer.classList.remove('d-none');
        alertContainer.scrollIntoView({ behavior: 'smooth' });
    }
});
