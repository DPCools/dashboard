<?php
declare(strict_types=1);
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Icon Library</h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Manage custom icons for your dashboard items and pages
                </p>
            </div>
            <div class="flex gap-3">
                <a href="<?= View::url('/settings') ?>"
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Back to Settings
                </a>
                <a href="<?= View::url('/icons/upload') ?>"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                    Upload Icon
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                    <p class="ml-3 text-sm text-green-700 dark:text-green-300">
                        <?= Security::escape($_SESSION['success']) ?>
                    </p>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['info'])): ?>
            <div class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <i data-lucide="info" class="w-5 h-5 text-blue-600 dark:text-blue-400"></i>
                    <p class="ml-3 text-sm text-blue-700 dark:text-blue-300">
                        <?= Security::escape($_SESSION['info']) ?>
                    </p>
                </div>
            </div>
            <?php unset($_SESSION['info']); ?>
        <?php endif; ?>

        <!-- Custom Icons Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        Custom Icons (<?= count($custom_icons) ?>)
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        SVG icons uploaded or manually added to /public/icons/
                    </p>
                </div>
                <form method="POST" action="<?= View::url('/icons/scan') ?>" class="inline">
                    <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                    <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i data-lucide="scan" class="w-4 h-4 mr-2"></i>
                        Scan Folder
                    </button>
                </form>
            </div>

            <div class="p-6">
                <?php if (empty($custom_icons)): ?>
                    <div class="text-center py-12">
                        <i data-lucide="image-off" class="w-12 h-12 mx-auto text-gray-400 mb-4"></i>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">No custom icons yet</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Upload your first SVG icon or manually copy icons to /public/icons/
                        </p>
                        <a href="<?= View::url('/icons/upload') ?>"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                            <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                            Upload Icon
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        <?php foreach ($custom_icons as $icon): ?>
                            <div class="relative group border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 flex items-center justify-center mb-2">
                                        <img src="<?= View::url('/public/icons/' . Security::escape($icon['filename'])) ?>"
                                             alt="<?= Security::escape($icon['display_name']) ?>"
                                             class="max-w-full max-h-full">
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white text-center truncate w-full" title="<?= Security::escape($icon['display_name']) ?>">
                                        <?= Security::escape($icon['display_name']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <?= number_format($icon['file_size'] / 1024, 1) ?> KB
                                    </p>
                                </div>
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button onclick="deleteIcon(<?= $icon['id'] ?>, '<?= Security::escape($icon['display_name']) ?>')"
                                            class="p-1 bg-red-600 hover:bg-red-700 text-white rounded">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lucide Icons Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Lucide Icons (<?= $lucide_count ?>+)
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Built-in icon library loaded from CDN - No management needed
                </p>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Lucide provides a comprehensive set of beautiful icons that are automatically available
                    in your icon picker. These icons are loaded from a CDN and require no storage or management.
                </p>
                <a href="https://lucide.dev/icons/"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                    Browse Lucide Icons
                    <i data-lucide="external-link" class="w-4 h-4 ml-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function deleteIcon(iconId, iconName) {
    if (!confirm(`Are you sure you want to delete "${iconName}"?\n\nThis action cannot be undone.`)) {
        return;
    }

    const formData = new FormData();
    formData.append('icon_id', iconId);
    formData.append('csrf_token', '<?= Security::getCSRFToken() ?>');

    fetch('<?= View::url('/icons/delete') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('Error deleting icon: ' + error.message);
    });
}
</script>
