<?php

declare(strict_types=1);

class AuthController
{
    /**
     * Display the login form
     */
    public static function showLoginForm(): void
    {
        // Check if user is already logged in as admin
        if (Auth::isAdmin() && !isset($_GET['page'])) {
            View::redirect(BASE_URL . '/settings');
            return;
        }

        $action = $_GET['action'] ?? null;
        $pageSlug = $_GET['page'] ?? null;

        $page = null;
        if ($pageSlug !== null) {
            $page = Page::findBySlug($pageSlug);
            if ($page === null) {
                View::notFound();
                return;
            }

            // Check if already unlocked
            if (Auth::isPageUnlocked((int) $page['id'])) {
                View::redirect(BASE_URL . '/?page=' . $pageSlug);
                return;
            }
        }

        View::render('auth/login', [
            'action' => $action,
            'page' => $page
        ]);
    }

    /**
     * Handle login form submission
     */
    public static function login(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            Auth::setFlashMessage('error', 'Invalid CSRF token. Please try again.');
            View::redirectBack(BASE_URL . '/login.php');
            return;
        }

        $password = $_POST['password'] ?? '';
        $action = $_POST['action'] ?? null;
        $pageSlug = $_POST['page'] ?? null;

        // Reason: Handle admin login
        if ($action === 'admin') {
            $adminPasswordHash = View::setting('admin_password_hash');

            if ($adminPasswordHash !== null && Security::verifyPassword($password, $adminPasswordHash)) {
                Auth::loginAsAdmin();
                Auth::setFlashMessage('success', 'Welcome back, admin!');
                View::redirect(BASE_URL . '/settings');
                return;
            }

            Auth::setFlashMessage('error', 'Invalid password.');
            View::redirectBack(BASE_URL . '/login.php?action=admin');
            return;
        }

        // Reason: Handle page-specific login
        if ($pageSlug !== null) {
            $page = Page::findBySlug($pageSlug);

            if ($page === null) {
                View::notFound();
                return;
            }

            $pageId = (int) $page['id'];

            // Reason: Check page password or admin password as fallback
            $adminPasswordHash = View::setting('admin_password_hash');
            $isValidPagePassword = Page::verifyPassword($pageId, $password);
            $isValidAdminPassword = $adminPasswordHash !== null && Security::verifyPassword($password, $adminPasswordHash);

            if ($isValidPagePassword || $isValidAdminPassword) {
                Auth::unlockPage($pageId);

                // If admin password was used, also grant admin access
                if ($isValidAdminPassword) {
                    Auth::loginAsAdmin();
                }

                Auth::setFlashMessage('success', 'Page unlocked successfully.');
                View::redirect(BASE_URL . '/?page=' . $pageSlug);
                return;
            }

            Auth::setFlashMessage('error', 'Invalid password.');
            View::redirectBack(BASE_URL . '/login.php?page=' . urlencode($pageSlug));
            return;
        }

        Auth::setFlashMessage('error', 'Invalid login request.');
        View::redirect(BASE_URL);
    }

    /**
     * Handle logout
     */
    public static function logout(): void
    {
        Auth::logout();
        View::redirect(BASE_URL);
    }
}
