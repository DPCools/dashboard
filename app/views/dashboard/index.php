<div class="space-y-8">
    <?php if (Auth::isAdmin()): ?>
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            <?= Security::escape($page['name']) ?>
        </h2>
        <div class="flex space-x-2">
            <a href="<?= View::url('/widgets/create?page_id=' . $page['id']) ?>"
               class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-medium transition-colors">
                <i data-lucide="layout-grid" class="w-4 h-4 mr-2"></i>
                Add Widget
            </a>
            <a href="<?= View::url('/items/create?page_id=' . $page['id']) ?>"
               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Add Item
            </a>
            <a href="<?= View::url('/pages/edit?id=' . $page['id']) ?>"
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-sm font-medium transition-colors">
                <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                Edit Page
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Widgets Section -->
    <?php if (!empty($widgets)): ?>
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
            <span class="h-px flex-1 bg-gray-300 dark:bg-gray-700 mr-4"></span>
            Widgets
            <span class="h-px flex-1 bg-gray-300 dark:bg-gray-700 ml-4"></span>
        </h3>

        <div id="widgets-grid" class="widgets-masonry-grid gap-4">
            <?php foreach ($widgets as $widget): ?>
            <div class="widget-card bg-white dark:bg-gray-800 rounded-lg border-2 border-purple-200 dark:border-purple-700 hover:shadow-lg transition-all break-inside-avoid"
                 data-widget-id="<?= $widget->getId() ?>"
                 data-widget-refresh="<?= $widget->getRefreshInterval() ?>"
                 data-display-order="<?= $widget->getDisplayOrder() ?>">

                <div class="p-6">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                                <?php if ($widget->getIconType() === 'custom'): ?>
                                    <img src="<?= View::url('/public/icons/' . Security::escape($widget->getIcon())) ?>"
                                         alt="<?= Security::escape($widget->getTitle()) ?>"
                                         class="w-6 h-6">
                                <?php else: ?>
                                    <i data-lucide="<?= Security::escape($widget->getIcon()) ?>" class="w-6 h-6 text-purple-600 dark:text-purple-400"></i>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                <?= Security::escape($widget->getTitle()) ?>
                            </h4>
                            <div class="widget-content text-sm text-gray-600 dark:text-gray-400">
                                <?= $widget->render() ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (Auth::isAdmin()): ?>
                <div class="px-6 pb-4 pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <i data-lucide="grip-vertical" class="w-4 h-4 text-gray-400 dark:text-gray-600 widget-drag-handle cursor-move"></i>
                        <span class="text-xs text-gray-400 dark:text-gray-600">Drag to reorder</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="<?= View::url('/widgets/edit?id=' . $widget->getId()) ?>"
                           class="inline-flex items-center px-2 py-1 text-xs font-medium text-purple-600 dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded transition-colors">
                            <i data-lucide="edit-2" class="w-3 h-3 mr-1"></i>
                            Edit
                        </a>
                        <form method="POST" action="<?= View::url('/widgets/delete') ?>"
                              onsubmit="return confirm('Are you sure you want to delete this widget?');"
                              class="inline">
                            <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                            <input type="hidden" name="id" value="<?= $widget->getId() ?>">
                            <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors">
                                <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
    <div class="text-center py-12">
        <i data-lucide="inbox" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4"></i>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No items yet</h3>
        <p class="text-gray-500 dark:text-gray-400 mb-4">Get started by adding your first service link.</p>
        <?php if (Auth::isAdmin()): ?>
        <a href="<?= View::url('/items/create?page_id=' . $page['id']) ?>"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add Your First Item
        </a>
        <?php endif; ?>
    </div>
    <?php else: ?>
        <?php foreach ($items as $category => $categoryItems): ?>
        <div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center">
                <span class="h-px flex-1 bg-gray-300 dark:bg-gray-700 mr-4"></span>
                <?= Security::escape($category) ?>
                <span class="h-px flex-1 bg-gray-300 dark:bg-gray-700 ml-4"></span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?= Security::escape(View::setting('items_per_row', (string) DEFAULT_ITEMS_PER_ROW)) ?> gap-4 <?= Auth::isAdmin() ? 'sortable-grid' : '' ?>" data-category="<?= Security::escape($category) ?>">
                <?php foreach ($categoryItems as $item): ?>
                <?php
                    // Reason: Check if item is password-protected and locked
                    $isPrivate = (int)($item['is_private'] ?? 0) === 1;
                    $isLocked = $isPrivate && !Item::isUnlocked((int)$item['id']);
                ?>
                <div class="group bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg transition-all duration-200 flex flex-col <?= Auth::isAdmin() ? 'sortable-item cursor-move' : '' ?>" data-item-id="<?= $item['id'] ?>">
                    <!-- Clickable card content -->
                    <a href="<?= $isLocked ? '#' : Security::escape($item['url']) ?>"
                       <?= $isLocked ? '' : 'target="_blank" rel="noopener noreferrer"' ?>
                       class="flex-1 p-6 block <?= $isLocked ? 'locked-item cursor-pointer' : '' ?>"
                       <?= $isLocked ? 'data-item-id="' . $item['id'] . '" data-item-title="' . Security::escape($item['title']) . '"' : '' ?>>
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 relative">
                                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 transition-colors <?= $isLocked ? 'relative' : '' ?>">
                                    <?php if (($item['icon_type'] ?? 'lucide') === 'custom'): ?>
                                        <img src="<?= View::url('/public/icons/' . Security::escape($item['icon'])) ?>"
                                             alt="<?= Security::escape($item['title']) ?>"
                                             class="w-6 h-6">
                                    <?php else: ?>
                                        <i data-lucide="<?= Security::escape($item['icon']) ?>" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                                    <?php endif; ?>
                                    <?php if ($isLocked): ?>
                                    <!-- Lock badge (top-left) -->
                                    <div class="absolute -top-1 -left-1 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-800">
                                        <i data-lucide="lock" class="w-3 h-3 text-white"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ((int)($item['status_check'] ?? 0) === 1): ?>
                                <!-- Status indicator badge -->
                                <div class="absolute -top-1 -right-1 w-3 h-3 rounded-full
                                            border-2 border-white dark:border-gray-800
                                            bg-gray-400 animate-pulse status-indicator"
                                     data-item-id="<?= $item['id'] ?>"
                                     title="Checking status...">
                                </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                        <?= Security::escape($item['title']) ?>
                                    </h4>
                                    <i data-lucide="external-link" class="w-4 h-4 text-gray-400 dark:text-gray-500 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors flex-shrink-0 ml-2"></i>
                                </div>
                                <?php if (!empty($item['description'])): ?>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <?= Security::escape($item['description']) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>

                    <?php if (Auth::isAdmin()): ?>
                    <!-- Admin controls -->
                    <div class="px-6 pb-4 pt-2 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="grip-vertical" class="w-4 h-4 text-gray-400 dark:text-gray-600 drag-handle cursor-move"></i>
                            <span class="text-xs text-gray-400 dark:text-gray-600">Drag to reorder</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="<?= View::url('/items/edit?id=' . $item['id']) ?>"
                               class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors">
                                <i data-lucide="edit-2" class="w-3 h-3 mr-1"></i>
                                Edit
                            </a>
                            <form method="POST" action="<?= View::url('/items/delete') ?>"
                                  onsubmit="return confirm('Are you sure you want to delete this item?');"
                                  class="inline">
                                <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                <button type="submit" class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors">
                                    <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
