<?php
/**
 * File Sharing - Index View
 * Admin list of all shared files
 */
?>

<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">File Sharing</h1>
        <div class="flex gap-2">
            <a href="<?= View::url('/files/upload') ?>" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                <i data-lucide="upload" class="w-4 h-4 mr-2"></i>
                Upload File
            </a>
            <form method="POST" action="<?= View::url('/files/cleanup') ?>" class="inline">
                <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                    <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                    Cleanup Expired
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Files</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total_files'] ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="text-sm text-gray-500 dark:text-gray-400">Storage Used</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= FileHelper::formatFileSize($stats['total_storage']) ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Downloads</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['total_downloads'] ?></div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <div class="text-sm text-gray-500 dark:text-gray-400">Password Protected</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white"><?= $stats['files_with_password'] ?></div>
        </div>
    </div>

    <!-- Storage Progress Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow mb-6">
        <div class="flex justify-between items-center mb-2">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Storage Usage</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <?= FileHelper::formatFileSize($storage_info['current_usage']) ?> / <?= FileHelper::formatFileSize($storage_info['max_storage']) ?>
            </div>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
            <?php
            $percentage = $storage_info['max_storage'] > 0
                ? ($storage_info['current_usage'] / $storage_info['max_storage']) * 100
                : 0;
            $barColor = $percentage > 90 ? 'bg-red-600' : ($percentage > 70 ? 'bg-yellow-600' : 'bg-purple-600');
            ?>
            <div class="<?= $barColor ?> h-2.5 rounded-full transition-all" style="width: <?= min($percentage, 100) ?>%"></div>
        </div>
    </div>

    <!-- Files Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <?php if (empty($files)): ?>
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                <p>No files uploaded yet.</p>
                <a href="<?= View::url('/files/upload') ?>" class="text-purple-600 hover:underline mt-2 inline-block">Upload your first file</a>
            </div>
        <?php else: ?>
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">File</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Downloads</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Share URL</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($files as $file): ?>
                        <?php $isExpired = SharedFile::isExpired($file); ?>
                        <tr class="<?= $isExpired ? 'opacity-50' : '' ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i data-lucide="<?= FileHelper::getMimeTypeIcon($file['mime_type']) ?>" class="w-5 h-5 mr-3 text-gray-400"></i>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?= Security::escape($file['original_filename']) ?>
                                            <?php if ($file['password_hash'] !== null): ?>
                                                <i data-lucide="lock" class="w-3 h-3 inline ml-1 text-gray-400"></i>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($file['description'])): ?>
                                            <div class="text-xs text-gray-500 dark:text-gray-400"><?= Security::escape($file['description']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= FileHelper::formatFileSize($file['file_size']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?= $file['download_count'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if ($file['expires_at'] === null): ?>
                                    <span class="text-gray-500 dark:text-gray-400">Never</span>
                                <?php elseif ($isExpired): ?>
                                    <span class="text-red-600 dark:text-red-400">Expired</span>
                                <?php else: ?>
                                    <span class="text-gray-500 dark:text-gray-400"><?= date('M j, Y', strtotime($file['expires_at'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <input type="text" readonly value="<?= SharedFile::getShareUrl($file['token']) ?>" class="flex-1 px-2 py-1 text-xs bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white" id="url-<?= $file['id'] ?>">
                                    <button onclick="copyUrl('url-<?= $file['id'] ?>')" class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded transition-colors">
                                        <i data-lucide="copy" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="<?= View::url('/files/edit?id=' . $file['id']) ?>" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 mr-3">
                                    <i data-lucide="edit-2" class="w-4 h-4 inline"></i>
                                </a>
                                <form method="POST" action="<?= View::url('/files/delete') ?>" class="inline" onsubmit="return confirm('Delete this file?')">
                                    <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
                                    <input type="hidden" name="id" value="<?= $file['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Back Link -->
    <div class="mt-6">
        <a href="<?= View::url('/settings') ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i>
            Back to Settings
        </a>
    </div>
</div>

<script>
function copyUrl(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    document.execCommand('copy');

    // Show feedback
    const button = input.nextElementSibling;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i data-lucide="check" class="w-3 h-3"></i>';
    lucide.createIcons();

    setTimeout(() => {
        button.innerHTML = originalHTML;
        lucide.createIcons();
    }, 2000);
}
</script>
