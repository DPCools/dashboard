<?php

declare(strict_types=1);

class PageController
{
    /**
     * Display the settings page with page management
     */
    public function settings(): void
    {
        $pages = Page::all();

        View::render('settings/index', [
            'pages' => $pages
        ]);
    }

    /**
     * Show the create page form
     */
    public function create(): void
    {
        View::render('pages/create');
    }

    /**
     * Store a new page
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
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Auth::setFlashMessage('error', 'Page name is required.');
            View::redirectBack();
            return;
        }

        // Prepare data
        $data = [
            'name' => $name,
            'slug' => trim($_POST['slug'] ?? ''),
            'is_private' => isset($_POST['is_private']) ? 1 : 0,
            'password' => $_POST['password'] ?? '',
            'icon' => trim($_POST['icon'] ?? 'layout-dashboard'),
            'icon_type' => trim($_POST['icon_type'] ?? 'lucide')
        ];

        try {
            $pageId = Page::create($data);
            Auth::setFlashMessage('success', 'Page created successfully.');
            View::redirect(BASE_URL . '/settings');
        } catch (Exception $e) {
            error_log('Error creating page: ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Failed to create page. Please try again.');
            View::redirectBack();
        }
    }

    /**
     * Show the edit page form
     */
    public function edit(): void
    {
        $id = Security::validateInt($_GET['id'] ?? 0);
        $page = Page::find($id);

        if ($page === null) {
            View::notFound();
            return;
        }

        View::render('pages/edit', [
            'page' => $page
        ]);
    }

    /**
     * Update an existing page
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
        $page = Page::find($id);

        if ($page === null) {
            View::notFound();
            return;
        }

        // Validate required fields
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Auth::setFlashMessage('error', 'Page name is required.');
            View::redirectBack();
            return;
        }

        // Prepare data
        $data = [
            'name' => $name,
            'slug' => trim($_POST['slug'] ?? ''),
            'is_private' => isset($_POST['is_private']) ? 1 : 0,
            'icon' => trim($_POST['icon'] ?? 'layout-dashboard'),
            'icon_type' => trim($_POST['icon_type'] ?? 'lucide'),
            'display_order' => Security::validateInt($_POST['display_order'] ?? 0, (int) $page['display_order'])
        ];

        // Only update password if a new one is provided
        if (!empty($_POST['password'])) {
            $data['password'] = $_POST['password'];
        }

        try {
            Page::update($id, $data);
            Auth::setFlashMessage('success', 'Page updated successfully.');
            View::redirect(BASE_URL . '/settings');
        } catch (Exception $e) {
            error_log('Error updating page: ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Failed to update page. Please try again.');
            View::redirectBack();
        }
    }

    /**
     * Delete a page
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
        $page = Page::find($id);

        if ($page === null) {
            View::notFound();
            return;
        }

        // Reason: Prevent deletion of the last page
        $totalPages = count(Page::all());
        if ($totalPages <= 1) {
            Auth::setFlashMessage('error', 'Cannot delete the last page.');
            View::redirectBack();
            return;
        }

        try {
            Page::delete($id);
            Auth::setFlashMessage('success', 'Page deleted successfully.');
            View::redirect(BASE_URL . '/settings');
        } catch (Exception $e) {
            error_log('Error deleting page: ' . $e->getMessage());
            Auth::setFlashMessage('error', 'Failed to delete page. Please try again.');
            View::redirectBack();
        }
    }
}
