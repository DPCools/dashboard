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
            View::redirect(View::url('/settings'));
            return;
        }

        $action = $_GET['action'] ?? null;
        $pageSlug = $_GET['page'] ?? null;
        $expired = isset($_GET['expired']) && $_GET['expired'] === '1';
        $returnUrl = $_GET['return'] ?? null;

        $page = null;
        if ($pageSlug !== null) {
            $page = Page::findBySlug($pageSlug);
            if ($page === null) {
                View::notFound();
                return;
            }

            // Check if already unlocked
            if (Auth::isPageUnlocked((int) $page['id'])) {
                View::redirect(View::url('/?page=' . $pageSlug));
                return;
            }
        }

        View::render('auth/login', [
            'action' => $action,
            'page' => $page,
            'expired' => $expired,
            'returnUrl' => $returnUrl
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
            View::redirectBack(View::url('/login.php'));
            return;
        }

        $password = $_POST['password'] ?? '';
        $action = $_POST['action'] ?? null;
        $pageSlug = $_POST['page'] ?? null;
        $returnUrl = $_POST['return_url'] ?? null;

        // Reason: Handle admin login
        if ($action === 'admin') {
            $adminPasswordHash = View::setting('admin_password_hash');

            if ($adminPasswordHash !== null && Security::verifyPassword($password, $adminPasswordHash)) {
                Auth::loginAsAdmin();
                Auth::setFlashMessage('success', 'Welcome back, admin!');

                // Redirect to return URL if provided, otherwise to settings
                if ($returnUrl) {
                    // Handle different return URL formats
                    if (strpos($returnUrl, '?page=') === 0) {
                        // Format: ?page=slug
                        View::redirect(View::url('/' . $returnUrl));
                    } elseif (strpos($returnUrl, '/') === 0) {
                        // Format: /path (like /commands)
                        View::redirect(View::url($returnUrl));
                    } else {
                        View::redirect(View::url('/settings'));
                    }
                } else {
                    View::redirect(View::url('/settings'));
                }
                return;
            }

            Auth::setFlashMessage('error', 'Invalid password.');
            View::redirectBack(View::url('/login.php?action=admin'));
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
                View::redirect(View::url('/?page=' . $pageSlug));
                return;
            }

            Auth::setFlashMessage('error', 'Invalid password.');
            View::redirectBack(View::url('/login.php?page=' . urlencode($pageSlug)));
            return;
        }

        Auth::setFlashMessage('error', 'Invalid login request.');
        View::redirect(View::url('/'));
    }

    /**
     * Handle logout
     */
    public static function logout(): void
    {
        Auth::logout();
        View::redirect(View::url('/'));
    }
}
