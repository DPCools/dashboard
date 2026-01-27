<?php

declare(strict_types=1);

/**
 * Widget Controller - Handles widget CRUD operations
 */
class WidgetController
{
    /**
     * Show create widget form
     */
    public function create(): void
    {
        $pages = Page::all();
        $widgetTypes = WidgetFactory::getAvailableTypes();

        // Reason: Get metadata for each widget type to populate form
        $typesMetadata = [];
        foreach ($widgetTypes as $type) {
            $metadata = WidgetFactory::getTypeMetadata($type);
            if ($metadata !== null) {
                $typesMetadata[$type] = $metadata;
            }
        }

        // Get page_id from query string if provided
        $pageId = isset($_GET['page_id']) ? Security::validateInt($_GET['page_id']) : null;

        View::render('widgets/create', [
            'pages' => $pages,
            'widgetTypes' => $typesMetadata,
            'selectedPageId' => $pageId
        ]);
    }

    /**
     * Store new widget
     */
    public function store(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid CSRF token';
            View::redirect(View::url('/settings'));
            return;
        }

        $pageId = Security::validateInt($_POST['page_id'] ?? 0);
        $widgetType = $_POST['widget_type'] ?? '';
        $title = trim($_POST['title'] ?? '');
        $icon = trim($_POST['icon'] ?? 'box');
        $iconType = trim($_POST['icon_type'] ?? 'lucide');
        $refreshInterval = Security::validateInt($_POST['refresh_interval'] ?? 300, 300);
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;

        // Validation
        $errors = [];

        if ($pageId === 0) {
            $errors[] = 'Please select a page';
        }

        if (empty($widgetType) || !WidgetFactory::isRegistered($widgetType)) {
            $errors[] = 'Invalid widget type';
        }

        if (empty($title)) {
            $errors[] = 'Title is required';
        }

        // Reason: Parse widget-specific settings from POST data
        $settings = $this->parseSettingsFromPost($widgetType);

        // Reason: Validate settings using widget's validation method
        if (empty($errors) && WidgetFactory::isRegistered($widgetType)) {
            $metadata = WidgetFactory::getTypeMetadata($widgetType);
            if ($metadata !== null) {
                $tempWidget = WidgetFactory::create([
                    'id' => 0,
                    'page_id' => $pageId,
                    'widget_type' => $widgetType,
                    'title' => $title,
                    'settings' => json_encode($settings),
                    'refresh_interval' => $refreshInterval,
                    'is_enabled' => $isEnabled
                ]);

                if ($tempWidget !== null) {
                    $validation = $tempWidget->validateSettings($settings);
                    if (!$validation['valid']) {
                        $errors = array_merge($errors, $validation['errors']);
                    }
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            View::redirectBack(View::url('/settings'));
            return;
        }

        // Create widget
        try {
            $widgetId = WidgetModel::create([
                'page_id' => $pageId,
                'widget_type' => $widgetType,
                'title' => $title,
                'icon' => $icon,
                'icon_type' => $iconType,
                'settings' => $settings,
                'refresh_interval' => $refreshInterval,
                'is_enabled' => $isEnabled
            ]);

            // Reason: Get page slug for redirect (URLs use slugs, not IDs)
            $page = Page::find($pageId);
            $pageSlug = $page['slug'] ?? 'main';

            $_SESSION['success'] = 'Widget created successfully';
            View::redirect(View::url('/?page=' . $pageSlug));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to create widget: ' . $e->getMessage();
            View::redirectBack(View::url('/settings'));
        }
    }

    /**
     * Show edit widget form
     */
    public function edit(): void
    {
        $id = Security::validateInt($_GET['id'] ?? 0);
        $widget = WidgetModel::find($id);

        if ($widget === null) {
            View::notFound();
            return;
        }

        $pages = Page::all();
        $metadata = WidgetFactory::getTypeMetadata($widget['widget_type']);

        View::render('widgets/edit', [
            'widget' => $widget,
            'pages' => $pages,
            'metadata' => $metadata
        ]);
    }

    /**
     * Update existing widget
     */
    public function update(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid CSRF token';
            View::redirect(View::url('/settings'));
            return;
        }

        $id = Security::validateInt($_POST['id'] ?? 0);
        $widget = WidgetModel::find($id);

        if ($widget === null) {
            View::notFound();
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $icon = trim($_POST['icon'] ?? 'box');
        $iconType = trim($_POST['icon_type'] ?? 'lucide');
        $refreshInterval = Security::validateInt($_POST['refresh_interval'] ?? 300, 300);
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;

        // Validation
        $errors = [];

        if (empty($title)) {
            $errors[] = 'Title is required';
        }

        // Reason: Parse widget-specific settings from POST data
        $settings = $this->parseSettingsFromPost($widget['widget_type']);

        // Reason: Validate settings using widget's validation method
        $widgetInstance = WidgetFactory::create(array_merge($widget, ['settings' => json_encode($settings)]));
        if ($widgetInstance !== null) {
            $validation = $widgetInstance->validateSettings($settings);
            if (!$validation['valid']) {
                $errors = array_merge($errors, $validation['errors']);
            }
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            View::redirectBack(View::url('/settings'));
            return;
        }

        // Update widget
        try {
            WidgetModel::update($id, [
                'title' => $title,
                'icon' => $icon,
                'icon_type' => $iconType,
                'settings' => $settings,
                'refresh_interval' => $refreshInterval,
                'is_enabled' => $isEnabled
            ]);

            // Reason: Get page slug for redirect (URLs use slugs, not IDs)
            $page = Page::find($widget['page_id']);
            $pageSlug = $page['slug'] ?? 'main';

            $_SESSION['success'] = 'Widget updated successfully';
            View::redirect(View::url('/?page=' . $pageSlug));
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to update widget: ' . $e->getMessage();
            View::redirectBack(View::url('/settings'));
        }
    }

    /**
     * Delete widget
     */
    public function delete(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid CSRF token';
            View::redirect(View::url('/settings'));
            return;
        }

        $id = Security::validateInt($_POST['id'] ?? 0);
        $widget = WidgetModel::find($id);

        if ($widget === null) {
            View::notFound();
            return;
        }

        try {
            WidgetModel::delete($id);
            $_SESSION['success'] = 'Widget deleted successfully';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Failed to delete widget: ' . $e->getMessage();
        }

        View::redirectBack(View::url('/settings'));
    }

