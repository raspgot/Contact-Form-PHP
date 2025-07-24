/**
 * - Form Validation: https://getbootstrap.com/docs/5.3/forms/validation
 * - FormData API:    https://developer.mozilla.org/docs/Web/API/FormData
 * - Fetch API:       https://developer.mozilla.org/docs/Web/API/Fetch_API
 * - reCAPTCHA v3:    https://developers.google.com/recaptcha/docs/v3
 *
 * Author: Raspgot
 */

const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY'; // Replace with your public reCAPTCHA key

document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // Get the form element that uses Bootstrap's validation classes
    const form = document.querySelector('.needs-validation');
    if (!form) return; // Stop if no form is found on the page

    // Select additional elements: loading spinner, submit button, and alert box
    const spinner = document.getElementById('loading-spinner');
    const submitButton = form.querySelector('button[type="submit"]');
    const alertContainer = document.getElementById('alert-status');

    /**
     * Enable live validation as the user types or selects options
     */
    form.querySelectorAll('input, select, textarea').forEach((field) => {
        const eventType = field.tagName === 'SELECT' ? 'change' : 'input';

        field.addEventListener(eventType, () => {
            if (!field.value.trim()) {
                // Clear feedback if field is empty
                field.classList.remove('is-valid', 'is-invalid');
            } else if (field.checkValidity()) {
                // Field is valid
                field.classList.add('is-valid');
                field.classList.remove('is-invalid');
            } else {
                // Field is invalid
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            }
        });
    });

    /**
     * Handle form submission with AJAX
     */
    form.addEventListener('submit', async (event) => {
        event.preventDefault(); // Stop the default form submission
        event.stopPropagation(); // Prevent the event from bubbling up

        // Clear any previous validation feedback
        form.querySelectorAll('.is-valid, .is-invalid').forEach((el) => el.classList.remove('is-valid', 'is-invalid'));

        // Check HTML5 field validity (required, pattern, etc.)
        if (!form.checkValidity()) {
            form.classList.add('was-validated'); // Bootstrap validation feedback
            form.querySelector(':invalid')?.focus(); // Focus first invalid input
            return;
        }

        // Show loading spinner and disable the submit button
        spinner.classList.remove('d-none');
        submitButton.disabled = true;

        try {
            // Wait for reCAPTCHA to be ready and get the token
            const token = await new Promise((resolve) => {
                grecaptcha.ready(() => {
                    grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'submit' }).then(resolve);
                });
            });

            // Prepare form data, including the reCAPTCHA token
            const formData = new FormData(form);
            formData.append('recaptcha_token', token);

            // Send data using Fetch API (AJAX)
            const response = await fetch('AjaxForm.php', {
                method: 'POST',
                body: formData,
            });

            // If server responded with an error (like 500), throw an exception
            if (!response.ok) throw new Error('Network error: ' + response.status);

            // Parse the JSON response from the server
            const result = await response.json();
            const { message, success, field } = result;
            const alertType = success ? 'success' : 'danger';

            // Highlight the invalid field if the server specified one
            if (field) {
                const fieldEl = form.querySelector(`[name="${field}"]`);
                if (fieldEl) {
                    fieldEl.classList.add('is-invalid');
                    fieldEl.reportValidity(); // Show native tooltip message
                    fieldEl.focus();
                    form.classList.remove('was-validated');
                }
            }

            // Display the success or error message
            alertContainer.className = `alert alert-${alertType} fade show`;
            alertContainer.textContent = message;
            alertContainer.classList.remove('d-none');
            alertContainer.scrollIntoView({ behavior: 'smooth' });

            // If the form was submitted successfully, reset it
            if (success) {
                form.reset();
                form.classList.remove('was-validated');
                form.querySelectorAll('.is-valid, .is-invalid').forEach((el) => el.classList.remove('is-valid', 'is-invalid'));
            }
        } catch (error) {
            // Log unexpected errors (network, parse errors, etc.)
            console.error('An error occurred:', error);
        } finally {
            // Always hide the spinner and re-enable the submit button
            spinner.classList.add('d-none');
            submitButton.disabled = false;
        }
    });
});
