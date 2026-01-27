<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Item</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update item details and configuration.</p>
    </div>

    <form method="POST" action="<?= View::url('/items/update') ?>" class="space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
        <input type="hidden" name="id" value="<?= $item['id'] ?>">

        <div>
            <label for="page_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Page *
            </label>
            <select name="page_id"
                    id="page_id"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
                <?php foreach ($pages as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (int) $p['id'] === (int) $item['page_id'] ? 'selected' : '' ?>>
                    <?= Security::escape($p['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Title *
            </label>
            <input type="text"
                   name="title"
                   id="title"
                   value="<?= Security::escape($item['title']) ?>"
                   required
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
        </div>

        <div>
            <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                URL *
            </label>
            <input type="url"
                   name="url"
                   id="url"
                   value="<?= Security::escape($item['url']) ?>"
                   required
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Icon
            </label>
            <input type="hidden" name="icon" value="<?= Security::escape($item['icon']) ?>">
            <input type="hidden" name="icon_type" value="<?= Security::escape($item['icon_type'] ?? 'lucide') ?>">
            <div class="flex items-center gap-3">
                <div id="icon-preview" class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <?php if (($item['icon_type'] ?? 'lucide') === 'custom'): ?>
                        <img src="<?= View::url('/public/icons/' . Security::escape($item['icon'])) ?>"
                             alt="Icon" class="w-6 h-6">
                    <?php else: ?>
                        <i data-lucide="<?= Security::escape($item['icon']) ?>" class="w-6 h-6 text-gray-600 dark:text-gray-300"></i>
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
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description
            </label>
            <textarea name="description"
                      id="description"
                      rows="3"
                      class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2"><?= Security::escape($item['description'] ?? '') ?></textarea>
        </div>

        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input type="checkbox"
                       id="status_check"
                       name="status_check"
                       value="1"
                       <?= (int)($item['status_check'] ?? 0) === 1 ? 'checked' : '' ?>
                       class="h-4 w-4 text-blue-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500">
            </div>
            <div class="ml-3">
                <label for="status_check" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Enable status monitoring
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Periodically check if this service is online and display a status indicator
                </p>
            </div>
        </div>

        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input type="checkbox"
                       id="ssl_verify"
                       name="ssl_verify"
                       value="1"
                       <?= (int)($item['ssl_verify'] ?? 0) === 1 ? 'checked' : '' ?>
                       class="h-4 w-4 text-blue-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500">
            </div>
            <div class="ml-3">
                <label for="ssl_verify" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Verify SSL certificate
                </label>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Uncheck to skip SSL verification for self-signed certificates (less secure)
                </p>
            </div>
        </div>

        <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
            <div class="flex items-start mb-3">
                <div class="flex items-center h-5">
                    <input type="checkbox"
                           id="is_private"
                           name="is_private"
                           value="1"
                           <?= (int)($item['is_private'] ?? 0) === 1 ? 'checked' : '' ?>
                           onchange="togglePasswordField()"
                           class="h-4 w-4 text-blue-600 bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded focus:ring-blue-500">
                </div>
                <div class="ml-3">
                    <label for="is_private" class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                        <i data-lucide="lock" class="w-4 h-4 mr-1 text-red-500"></i>
                        Password protect this item
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Require a password to access this service link
                    </p>
                </div>
            </div>

            <div id="password-field" class="<?= (int)($item['is_private'] ?? 0) === 1 ? '' : 'hidden' ?> mt-3 pt-3 border-t border-gray-300 dark:border-gray-600">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Password <?= (int)($item['is_private'] ?? 0) === 1 ? '(leave blank to keep current)' : '' ?>
                </label>
                <input type="password"
                       name="password"
                       id="password"
                       class="block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2"
                       placeholder="<?= (int)($item['is_private'] ?? 0) === 1 ? 'Enter new password or leave blank' : 'Enter password' ?>">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <?= (int)($item['is_private'] ?? 0) === 1 ? 'Only enter a password if you want to change it' : 'Users will need to enter this password to access the item' ?>
                </p>
            </div>
        </div>

        <script>
            function togglePasswordField() {
                const checkbox = document.getElementById('is_private');
                const passwordField = document.getElementById('password-field');
                const passwordInput = document.getElementById('password');

                if (checkbox.checked) {
                    passwordField.classList.remove('hidden');
                    // Don't make password required on edit form (can keep existing password)
                } else {
                    passwordField.classList.add('hidden');
                    passwordInput.required = false;
                    passwordInput.value = '';
                }

                // Reinitialize Lucide icons
                lucide.createIcons();
            }
        </script>

        <div>
            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Category
            </label>
            <?php if (!empty($categories)): ?>
            <input list="categories"
                   name="category"
                   id="category"
                   value="<?= Security::escape($item['category'] ?? '') ?>"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
            <datalist id="categories">
                <?php foreach ($categories as $cat): ?>
                <option value="<?= Security::escape($cat) ?>">
                <?php endforeach; ?>
            </datalist>
            <?php else: ?>
            <input type="text"
                   name="category"
                   id="category"
                   value="<?= Security::escape($item['category'] ?? '') ?>"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
            <?php endif; ?>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Group similar items together.</p>
        </div>

        <div>
            <label for="display_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Display Order
            </label>
            <input type="number"
                   name="display_order"
                   id="display_order"
                   value="<?= Security::escape((string) $item['display_order']) ?>"
                   min="0"
                   class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lower numbers appear first.</p>
        </div>

        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= View::url('/?page=' . Security::escape($page['slug'])) ?>"
               class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors cursor-pointer">
                Update Item
            </button>
        </div>
    </form>
</div>