    /**
     * AJAX endpoint to fetch widget data
     */
    public function fetchData(): void
    {
        $id = Security::validateInt($_GET['id'] ?? 0);
        $widget = WidgetModel::find($id);

        if ($widget === null) {
            View::json(['success' => false, 'error' => 'Widget not found'], 404);
            return;
        }

        try {
            $widgetInstance = WidgetFactory::create($widget);

            if ($widgetInstance === null) {
                View::json(['success' => false, 'error' => 'Invalid widget type'], 400);
                return;
            }

            $data = $widgetInstance->getData();

            View::json([
                'success' => true,
                'data' => $data,
                'html' => $widgetInstance->render()
            ]);

        } catch (Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Clean expired cache entries
     */
    public function cleanCache(): void
    {
        // Validate CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            View::json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        try {
            $count = WidgetModel::cleanExpiredCache();
            View::json(['success' => true, 'deleted' => $count]);
        } catch (Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reorder widgets via AJAX
     */
    public function reorder(): void
    {
        // Reason: Parse JSON body for AJAX request
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate CSRF token
        if (!Security::validateCSRFToken($input['csrf_token'] ?? null)) {
            View::json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
            return;
        }

        if (!isset($input['order']) || !is_array($input['order'])) {
            View::json(['success' => false, 'message' => 'Invalid order data'], 400);
            return;
        }

        try {
            WidgetModel::reorder($input['order']);
            View::json(['success' => true, 'message' => 'Widget order updated']);
        } catch (Exception $e) {
            View::json(['success' => false, 'message' => 'Failed to update order: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Parse widget-specific settings from POST data
     *
     * @param string $widgetType Widget type
     * @return array Parsed settings
     */
    private function parseSettingsFromPost(string $widgetType): array
    {
        $settings = [];

        // Reason: Extract settings based on widget type
        switch ($widgetType) {
            case 'external_ip':
                $settings['show_location'] = isset($_POST['show_location']);
                $settings['show_isp'] = isset($_POST['show_isp']);
                break;

            case 'weather':
                $settings['location'] = trim($_POST['location'] ?? '');
                $settings['units'] = $_POST['units'] ?? 'metric';
                $settings['show_forecast'] = isset($_POST['show_forecast']);
                break;

            case 'system_stats':
                $settings['show_cpu'] = isset($_POST['show_cpu']);
                $settings['show_memory'] = isset($_POST['show_memory']);
                $settings['show_disk'] = isset($_POST['show_disk']);
                $settings['disk_path'] = trim($_POST['disk_path'] ?? '/');
                break;

            case 'clock':
                $settings['timezone'] = trim($_POST['timezone'] ?? date_default_timezone_get());
                $settings['format'] = $_POST['format'] ?? '24h';
                $settings['show_seconds'] = isset($_POST['show_seconds']);
                break;

            case 'notes':
                $settings['content'] = trim($_POST['content'] ?? '');
                break;

            case 'rss':
                $settings['feed_url'] = trim($_POST['feed_url'] ?? '');
                $settings['item_count'] = Security::validateInt($_POST['item_count'] ?? 5, 5);
                break;

            case 'proxmox':
                $settings['api_url'] = trim($_POST['api_url'] ?? '');
                $settings['username'] = trim($_POST['username'] ?? '');
                $settings['token_id'] = trim($_POST['token_id'] ?? '');
                $settings['token_secret'] = trim($_POST['token_secret'] ?? '');
                $settings['show_cluster_stats'] = isset($_POST['show_cluster_stats']);
                $settings['show_node_details'] = isset($_POST['show_node_details']);
                break;
        }

        return $settings;
    }
}
