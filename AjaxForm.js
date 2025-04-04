/**
 * Form Validation: https://getbootstrap.com/docs/5.3/forms/validation
 * FormData API:    https://developer.mozilla.org/en-US/docs/Web/API/FormData
 * Fetch API:       https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API
 * reCaptcha v3:    https://developers.google.com/recaptcha/docs/v3
 *
 * Author: Raspgot
 */

const RECAPTCHA_SITE_KEY = 'YOUR_RECAPTCHA_SITE_KEY'; // Replace with your reCAPTCHA public key

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // Select the form element with Bootstrap validation class
    const form = document.querySelector('.needs-validation');
    if (!form) return; // Exit if no form found

    // Select DOM elements: spinner, submit button and alert container
    const spinner = document.getElementById('loading-spinner');
    const submitButton = form.querySelector('button[type="submit"]');
    const alertContainer = document.getElementById('alert-status');

    // Add custom submit event listener to the form
    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Prevent default form submission
        event.stopPropagation(); // Stop event from bubbling up

        // Add Bootstrap class to show validation feedback
        form.classList.add('was-validated');

        // If the form is invalid, focus the first invalid field and stop
        if (!form.checkValidity()) {
            const firstInvalidField = form.querySelector(':invalid');
            if (firstInvalidField) firstInvalidField.focus();
            return;
        }

        // Show loading spinner and disable submit button
        spinner.classList.remove('d-none');
        submitButton.disabled = true;

        // Wait for reCaptcha to be ready
        grecaptcha.ready(function () {
            // Execute reCaptcha v3 to get the token
            grecaptcha.execute(RECAPTCHA_SITE_KEY, { action: 'submit' }).then(function (token) {
                // Create FormData object from form fields
                const formData = new FormData(form);
                // Append the reCaptcha token to form data
                formData.append('recaptcha_token', token);

                // Send the form data to the server using Fetch API
                fetch('AjaxForm.php', {
                    method: 'POST',
                    body: formData,
                })
                    .then(function (response) {
                        // Handle HTTP-level errors
                        if (!response.ok) {
                            throw new Error('Network error: ' + response.status);
                        }
                        return response.json(); // Parse the response as JSON
                    })
                    .then(function (result) {
                        // Compose the alert message from result
                        const message = result.detail ? result.message + ' ' + result.detail : result.message;
                        const alertType = result.success ? 'success' : 'danger'; // Set alert class based on status

                        // Show alert with response message
                        alertContainer.className = 'alert alert-' + alertType;
                        alertContainer.textContent = message;
                        alertContainer.classList.remove('d-none');
                        alertContainer.scrollIntoView({ behavior: 'smooth' });

                        // If the form submission was successful, reset the form
                        if (result.success) {
                            form.reset();
                            form.classList.remove('was-validated');
                        }
                    })
                    .catch(function (error) {
                        // Log any network or parsing errors
                        console.error('An error occurred:', error);
                    })
                    .finally(function () {
                        // Hide spinner and re-enable submit button
                        spinner.classList.add('d-none');
                        submitButton.disabled = false;
                    });
            });
        });
    });
});
