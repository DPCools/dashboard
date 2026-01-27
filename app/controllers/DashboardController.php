<?php

declare(strict_types=1);

class DashboardController
{
    /**
     * Display the main dashboard
     */
    public static function index(): void
    {
        // Get the requested page slug from query parameter
        $slug = $_GET['page'] ?? 'main';

        // Find the page
        $page = Page::findBySlug($slug);

        if ($page === null) {
            View::notFound();
            return;
        }

        $pageId = (int) $page['id'];
        $isPrivate = (int) $page['is_private'] === 1;

        // Reason: Check if the page requires authentication
        if ($isPrivate && !Auth::isPageUnlocked($pageId)) {
            Auth::redirectToLogin($slug);
            return;
        }

        // Get items for this page, grouped by category
        $items = Item::getByPage($pageId);

        // Reason: Load widgets for this page
        $widgets = [];
        if (class_exists('WidgetModel') && class_exists('WidgetFactory')) {
            $widgetData = WidgetModel::getByPage($pageId);
            foreach ($widgetData as $data) {
                $instance = WidgetFactory::create($data);
                if ($instance !== null && $instance->isEnabled()) {
                    $widgets[] = $instance;
                }
            }
        }

        // Render the dashboard view
        View::render('dashboard/index', [
            'page' => $page,
            'items' => $items,
            'widgets' => $widgets
        ]);
    }
}
