<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h2>
        <a href="<?= View::url('/') ?>"
           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Dashboard
        </a>
    </div>

    <!-- Pages Management -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pages</h3>
            <a href="<?= View::url('/pages/create') ?>"
               class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                New Page
            </a>
        </div>

        <?php if (empty($pages)): ?>
        <div class="px-6 py-8 text-center">
            <i data-lucide="inbox" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3"></i>
            <p class="text-gray-500 dark:text-gray-400">No pages found.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Slug
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Icon
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Privacy
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Items
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Order
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($pages as $page): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <?php if (($page['icon_type'] ?? 'lucide') === 'custom'): ?>
                                    <img src="<?= View::url('/public/icons/' . Security::escape($page['icon'])) ?>"
                                         alt="<?= Security::escape($page['name']) ?>"
                                         class="w-5 h-5 mr-3">
                                <?php else: ?>
                                    <i data-lucide="<?= Security::escape($page['icon']) ?>" class="w-5 h-5 text-gray-400 dark:text-gray-500 mr-3"></i>
                                <?php endif; ?>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    <?= Security::escape($page['name']) ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-500 dark:text-gray-400 font-mono">
                                <?= Security::escape($page['slug']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded">
                                <?= Security::escape($page['icon']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ((int) $page['is_private'] === 1): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">
                                    <i data-lucide="lock" class="w-3 h-3 mr-1"></i>
                                    Private
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200">
                                    <i data-lucide="globe" class="w-3 h-3 mr-1"></i>
                                    Public
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?= Page::getItemCount((int) $page['id']) ?> items
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?= Security::escape((string) $page['display_order']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="<?= View::url('/?page=' . Security::escape($page['slug'])) ?>"
                               class="text-blue-600 dark:text-blue-400 hover:underline">View</a>
                            <a href="<?= View::url('/pages/edit?id=' . $page['id']) ?>"
                               class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</a>
                            <?php if (count($pages) > 1): ?>
                            <form method="POST" action="<?= View::url('/pages/delete') ?>"
                                  onsubmit="return confirm('Are you sure? This will delete all items in this page.');"
                                  class="inline">
                                <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                                <input type="hidden" name="id" value="<?= $page['id'] ?>">
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Icon Management -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Custom Icons</h3>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        Upload custom SVG icons or use the built-in Lucide icon library.
                    </p>
                    <?php
                    require_once BASE_PATH . '/app/models/Icon.php';
                    $iconCount = Icon::count();
                    ?>
                    <p class="text-sm text-gray-500 dark:text-gray-500">
                        <?= $iconCount ?> custom icon<?= $iconCount === 1 ? '' : 's' ?> uploaded
                    </p>
                </div>
                <a href="<?= View::url('/icons') ?>"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <i data-lucide="image" class="w-4 h-4 mr-2"></i>
                    Manage Icons
                </a>
            </div>
        </div>
    </div>

    <!-- Admin Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Global Settings</h3>
        </div>
        <div class="px-6 py-4">
            <dl class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Site Title</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= Security::escape(View::setting('site_title', DEFAULT_SITE_TITLE)) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Theme</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white capitalize"><?= Security::escape(View::setting('theme', DEFAULT_THEME)) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Items Per Row</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white"><?= Security::escape(View::setting('items_per_row', (string) DEFAULT_ITEMS_PER_ROW)) ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Admin Password</dt>
                    <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Configured (default: admin123)
                        <p class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">
                            <i data-lucide="alert-triangle" class="w-3 h-3 inline"></i>
                            To change the admin password, update the database directly using SQL.
                        </p>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
