<?php

declare(strict_types=1);

class Security
{
    /**
     * Escape output to prevent XSS attacks
     *
     * @param string|null $string The string to escape
     * @return string The escaped string
     */
    public static function escape(?string $string): string
    {
        if ($string === null) {
            return '';
        }

        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate a CSRF token and store it in the session
     *
     * @return string The generated CSRF token
     */
    public static function generateCSRFToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Reason: Generate a cryptographically secure random token for CSRF protection
        $token = bin2hex(random_bytes(32));
        $_SESSION[CSRF_TOKEN_NAME] = $token;
        $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();

        return $token;
    }

    /**
     * Validate a CSRF token from the request
     *
     * @param string|null $token The token to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateCSRFToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($token === null || !isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }

        $storedToken = $_SESSION[CSRF_TOKEN_NAME];
        $tokenTime = $_SESSION[CSRF_TOKEN_NAME . '_time'] ?? 0;

        // Reason: Check token expiration to prevent replay attacks
        if ((time() - $tokenTime) > CSRF_TOKEN_LIFETIME) {
            // Mark session as expired for forced logout
            $_SESSION['csrf_expired'] = true;
            error_log('CSRF token expired - session marked for forced logout');
            return false;
        }

        // Reason: Use hash_equals to prevent timing attacks
        return hash_equals($storedToken, $token);
    }

    /**
     * Check if CSRF token has expired (for forced logout)
     *
     * @return bool True if CSRF expired and user should be logged out
     */
    public static function isCSRFExpired(): bool
    {
        return isset($_SESSION['csrf_expired']) && $_SESSION['csrf_expired'] === true;
    }

    /**
     * Force logout due to expired session
     */
    public static function forceLogout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Get the CSRF token from the session or generate a new one
     *
     * @return string The CSRF token
     */
    public static function getCSRFToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return self::generateCSRFToken();
        }

        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Hash a password using Argon2ID
     *
     * @param string $password The password to hash
     * @return string The hashed password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify a password against a hash
     *
     * @param string $password The password to verify
     * @param string $hash The hash to verify against
     * @return bool True if the password matches, false otherwise
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Sanitize a slug for URL-safe usage
     *
     * @param string $string The string to slugify
     * @return string The slugified string
     */
    public static function slugify(string $string): string
    {
        // Convert to lowercase
        $slug = strtolower($string);

        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Validate an integer input
     *
     * @param mixed $value The value to validate
     * @param int $default The default value if validation fails
     * @return int The validated integer
     */
    public static function validateInt($value, int $default = 0): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }

    /**
     * Validate a URL
     *
     * @param string|null $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    public static function validateUrl(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
