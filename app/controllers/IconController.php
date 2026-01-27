<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/Auth.php';
require_once BASE_PATH . '/app/helpers/View.php';
require_once BASE_PATH . '/app/helpers/Security.php';
require_once BASE_PATH . '/app/helpers/IconHelper.php';
require_once BASE_PATH . '/app/models/Icon.php';

/**
 * Icon Controller - Manages custom icon uploads and administration
 */
class IconController
{
    /**
     * Display icon library page
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $customIcons = Icon::all();
        $lucideIcons = IconHelper::getLucideIcons();

        View::render('icons/index', [
            'title' => 'Icon Library',
            'custom_icons' => $customIcons,
            'lucide_count' => count($lucideIcons)
        ]);
    }

    /**
     * Display icon upload form
     */
    public function upload(): void
    {
        Auth::requireAdmin();

        View::render('icons/upload', [
            'title' => 'Upload Icon'
        ]);
    }

    /**
     * Handle icon upload submission
     */
    public function store(): void
    {
        Auth::requireAdmin();

        // Reason: Check if this is an AJAX request for JSON response
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Verify CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            if ($isAjax) {
                View::json(['success' => false, 'message' => 'Invalid security token']);
                return;
            }
            $_SESSION['error'] = 'Invalid security token';
            View::redirect('/icons/upload');
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['icon']) || $_FILES['icon']['error'] === UPLOAD_ERR_NO_FILE) {
            if ($isAjax) {
                View::json(['success' => false, 'message' => 'Please select a file to upload']);
                return;
            }
            $_SESSION['error'] = 'Please select a file to upload';
            View::redirect('/icons/upload');
            return;
        }

        // Get display name
        $displayName = trim($_POST['display_name'] ?? '');
        if (empty($displayName)) {
            // Auto-generate from filename
            $displayName = ucwords(str_replace(
                ['-', '_'],
                ' ',
                pathinfo($_FILES['icon']['name'], PATHINFO_FILENAME)
            ));
        }

        // Reason: Check if icon with same filename already exists in database
        $sanitizedFilename = IconHelper::sanitizeFilename($_FILES['icon']['name']);
        $existingIcon = Icon::findByFilename($sanitizedFilename);
        if ($existingIcon !== null) {
            if ($isAjax) {
                View::json(['success' => false, 'message' => 'An icon with this name already exists: ' . $existingIcon['display_name']]);
                return;
            }
            $_SESSION['error'] = 'An icon with this name already exists: ' . $existingIcon['display_name'];
            View::redirect('/icons/upload');
            return;
        }

        // Save the icon
        $result = IconHelper::saveIcon($_FILES['icon']);

        if (!$result['success']) {
            if ($isAjax) {
                View::json(['success' => false, 'message' => $result['error']]);
                return;
            }
            $_SESSION['error'] = $result['error'];
            View::redirect('/icons/upload');
            return;
        }

        // Register in database
        try {
            $iconId = Icon::create([
                'filename' => $result['filename'],
                'display_name' => $displayName,
                'file_size' => $_FILES['icon']['size'],
                'uploaded_by' => $_SESSION['username'] ?? 'admin'
            ]);

            if ($isAjax) {
                View::json([
                    'success' => true,
                    'message' => 'Icon uploaded successfully: ' . $displayName,
                    'icon_id' => $iconId,
                    'filename' => $result['filename']
                ]);
                return;
            }

            $_SESSION['success'] = 'Icon uploaded successfully: ' . $displayName;
            View::redirect('/icons');
        } catch (Exception $e) {
            // Reason: Check if this is a unique constraint violation
            $errorMessage = 'Database error: ' . $e->getMessage();
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false ||
                strpos($e->getMessage(), 'duplicate') !== false) {
                $errorMessage = 'An icon with this filename already exists in the database';
            }

            if ($isAjax) {
                View::json(['success' => false, 'message' => $errorMessage]);
                return;
            }
            $_SESSION['error'] = $errorMessage;
            View::redirect('/icons/upload');
        }
    }

    /**
     * Handle icon deletion
     */
    public function delete(): void
    {
        Auth::requireAdmin();

        // Verify CSRF token
        if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid security token']);
            return;
        }

        $iconId = (int) ($_POST['icon_id'] ?? 0);

        if ($iconId === 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Invalid icon ID']);
            return;
        }

        // Find the icon
        $icon = Icon::find($iconId);
        if ($icon === null) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Icon not found']);
            return;
        }

        // Check if icon is in use
        $checkResult = IconHelper::canDeleteIcon($icon['filename']);

        if (!$checkResult['can_delete']) {
            $itemCount = count($checkResult['used_by_items']);
            $pageCount = count($checkResult['used_by_pages']);

            $message = 'Cannot delete icon. It is currently in use by ';
            $parts = [];

            if ($itemCount > 0) {
                $parts[] = $itemCount . ' item' . ($itemCount > 1 ? 's' : '');
            }
            if ($pageCount > 0) {
                $parts[] = $pageCount . ' page' . ($pageCount > 1 ? 's' : '');
            }

            $message .= implode(' and ', $parts);

            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $message]);
            return;
        }

        // Delete from filesystem
        if (!IconHelper::deleteIcon($icon['filename'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Failed to delete icon file']);
            return;
        }

        // Delete from database
        Icon::delete($iconId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Icon deleted successfully']);
    }

    /**
     * Scan filesystem for manually uploaded icons
     */
    public function scan(): void
    {
        Auth::requireAdmin();

        // Verify CSRF token
        if (!Security::validateCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token';
            View::redirect('/icons');
            return;
        }

        $result = IconHelper::syncWithFilesystem();

        if ($result['added'] > 0) {
            $_SESSION['success'] = sprintf(
                'Scan complete: %d new icon%s added, %d skipped',
                $result['added'],
                $result['added'] === 1 ? '' : 's',
                $result['skipped']
            );
        } else {
            $_SESSION['info'] = 'No new icons found';
        }

        View::redirect('/icons');
    }

    /**
     * Get icons as JSON for AJAX requests
     */
    public function getIcons(): void
    {
        Auth::requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 100; // Load 100 icons at a time
        $offset = ($page - 1) * $perPage;

        // If searching, query database directly
        if ($search !== '') {
            $searchResults = Database::fetchAll(
                "SELECT * FROM icons
                 WHERE display_name LIKE ? OR filename LIKE ?
                 ORDER BY display_name ASC
                 LIMIT ? OFFSET ?",
                ['%' . $search . '%', '%' . $search . '%', $perPage, $offset]
            );

            // Get total count for pagination
            $totalResult = Database::fetchOne(
                "SELECT COUNT(*) as count FROM icons
                 WHERE display_name LIKE ? OR filename LIKE ?",
                ['%' . $search . '%', '%' . $search . '%']
            );
            $totalIcons = (int) ($totalResult['count'] ?? 0);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'icons' => [
                    'lucide' => [], // Don't return Lucide icons when searching custom
                    'custom' => $searchResults
                ],
                'search' => $search,
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $totalIcons,
                    'total_pages' => ceil($totalIcons / $perPage),
                    'has_more' => ($offset + $perPage) < $totalIcons
                ]
            ]);
            return;
        }

        // Normal pagination without search
        $allCustomIcons = Icon::all();
        $totalIcons = count($allCustomIcons);
        $customIcons = array_slice($allCustomIcons, $offset, $perPage);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'icons' => [
                'lucide' => IconHelper::getLucideIcons(),
                'custom' => $customIcons
            ],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalIcons,
                'total_pages' => ceil($totalIcons / $perPage),
                'has_more' => ($offset + $perPage) < $totalIcons
            ]
        ]);
    }
}
