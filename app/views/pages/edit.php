<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Page</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update page settings and configuration.</p>
    </div>

    <form method="POST" action="<?= View::url('/pages/update') ?>" class="space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
        <input type="hidden" name="id" value="<?= $page['id'] ?>">

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Page Name *
            </label>
            <input type="text"
                   name="name"
                   id="name"
                   value="<?= Security::escape($page['name']) ?>"
                   required
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Slug *
            </label>
            <input type="text"
                   name="slug"
                   id="slug"
                   value="<?= Security::escape($page['slug']) ?>"
                   required
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Icon
            </label>
            <input type="hidden" name="icon" value="<?= Security::escape($page['icon']) ?>">
            <input type="hidden" name="icon_type" value="<?= Security::escape($page['icon_type'] ?? 'lucide') ?>">
            <div class="flex items-center gap-3">
                <div id="icon-preview" class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <?php if (($page['icon_type'] ?? 'lucide') === 'custom'): ?>
                        <img src="<?= View::url('/public/icons/' . Security::escape($page['icon'])) ?>"
                             alt="Icon" class="w-6 h-6">
                    <?php else: ?>
                        <i data-lucide="<?= Security::escape($page['icon']) ?>" class="w-6 h-6 text-gray-600 dark:text-gray-300"></i>
                    <?php endif; ?>
                </div>
                <button type="button"
                        onclick="openIconPicker(function(icon, type) { setFormIcon(icon, type, 'icon-preview'); })"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                    Choose Icon
                </button>
            </div>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Select from Lucide icons or upload custom SVG icons
            </p>
        </div>

        <div>
            <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Display Order
            </label>
            <input type="number"
                   name="display_order"
                   id="display_order"
                   value="<?= Security::escape((string) $page['display_order']) ?>"
                   min="0"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lower numbers appear first in the navigation.</p>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <div class="flex items-start">
                <div class="flex items-center h-5">
                    <input type="checkbox"
                           name="is_private"
                           id="is_private"
                           <?= (int) $page['is_private'] === 1 ? 'checked' : '' ?>
                           class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500"
                           onchange="document.getElementById('password-field').classList.toggle('hidden', !this.checked)">
                </div>
                <div class="ml-3">
                    <label for="is_private" class="font-medium text-gray-700 dark:text-gray-300">
                        Private Page
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Require a password to access this page.</p>
                </div>
            </div>

            <div id="password-field" class="mt-4 <?= (int) $page['is_private'] === 0 ? 'hidden' : '' ?>">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    New Password
                </label>
                <input type="password"
                       name="password"
                       id="password"
                       class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Leave blank to keep existing password or use the global admin password.
                </p>
            </div>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= View::url('/settings') ?>"
               class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors cursor-pointer">
                Update Page
            </button>
        </div>
    </form>
</div>
