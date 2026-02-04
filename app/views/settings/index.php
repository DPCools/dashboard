<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Settings</h2>
        <div class="flex space-x-3">
            <a href="<?= View::url('/files') ?>"
               class="inline-flex items-center px-4 py-2 border border-purple-300 dark:border-purple-600 rounded-md text-sm font-medium text-purple-700 dark:text-purple-300 bg-white dark:bg-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                <i data-lucide="share-2" class="w-4 h-4 mr-2"></i>
                File Sharing
            </a>
            <a href="<?= View::url('/commands') ?>"
               class="inline-flex items-center px-4 py-2 border border-purple-300 dark:border-purple-600 rounded-md text-sm font-medium text-purple-700 dark:text-purple-300 bg-white dark:bg-gray-700 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-colors">
                <i data-lucide="terminal" class="w-4 h-4 mr-2"></i>
                Remote Commands
            </a>
            <a href="<?= View::url('/') ?>"
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <div class="flex">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                <p class="ml-3 text-sm text-green-700 dark:text-green-300">
                    <?= Security::escape($_SESSION['success']) ?>
                </p>
            </div>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                <p class="ml-3 text-sm text-red-700 dark:text-red-300">
                    <?= Security::escape($_SESSION['error']) ?>
                </p>
            </div>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

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

    <!-- Remote Commands -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Remote Commands</h3>
        </div>
        <div class="px-6 py-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        Execute pre-defined commands on remote Proxmox nodes and Docker hosts from a centralized dashboard.
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">
                        Securely manage your infrastructure with whitelisted command templates and comprehensive audit logging.
                    </p>
                </div>
                <a href="<?= View::url('/commands') ?>"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <i data-lucide="terminal" class="w-4 h-4 mr-2"></i>
                    Manage Commands
                </a>
            </div>
        </div>
    </div>

    <!-- Global Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Global Settings</h3>
        </div>
        <div class="px-6 py-6">
            <form method="POST" action="<?= View::url('/settings/update') ?>" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">

                <!-- Site Title -->
                <div>
                    <label for="site_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Site Title
                    </label>
                    <input type="text"
                           name="site_title"
                           id="site_title"
                           value="<?= Security::escape(View::setting('site_title', DEFAULT_SITE_TITLE)) ?>"
                           class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        This appears in the browser tab and page header
                    </p>
                </div>

                <!-- Favicon -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Favicon (Site Icon)
                    </label>
                    <input type="hidden" name="favicon" value="<?= Security::escape(View::setting('favicon', '')) ?>">
                    <input type="hidden" name="favicon_type" value="<?= Security::escape(View::setting('favicon_type', 'lucide')) ?>">
                    <div class="flex items-center gap-3">
                        <div id="favicon-preview" class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <?php
                            $favicon = View::setting('favicon', '');
                            $faviconType = View::setting('favicon_type', 'lucide');
                            if (!empty($favicon)):
                                if ($faviconType === 'custom'):
                            ?>
                                <img src="<?= View::url('/public/icons/' . Security::escape($favicon)) ?>" alt="Favicon" class="w-6 h-6">
                            <?php else: ?>
                                <i data-lucide="<?= Security::escape($favicon) ?>" class="w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                            <?php endif; ?>
                            <?php else: ?>
                                <i data-lucide="home" class="w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                            <?php endif; ?>
                        </div>
                        <button type="button"
                                onclick="openIconPicker(function(icon, type) { setFormIcon(icon, type, 'favicon-preview'); document.querySelector('input[name=favicon]').value = icon; document.querySelector('input[name=favicon_type]').value = type; })"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                            Choose Icon
                        </button>
                        <?php if (!empty($favicon)): ?>
                        <button type="button"
                                onclick="document.querySelector('input[name=favicon]').value = ''; document.querySelector('input[name=favicon_type]').value = 'lucide'; document.getElementById('favicon-preview').innerHTML = '<i data-lucide=\'home\' class=\'w-6 h-6 text-gray-600 dark:text-gray-300\'></i>'; lucide.createIcons();"
                                class="inline-flex items-center px-3 py-2 border border-red-300 dark:border-red-600 rounded-md text-sm font-medium text-red-700 dark:text-red-300 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <i data-lucide="x" class="w-4 h-4 mr-1"></i>
                            Remove
                        </button>
                        <?php endif; ?>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        This icon appears in browser tabs and bookmarks
                    </p>
                </div>

                <!-- Theme -->
                <div>
                    <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Theme
                    </label>
                    <select name="theme"
                            id="theme"
                            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
                        <?php
                        $currentTheme = View::setting('theme', DEFAULT_THEME);
                        $themes = [
                            'system' => 'System (Auto)',
                            'light' => 'Light',
                            'dark' => 'Dark'
                        ];
                        foreach ($themes as $value => $label):
                        ?>
                        <option value="<?= $value ?>" <?= $currentTheme === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        System follows your browser/OS preference
                    </p>
                </div>

                <!-- Items Per Row -->
                <div>
                    <label for="items_per_row" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Items Per Row
                    </label>
                    <select name="items_per_row"
                            id="items_per_row"
                            class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
                        <?php
                        $currentItemsPerRow = (int) View::setting('items_per_row', (string) DEFAULT_ITEMS_PER_ROW);
                        for ($i = 2; $i <= 6; $i++):
                        ?>
                        <option value="<?= $i ?>" <?= $currentItemsPerRow === $i ? 'selected' : '' ?>>
                            <?= $i ?> items per row
                        </option>
                        <?php endfor; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Number of service items displayed horizontally
                    </p>
                </div>

                <!-- Save Button -->
                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                        <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                        Save Settings
                    </button>
                </div>
            </form>

            <!-- Admin Password Info -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Admin Password</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    To change the admin password, use the command line:
                </p>
                <code class="block mt-2 bg-gray-100 dark:bg-gray-900 text-sm p-3 rounded border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200">
                    sqlite3 data/dashboard.db "UPDATE settings SET value='new_hash_here' WHERE key='admin_password_hash';"
                </code>
                <p class="mt-2 text-xs text-yellow-600 dark:text-yellow-400">
                    <i data-lucide="alert-triangle" class="w-3 h-3 inline"></i>
                    Use a proper password hashing tool or update via a future password change feature
                </p>
            </div>
        </div>
    </div>
</div>
