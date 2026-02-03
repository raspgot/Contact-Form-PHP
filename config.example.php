<?php

/**
 * Configuration Template
 * 
 * Copy this file to config.php and fill in your actual values.
 * NEVER commit config.php to version control!
 * 
 * @link https://github.com/raspgot/Contact-Form-PHP
 */

return [
    // ========================================================================
    // reCAPTCHA Settings
    // ========================================================================
    // Get your keys from: https://console.cloud.google.com/security/recaptcha
    'recaptcha' => [
        'secret_key'  => '',  // Your reCAPTCHA secret key
        'site_key'    => '',  // Your reCAPTCHA site key (for frontend)
        'min_score'   => 0.6, // Minimum bot score (0.0 = bot, 1.0 = human)
    ],

    // ========================================================================
    // SMTP Email Settings
    // ========================================================================
    'smtp' => [
        'host'     => '',            // e.g., 'smtp.gmail.com'
        'username' => '',            // Your email address
        'password' => '',            // Your email password or app password
        'secure'   => 'tls',         // 'tls' or 'ssl'
        'port'     => 587,           // 587 for TLS, 465 for SSL
        'auth'     => true,          // Enable SMTP authentication
    ],

    // ========================================================================
    // Email Content Settings
    // ========================================================================
    'email' => [
        'from_name'          => 'Contact Form',
        'subject_default'    => '[Contact Form] New message received',
        'subject_autoreply'  => 'We have received your message',
    ],

    // ========================================================================
    // Security & Rate Limiting
    // ========================================================================
    'security' => [
        'max_attempts'       => 5,        // Max submissions per session
        'rate_limit_window'  => 3600,     // Rate limit window in seconds (1 hour)
        'max_name_length'    => 100,      // Maximum length for name field
        'max_subject_length' => 200,      // Maximum length for subject field
        'max_message_length' => 5000,     // Maximum length for message field
    ],
];
