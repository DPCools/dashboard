<?php

declare(strict_types=1);

class Auth
{
    /**
     * Start a session if one hasn't been started
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();

            // Reason: Regenerate session ID periodically to prevent session fixation attacks
            if (!isset($_SESSION['last_regeneration'])) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
    }

    /**
     * Check if the user is authenticated as admin
     *
     * @return bool True if authenticated as admin, false otherwise
     */
    public static function isAdmin(): bool
    {
        self::startSession();
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    /**
     * Set the user as authenticated admin
     */
    public static function loginAsAdmin(): void
    {
        self::startSession();
        $_SESSION['is_admin'] = true;
        $_SESSION['login_time'] = time();
    }

    /**
     * Check if a specific page is unlocked
     *
     * @param int $pageId The page ID to check
     * @return bool True if unlocked, false otherwise
     */
    public static function isPageUnlocked(int $pageId): bool
    {
        self::startSession();

        // Reason: Admin users have access to all pages
        if (self::isAdmin()) {
            return true;
        }

        return isset($_SESSION['unlocked_pages'])
            && is_array($_SESSION['unlocked_pages'])
            && in_array($pageId, $_SESSION['unlocked_pages'], true);
    }

    /**
     * Unlock a specific page for the current session
     *
     * @param int $pageId The page ID to unlock
     */
    public static function unlockPage(int $pageId): void
    {
        self::startSession();

        if (!isset($_SESSION['unlocked_pages'])) {
            $_SESSION['unlocked_pages'] = [];
        }

        if (!in_array($pageId, $_SESSION['unlocked_pages'], true)) {
            $_SESSION['unlocked_pages'][] = $pageId;
        }
    }

    /**
     * Log out the user and destroy the session
     */
    public static function logout(): void
    {
        self::startSession();

        // Reason: Clear all session data and destroy the session cookie
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Redirect to the login page
     *
     * @param string|null $page The page slug to return to after login
     */
    public static function redirectToLogin(?string $page = null): void
    {
        $url = BASE_URL . '/login.php';

        if ($page !== null) {
            $url .= '?page=' . urlencode($page);
        } else {
            $url .= '?action=admin';
        }

        header('Location: ' . $url);
        exit;
    }

    /**
     * Require admin authentication or redirect to login
     */
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            self::redirectToLogin();
        }
    }

    /**
     * Require page access or redirect to login
     *
     * @param int $pageId The page ID to check
     * @param string $pageSlug The page slug for redirect
     */
    public static function requirePageAccess(int $pageId, string $pageSlug): void
    {
        if (!self::isPageUnlocked($pageId)) {
            self::redirectToLogin($pageSlug);
        }
    }

    /**
     * Set a flash message in the session
     *
     * @param string $type The message type (success, error, info, warning)
     * @param string $message The message text
     */
    public static function setFlashMessage(string $type, string $message): void
    {
        self::startSession();
        $_SESSION['flash_message'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Get and clear the flash message from the session
     *
     * @return array|null The flash message array or null if none exists
     */
    public static function getFlashMessage(): ?array
    {
        self::startSession();

        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            return $message;
        }

        return null;
    }
}
