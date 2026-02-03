<?php

/**
 * Test helper functions extracted from AjaxForm.php
 * These are standalone versions for testing purposes
 */

/**
 * Sanitize user input to prevent XSS and injection attacks
 */
function sanitizeInput(string $data): string
{
    $filtered = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $data);
    if ($filtered === null) {
        $filtered = $data;
    }
    return trim(htmlspecialchars($filtered, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8', true));
}

/**
 * Convert HTML email to plain text alternative
 */
function buildAltBodyTest(string $html): string
{
    $text = preg_replace('/<br\s*\/?>(?i)/', "\n", $html) ?? $html;
    $text = preg_replace('/<\/p\s*>/i', "\n\n", $text) ?? $text;
    $text = strip_tags($text);
    return html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
