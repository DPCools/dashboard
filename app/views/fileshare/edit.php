<?php
/**
 * File Sharing - Edit View
 * Form for editing file metadata
 */
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <!-- Header -->
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Edit File</h1>

    <!-- Edit Form -->
    <form method="POST" action="<?= View::url('/files/update') ?>" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>">
        <input type="hidden" name="id" value="<?= $file['id'] ?>">

        <!-- File Info (Read-only) -->
        <div class="mb-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
            <div class="flex items-center mb-3">
                <i data-lucide="<?= FileHelper::getMimeTypeIcon($file['mime_type']) ?>" class="w-6 h-6 mr-3 text-gray-400"></i>
                <div>
                    <div class="text-lg font-medium text-gray-900 dark:text-white">
                        <?= Security::escape($file['original_filename']) ?>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <?= FileHelper::formatFileSize($file['file_size']) ?> · <?= $file['download_count'] ?> downloads
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-500 dark:text-gray-400">Share URL:</span>
                <input type="text" readonly value="<?= SharedFile::getShareUrl($file['token']) ?>" class="flex-1 px-2 py-1 text-xs bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded text-gray-900 dark:text-white" id="shareUrl">
                <button type="button" onclick="copyUrl()" class="px-2 py-1 text-xs bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded transition-colors">
                    <i data-lucide="copy" class="w-3 h-3"></i>
                </button>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description
            </label>
            <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" placeholder="Add a description for this file..."><?= Security::escape($file['description'] ?? '') ?></textarea>
        </div>

        <!-- Expiration -->
        <div class="mb-6">
            <label for="expiration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Expiration
            </label>
            <?php
            // Calculate current expiration preset
            $currentExpiration = 'unlimited';
            if ($file['expires_at'] !== null) {
                $expiresTime = strtotime($file['expires_at']);
                $now = time();
                $diff = $expiresTime - $now;

                if ($diff <= 2592000 + 86400) { // 30 days + 1 day tolerance
                    $currentExpiration = '30d';
                } elseif ($diff <= 5184000 + 86400) { // 2 months
                    $currentExpiration = '2m';
                } elseif ($diff <= 10368000 + 86400) { // 4 months
                    $currentExpiration = '4m';
                } elseif ($diff <= 15552000 + 86400) { // 6 months
                    $currentExpiration = '6m';
                }
            }
            ?>
            <select name="expiration" id="expiration" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                <option value="30d" <?= $currentExpiration === '30d' ? 'selected' : '' ?>>30 days</option>
                <option value="2m" <?= $currentExpiration === '2m' ? 'selected' : '' ?>>2 months</option>
                <option value="4m" <?= $currentExpiration === '4m' ? 'selected' : '' ?>>4 months</option>
                <option value="6m" <?= $currentExpiration === '6m' ? 'selected' : '' ?>>6 months</option>
                <option value="unlimited" <?= $currentExpiration === 'unlimited' ? 'selected' : '' ?>>Unlimited (never expires)</option>
            </select>
            <?php if ($file['expires_at'] !== null): ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Current expiration: <?= date('F j, Y g:i A', strtotime($file['expires_at'])) ?>
                    <?php if (SharedFile::isExpired($file)): ?>
                        <span class="text-red-600 dark:text-red-400 font-medium">(Expired)</span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Password Protection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Password Protection
            </label>

            <?php if ($file['password_hash'] !== null): ?>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-blue-700 dark:text-blue-300">
                            <i data-lucide="lock" class="w-4 h-4 mr-2"></i>
                            <span class="text-sm">Password protection enabled</span>
                        </div>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remove_password" value="1" class="mr-2">
                            <span class="text-sm text-blue-700 dark:text-blue-300">Remove password</span>
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    <?= $file['password_hash'] !== null ? 'Change password (leave empty to keep current)' : 'Set password (leave empty for no password)' ?>
                </span>
                <button type="button" onclick="togglePassword()" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">
                    <span id="passwordToggleText">Show</span>
                </button>
            </div>
            <input type="password" name="password" id="password" autocomplete="new-password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" placeholder="Enter new password">
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= View::url('/files') ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                Save Changes
            </button>
        </div>
    </form>
</div>

<script>
function copyUrl() {
    const input = document.getElementById('shareUrl');
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

function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleText = document.getElementById('passwordToggleText');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleText.textContent = 'Hide';
    } else {
        passwordInput.type = 'password';
        toggleText.textContent = 'Show';
    }
}
</script>
