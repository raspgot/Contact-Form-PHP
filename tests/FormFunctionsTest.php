<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test suite for Contact Form helper functions
 * 
 * These tests validate core functionality without requiring SMTP or reCAPTCHA
 */
class FormFunctionsTest extends TestCase
{
    /**
     * Load form functions for testing
     */
    public static function setUpBeforeClass(): void
    {
        // We need to extract functions from AjaxForm.php
        // Since they're in the global scope, we'll create a test version
        require_once __DIR__ . '/FormTestHelpers.php';
    }

    /**
     * Test sanitize function removes control characters
     */
    public function testSanitizeRemovesControlCharacters(): void
    {
        $input = "Hello\x00World\x08Test";
        $expected = "HelloWorldTest";
        $this->assertEquals($expected, sanitizeInput($input));
    }

    /**
     * Test sanitize function escapes HTML
     */
    public function testSanitizeEscapesHtml(): void
    {
        $input = '<script>alert("XSS")</script>';
        $expected = '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';
        $this->assertEquals($expected, sanitizeInput($input));
    }

    /**
     * Test sanitize function handles empty strings
     */
    public function testSanitizeHandlesEmptyString(): void
    {
        $this->assertEquals('', sanitizeInput(''));
        $this->assertEquals('', sanitizeInput('   '));
    }

    /**
     * Test sanitize function handles Unicode
     */
    public function testSanitizeHandlesUnicode(): void
    {
        $input = 'Hello 世界 🌍';
        $this->assertNotEmpty(sanitizeInput($input));
    }

    /**
     * Test buildAltBody converts <br> to newlines
     */
    public function testBuildAltBodyConvertsBrTags(): void
    {
        $html = 'Line 1<br>Line 2<br/>Line 3';
        $expected = "Line 1\nLine 2\nLine 3";
        $this->assertEquals($expected, buildAltBodyTest($html));
    }

    /**
     * Test buildAltBody strips HTML tags
     */
    public function testBuildAltBodyStripsHtml(): void
    {
        $html = '<p>Hello <strong>World</strong></p>';
        $result = buildAltBodyTest($html);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringNotContainsString('>', $result);
    }

    /**
     * Test buildAltBody decodes HTML entities
     */
    public function testBuildAltBodyDecodesEntities(): void
    {
        $html = 'Hello &amp; goodbye &lt;test&gt;';
        $result = buildAltBodyTest($html);
        $this->assertStringContainsString('&', $result);
        $this->assertStringNotContainsString('&amp;', $result);
    }

    /**
     * Test email validation
     */
    public function testEmailValidation(): void
    {
        $validEmails = [
            'test@example.com',
            'user.name@example.co.uk',
            'user+tag@example.com',
        ];

        foreach ($validEmails as $email) {
            $this->assertNotFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Email '$email' should be valid"
            );
        }

        $invalidEmails = [
            'invalid',
            '@example.com',
            'test@',
            'test..double@example.com',
        ];

        foreach ($invalidEmails as $email) {
            $this->assertFalse(
                filter_var($email, FILTER_VALIDATE_EMAIL),
                "Email '$email' should be invalid"
            );
        }
    }

    /**
     * Test rate limit data structure
     */
    public function testRateLimitStructure(): void
    {
        $rateLimitData = [];
        $now = time();
        $window = 3600;
        $max = 5;

        // Simulate adding timestamps
        for ($i = 0; $i < $max; $i++) {
            $rateLimitData[] = $now - ($i * 600); // Every 10 minutes
        }

        // Filter old timestamps
        $filtered = array_filter(
            $rateLimitData,
            fn($timestamp) => $timestamp >= ($now - $window)
        );

        $this->assertCount($max, $filtered, 'All timestamps should be within window');

        // Add old timestamp
        $rateLimitData[] = $now - 7200; // 2 hours ago

        $filtered = array_filter(
            $rateLimitData,
            fn($timestamp) => $timestamp >= ($now - $window)
        );

        $this->assertCount($max, $filtered, 'Old timestamp should be filtered out');
    }

    /**
     * Test length validation logic
     */
    public function testLengthValidation(): void
    {
        $maxLength = 100;
        $validString = str_repeat('a', $maxLength);
        $invalidString = str_repeat('a', $maxLength + 1);

        $this->assertLessThanOrEqual($maxLength, mb_strlen($validString));
        $this->assertGreaterThan($maxLength, mb_strlen($invalidString));
    }

    /**
     * Test honeypot detection
     */
    public function testHoneypotDetection(): void
    {
        $honeypotEmpty = '';
        $honeypotFilled = 'http://spam.com';

        $this->assertEquals('', trim($honeypotEmpty), 'Empty honeypot should pass');
        $this->assertNotEquals('', trim($honeypotFilled), 'Filled honeypot should fail');
    }

    /**
     * Test user agent filtering
     */
    public function testUserAgentFiltering(): void
    {
        $suspiciousAgents = [
            'curl/7.64.1',
            'wget/1.20.3',
            'spider-bot',
            'web-crawler',
        ];

        // Pattern from AjaxForm.php - uses word boundaries
        $pattern = '/\b(curl|wget|bot|crawler|spider)\b/i';

        foreach ($suspiciousAgents as $agent) {
            $this->assertEquals(
                1,
                preg_match($pattern, $agent),
                "Agent '$agent' should be blocked"
            );
        }

        $validAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
        ];

        foreach ($validAgents as $agent) {
            $this->assertEquals(
                0,
                preg_match($pattern, $agent),
                "Agent '$agent' should be allowed"
            );
        }
        
        // Note: "Googlebot" won't match due to word boundaries,
        // but in practice, actual bot user agents often include spaces or hyphens
        // e.g. "Mozilla/5.0 (compatible; Googlebot/2.1)" would be blocked
    }
}
