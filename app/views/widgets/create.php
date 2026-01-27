<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Add New Widget</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add a widget to display dynamic information on your dashboard.</p>
    </div>

    <form method="POST" action="<?= View::url('/widgets/create') ?>" class="space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">

        <div>
            <label for="page_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Page *
            </label>
            <select name="page_id"
                    id="page_id"
                    required
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
                <?php foreach ($pages as $p): ?>
                <option value="<?= $p['id'] ?>" <?= isset($selectedPageId) && (int) $p['id'] === (int) $selectedPageId ? 'selected' : '' ?>>
                    <?= Security::escape($p['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="widget_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Widget Type *
            </label>
            <select name="widget_type"
                    id="widget_type"
                    required
                    onchange="updateWidgetSettings()"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
                <option value="">-- Select Widget Type --</option>
                <?php foreach ($widgetTypes as $type => $metadata): ?>
                <option value="<?= Security::escape($type) ?>">
                    <?= Security::escape($metadata['display_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose the type of widget to display.</p>
        </div>

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Title *
            </label>
            <input type="text"
                   name="title"
                   id="title"
                   required
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
        </div>

        <div>
            <label for="icon" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Icon
            </label>
            <input type="hidden" name="icon_type" value="lucide">
            <select name="icon"
                    id="icon"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
                <?php
                $icons = ['activity', 'bar-chart', 'box', 'clock', 'cloud', 'cpu', 'database', 'disc', 'folder', 'gauge', 'globe', 'hard-drive', 'home', 'info', 'layout-grid', 'monitor', 'network', 'notepad-text', 'rss', 'server', 'sticky-note', 'sun', 'thermometer', 'wifi', 'zap'];
                foreach ($icons as $iconName):
                ?>
                <option value="<?= $iconName ?>" <?= $iconName === 'box' ? 'selected' : '' ?>>
                    <?= ucwords(str_replace('-', ' ', $iconName)) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="refresh_interval" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Refresh Interval (seconds)
            </label>
            <input type="number"
                   name="refresh_interval"
                   id="refresh_interval"
                   value="300"
                   min="0"
                   step="1"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">How often to refresh the widget data (0 = no auto-refresh).</p>
        </div>

        <div class="flex items-center">
            <input type="checkbox"
                   name="is_enabled"
                   id="is_enabled"
                   checked
                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
            <label for="is_enabled" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                Enable widget
            </label>
        </div>

        <!-- Dynamic Widget Settings -->
        <div id="widget-settings" class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-6">
            <!-- Settings will be injected here by JavaScript -->
        </div>

        <div class="flex justify-end space-x-3">
            <a href="<?= View::url('/settings') ?>"
               class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </a>
            <button type="submit"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md text-sm font-medium">
                Create Widget
            </button>
        </div>
    </form>
</div>

<script>
// Widget settings templates
const widgetSettingsTemplates = {
    'external_ip': `
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_location" id="show_location" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_location" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show location information
                </label>
            </div>
        </div>
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_isp" id="show_isp" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_isp" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show ISP information
                </label>
            </div>
        </div>
    `,
    'weather': `
        <div>
            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Location *
            </label>
            <input type="text" name="location" id="location" required
                   placeholder="e.g., London, Tokyo, New York"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">City name or coordinates.</p>
        </div>
        <div>
            <label for="units" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Units
            </label>
            <select name="units" id="units"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
                <option value="metric">Metric (°C, km/h)</option>
                <option value="imperial">Imperial (°F, mph)</option>
            </select>
        </div>
    `,
    'system_stats': `
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_cpu" id="show_cpu" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_cpu" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show CPU load
                </label>
            </div>
        </div>
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_memory" id="show_memory" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_memory" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show memory usage
                </label>
            </div>
        </div>
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_disk" id="show_disk" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_disk" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show disk usage
                </label>
            </div>
        </div>
        <div>
            <label for="disk_path" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Disk Path
            </label>
            <input type="text" name="disk_path" id="disk_path" value="/"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
        </div>
    `,
    'clock': `
        <div>
            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Timezone
            </label>
            <input type="text" name="timezone" id="timezone" value="<?= date_default_timezone_get() ?>"
                   placeholder="e.g., America/New_York, Europe/London"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">PHP timezone identifier.</p>
        </div>
        <div>
            <label for="format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Time Format
            </label>
            <select name="format" id="format"
                    class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
                <option value="24h">24-hour</option>
                <option value="12h">12-hour (AM/PM)</option>
            </select>
        </div>
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_seconds" id="show_seconds" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_seconds" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show seconds
                </label>
            </div>
        </div>
    `,
    'notes': `
        <div>
            <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Note Content
            </label>
            <textarea name="content" id="content" rows="5"
                      placeholder="Enter your notes here..."
                      class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2"></textarea>
        </div>
    `,
    'rss': `
        <div>
            <label for="feed_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                RSS Feed URL *
            </label>
            <input type="url" name="feed_url" id="feed_url" required
                   placeholder="https://example.com/feed.xml"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
        </div>
        <div>
            <label for="item_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Number of Items
            </label>
            <input type="number" name="item_count" id="item_count" value="5" min="1" max="20"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
        </div>
    `,
    'proxmox': `
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 mb-4">
            <p class="text-xs font-semibold text-yellow-800 dark:text-yellow-200 mb-2">API Token Setup:</p>
            <ol class="text-xs text-yellow-800 dark:text-yellow-200 space-y-1 list-decimal list-inside">
                <li>Proxmox Web UI → Datacenter → Permissions → API Tokens</li>
                <li>Click "Add" to create new token</li>
                <li><strong>IMPORTANT:</strong> UNCHECK "Privilege Separation"</li>
                <li>Copy the Token Secret (shown only once!)</li>
            </ol>
        </div>
        <div>
            <label for="api_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Proxmox API URL *
            </label>
            <input type="url" name="api_url" id="api_url" required
                   placeholder="https://proxmox.example.com:8006"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Example: https://192.168.1.100:8006</p>
        </div>
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Username *
            </label>
            <input type="text" name="username" id="username" required value="root@pam"
                   placeholder="root@pam"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format: username@realm (e.g., root@pam)</p>
        </div>
        <div>
            <label for="token_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                API Token ID *
            </label>
            <input type="text" name="token_id" id="token_id" required
                   placeholder="dashboard"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Just the token name (e.g., "dashboard"), not the full ID shown in Proxmox
            </p>
        </div>
        <div>
            <label for="token_secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                API Token Secret *
            </label>
            <input type="password" name="token_secret" id="token_secret" required
                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                   class="mt-1 block w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm px-4 py-2">
        </div>
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_cluster_stats" id="show_cluster_stats" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_cluster_stats" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show cluster overview statistics
                </label>
            </div>
        </div>
        <div>
            <div class="flex items-center">
                <input type="checkbox" name="show_node_details" id="show_node_details" checked
                       class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="show_node_details" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    Show individual node details
                </label>
            </div>
        </div>
    `
};

function updateWidgetSettings() {
    const widgetType = document.getElementById('widget_type').value;
    const settingsContainer = document.getElementById('widget-settings');

    if (widgetType && widgetSettingsTemplates[widgetType]) {
        settingsContainer.innerHTML = '<h3 class="text-md font-semibold text-gray-700 dark:text-gray-300 mb-3">Widget Settings</h3>' + widgetSettingsTemplates[widgetType];
    } else {
        settingsContainer.innerHTML = '';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateWidgetSettings();
});
</script>
