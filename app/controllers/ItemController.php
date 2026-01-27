<?php

declare(strict_types=1);

class ItemController
{
    /**
     * Show the create item form
     */
    public function create(): void
    {
        $pageId = Security::validateInt($_GET['page_id'] ?? 0);
        $page = Page::find($pageId);

        if ($page === null) {
            View::notFound();
            return;
        }

        $pages = Page::all();
        $categories = Item::getCategories();

        View::render('items/create', [
            'page' => $page,
            'pages' => $pages,
            'categories' => $categories
        ]);
    }

    /**
     * Store a new item
     */
    public function store(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            Auth::setFlashMessage('error', 'Invalid CSRF token. Please try again.');
            View::redirectBack();
            return;
        }

        // Validate required fields
        $pageId = Security::validateInt($_POST['page_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');

        if ($pageId === 0 || $title === '' || $url === '') {
            Auth::setFlashMessage('error', 'Page, title, and URL are required.');
            View::redirectBack();
            return;
        }

        // Validate URL format
        if (!Security::validateUrl($url)) {
            Auth::setFlashMessage('error', 'Invalid URL format.');
            View::redirectBack();
            return;
        }

        // Check if page exists
        $page = Page::find($pageId);
        if ($page === null) {
            Auth::setFlashMessage('error', 'Invalid page.');
            View::redirectBack();
            return;
        }

        // Prepare data
        $data = [
            'page_id' => $pageId,
            'title' => $title,
            'url' => $url,
            'icon' => trim($_POST['icon'] ?? 'link'),
            'icon_type' => trim($_POST['icon_type'] ?? 'lucide'),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'status_check' => isset($_POST['status_check']) ? 1 : 0,
            'ssl_verify' => isset($_POST['ssl_verify']) ? 1 : 0, // Reason: Checkbox only sent when checked
            'is_private' => isset($_POST['is_private']) ? 1 : 0,
            'password' => trim($_POST['password'] ?? '')
        ];

        try {
            $itemId = Item::create($data);
            Auth::setFlashMessage('success', 'Item created successfully.');
            View::redirect(BASE_URL . '/?page=' . $page['slug']);
        } catch (Exception $e) {
            error_log('Error creating item: ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Failed to create item. Please try again.');
            View::redirectBack();
        }
    }

    /**
     * Show the edit item form
     */
    public function edit(): void
    {
        $id = Security::validateInt($_GET['id'] ?? 0);
        $item = Item::find($id);

        if ($item === null) {
            View::notFound();
            return;
        }

        $page = Page::find((int) $item['page_id']);
        if ($page === null) {
            View::notFound();
            return;
        }

        $pages = Page::all();
        $categories = Item::getCategories();

        View::render('items/edit', [
            'item' => $item,
            'page' => $page,
            'pages' => $pages,
            'categories' => $categories
        ]);
    }

    /**
     * Update an existing item
     */
    public function update(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            Auth::setFlashMessage('error', 'Invalid CSRF token. Please try again.');
            View::redirectBack();
            return;
        }

        $id = Security::validateInt($_POST['id'] ?? 0);
        $item = Item::find($id);

        if ($item === null) {
            View::notFound();
            return;
        }

