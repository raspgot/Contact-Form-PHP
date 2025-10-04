/**
 * - Form Validation: https://getbootstrap.com/docs/5.3/forms/validation
 * - FormData API:    https://developer.mozilla.org/docs/Web/API/FormData
 * - Fetch API:       https://developer.mozilla.org/docs/Web/API/Fetch_API
 * - reCAPTCHA v3:    https://developers.google.com/recaptcha/docs/v3
 *
 * Author: Raspgot
 */

/**
 * Public reCAPTCHA v3 site key (frontend only)
 * Replace this placeholder with your actual key
 * @constant {string}
 */
const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY';

/**
 * @typedef {Object} AjaxResponse
 * @property {boolean} success Indicates if backend processing succeeded
 * @property {string}  message Human readable status or error
 * @property {string=} field Optional form field name that failed validation
 */

/**
 * @typedef {HTMLInputElement|HTMLTextAreaElement|HTMLSelectElement} FormControl
 */

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    /** @type {HTMLFormElement|null} */
    const form = document.querySelector('.needs-validation');
    if (!form) return;

    /** @type {HTMLElement|null} */
    const spinner = document.getElementById('loading-spinner');
    /** @type {HTMLButtonElement|null} */
    const submitButton = form.querySelector('button[type="submit"]');
    /** @type {HTMLElement|null} */
    const alertContainer = document.getElementById('alert-status');
    /** @type {boolean} */
    let inFlight = false;

    // Live validation
    form.querySelectorAll('input, select, textarea').forEach((field) => {
        const eventName = field.tagName === 'SELECT' ? 'change' : 'input';
        field.addEventListener(eventName, () => {
            if (!field.value.trim()) {
                field.classList.remove('is-valid', 'is-invalid');
            } else if (field.checkValidity()) {
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            } else {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            }
        });
    });

    // Handle form submission with AJAX
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        event.stopPropagation();

        if (inFlight) return;

        form.classList.remove('was-validated');
        form.querySelectorAll('.is-valid, .is-invalid').forEach((el) => el.classList.remove('is-valid', 'is-invalid'));

        // Native HTML5 validity check
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            form.querySelector(':invalid')?.focus();
            return;
        }

        const formData = new FormData(form);
        const endpoint = 'AjaxForm.php';

        // Show loading spinner and disable form
        if (spinner) spinner.classList.remove('d-none');
        if (submitButton) submitButton.disabled = true;
        form.querySelectorAll('input, select, textarea, button').forEach((el) => {
            if (el !== submitButton) el.disabled = true;
        });
        inFlight = true;

        try {
            if (!RECAPTCHA_SITE_KEY || RECAPTCHA_SITE_KEY === 'YOUR_RECAPTCHA_SITE_KEY') {
                throw new Error('⚠️ Missing reCAPTCHA site key.');
            }
            if (typeof grecaptcha === 'undefined' || !grecaptcha?.ready) {
                throw new Error('⚠️ reCAPTCHA not loaded.');
            }

            // Wait for reCAPTCHA to be ready and get the token
            const token = await new Promise((resolve, reject) => {
                try {
                    grecaptcha.ready(() => {
                        grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'submit' }).then(resolve).catch(reject);
                    });
                } catch (e) {
                    reject(e);
                }
            });

            // Append token to input form
            formData.append('recaptcha_token', token);

            // Send data using Fetch API (AJAX)
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) throw new Error(`⚠️ Network error: ${response.status}`);

            /** @type {AjaxResponse} */
            let result;
            try {
                result = await response.json();
            } catch {
                throw new Error('⚠️ Invalid JSON response.');
            }

            const success = !!result?.success;
            const message = result?.message || (success ? 'Success.' : 'An error occurred.');
            const field = result?.field;

            // Highlight the invalid field
            if (field) {
                const target = form.querySelector(`[name="${CSS.escape(field)}"]`);
                if (target) {
                    target.classList.add('is-invalid');
                    target.focus();
                    form.classList.remove('was-validated');
                }
            }

            if (alertContainer) {
                alertContainer.className = `alert alert-${success ? 'success' : 'danger'} fade show`;
                alertContainer.textContent = message;
                alertContainer.classList.remove('d-none');
                alertContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            // If the form was submitted successfully, reset it
            if (success) {
                form.reset();
                form.classList.remove('was-validated');
                form.querySelectorAll('.is-valid, .is-invalid').forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
            }
        } catch (err) {
            console.error(err);
            if (alertContainer) {
                alertContainer.className = 'alert alert-danger fade show';
                alertContainer.textContent = err?.message || 'Unexpected error.';
                alertContainer.classList.remove('d-none');
            }
        } finally {
            // Hide loading spinner and enable form
            if (spinner) spinner.classList.add('d-none');
            if (submitButton) submitButton.disabled = false;
            form.querySelectorAll('input, select, textarea, button').forEach((el) => {
                if (el !== submitButton) el.disabled = false;
            });
            inFlight = false;
        }
    });
});