        // Validate required fields
        $pageId = Security::validateInt($_POST['page_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['url'] ?? '');

        if ($pageId === 0 || $title === '' || $url === '') {
            Auth::setFlashMessage('error', 'Page, title, and URL are required.');
            View::redirectBack();
            return;
        }

        // Validate URL format
        if (!Security::validateUrl($url)) {
            Auth::setFlashMessage('error', 'Invalid URL format.');
            View::redirectBack();
            return;
        }

        // Check if page exists
        $page = Page::find($pageId);
        if ($page === null) {
            Auth::setFlashMessage('error', 'Invalid page.');
            View::redirectBack();
            return;
        }

        // Prepare data
        $data = [
            'page_id' => $pageId,
            'title' => $title,
            'url' => $url,
            'icon' => trim($_POST['icon'] ?? 'link'),
            'icon_type' => trim($_POST['icon_type'] ?? 'lucide'),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'display_order' => Security::validateInt($_POST['display_order'] ?? 0, (int) $item['display_order']),
            'status_check' => isset($_POST['status_check']) ? 1 : 0,
            'ssl_verify' => isset($_POST['ssl_verify']) ? 1 : 0,
            'is_private' => isset($_POST['is_private']) ? 1 : 0,
            'password' => trim($_POST['password'] ?? '')
        ];

        try {
            Item::update($id, $data);
            Auth::setFlashMessage('success', 'Item updated successfully.');
            View::redirect(BASE_URL . '/?page=' . $page['slug']);
        } catch (Exception $e) {
            error_log('Error updating item: ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Failed to update item. Please try again.');
            View::redirectBack();
        }
    }

    /**
     * Delete an item
     */
    public function delete(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            Auth::setFlashMessage('error', 'Invalid CSRF token. Please try again.');
            View::redirectBack();
            return;
        }

        $id = Security::validateInt($_POST['id'] ?? 0);
        $item = Item::find($id);

        if ($item === null) {
            View::notFound();
            return;
        }

        // Get page for redirect
        $page = Page::find((int) $item['page_id']);

        try {
            Item::delete($id);
            Auth::setFlashMessage('success', 'Item deleted successfully.');

            if ($page !== null) {
                View::redirect(BASE_URL . '/?page=' . $page['slug']);
            } else {
                View::redirect(BASE_URL);
            }
        } catch (Exception $e) {
            error_log('Error deleting item: ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Failed to delete item. Please try again.');
            View::redirectBack();
        }
    }

    /**
     * Reorder items via AJAX
     */
    public function reorder(): void
    {
        // Reason: Handle JSON input for AJAX requests
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate CSRF token
        if (!Security::validateCSRFToken($input['csrf_token'] ?? null)) {
            View::json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        $order = $input['order'] ?? [];

        if (empty($order) || !is_array($order)) {
            View::json(['success' => false, 'message' => 'Invalid order data'], 400);
            return;
        }

        try {
            // Reason: Convert order array to integers for security
            $itemIds = array_map('intval', $order);
            Item::reorder($itemIds);

            View::json(['success' => true, 'message' => 'Order updated successfully']);
        } catch (Exception $e) {
            error_log('Error reordering items: ' . $e->getMessage());
            View::json(['success' => false, 'message' => 'Failed to update order'], 500);
        }
    }

    /**
     * Check service status for items on a page
     */
    public function checkStatus(): void
    {
        // Accept page_slug parameter
        $pageSlug = trim($_GET['page_slug'] ?? 'main');

        // Get page by slug
        $page = Page::findBySlug($pageSlug);
        if ($page === null) {
            View::json(['success' => false, 'error' => 'Page not found'], 404);
            return;
        }

        try {
            // Get all items for page with status_check=1
            $items = Database::fetchAll(
                'SELECT id, title, url, ssl_verify FROM items
                 WHERE page_id = ? AND status_check = 1
                 ORDER BY display_order ASC',
                [(int) $page['id']]
            );

            // If no items to check, return empty results
            if (empty($items)) {
                View::json([
                    'success' => true,
                    'statuses' => [],
                    'cached' => false,
                    'timestamp' => time()
                ]);
                return;
            }

            // Check URLs in parallel using StatusChecker
            require_once __DIR__ . '/../helpers/StatusChecker.php';
            $results = StatusChecker::checkBatch($items);

            // Return JSON response
            View::json([
                'success' => true,
                'statuses' => $results,
                'cached' => false,
                'timestamp' => time()
            ]);
        } catch (Exception $e) {
            error_log('Error checking service status: ' . $e->getMessage());
            View::json([
                'success' => false,
                'error' => 'Failed to check service status'
            ], 500);
        }
    }

    /**
     * Verify password for a password-protected item via AJAX
     */
    public function verifyPassword(): void
    {
        // Reason: Handle JSON input for AJAX requests
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate CSRF token
        if (!Security::validateCSRFToken($input['csrf_token'] ?? null)) {
            View::json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        $itemId = Security::validateInt($input['item_id'] ?? 0);
        $password = trim($input['password'] ?? '');

        if ($itemId === 0 || $password === '') {
            View::json(['success' => false, 'message' => 'Item ID and password are required'], 400);
            return;
        }

        // Verify password
        if (Item::verifyPassword($itemId, $password)) {
            // Unlock item in session
            Item::unlock($itemId);

            View::json([
                'success' => true,
                'message' => 'Password verified successfully'
            ]);
        } else {
            View::json([
                'success' => false,
                'message' => 'Incorrect password'
            ], 401);
        }
    }
}
