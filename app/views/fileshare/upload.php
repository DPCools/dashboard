<?php
/**
 * File Sharing - Upload View
 * Form for uploading new files
 */
?>

<div class="max-w-3xl mx-auto px-4 py-8">
    <!-- Header -->
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Upload File</h1>

    <!-- Upload Form -->
    <form method="POST" action="<?= View::url('/files/upload') ?>" enctype="multipart/form-data" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6" id="uploadForm">
        <input type="hidden" name="csrf_token" value="<?= Security::getCSRFToken() ?>" id="csrfToken">
        <input type="text" name="username" autocomplete="username" style="display:none;" tabindex="-1" aria-hidden="true">

        <!-- File Input -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                File
            </label>
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-purple-500 dark:hover:border-purple-400 transition-colors cursor-pointer" id="dropZone">
                <input type="file" name="file" id="fileInput" required class="hidden">
                <i data-lucide="upload-cloud" class="w-12 h-12 mx-auto mb-3 text-gray-400"></i>
                <p class="text-gray-600 dark:text-gray-400 mb-1">Click to select or drag and drop a file</p>
                <p class="text-sm text-gray-500 dark:text-gray-500">Maximum file size: 4.8 GB</p>
                <div id="fileInfo" class="mt-4 hidden">
                    <div class="inline-flex items-center bg-purple-100 dark:bg-purple-900 rounded-lg px-4 py-2">
                        <i data-lucide="file" class="w-4 h-4 mr-2 text-purple-600 dark:text-purple-400"></i>
                        <span id="fileName" class="text-sm font-medium text-purple-600 dark:text-purple-400"></span>
                        <button type="button" onclick="clearFile()" class="ml-3 text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-200">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Description (Optional)
            </label>
            <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" placeholder="Add a description for this file..."></textarea>
        </div>

        <!-- Expiration -->
        <div class="mb-6">
            <label for="expiration" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Expiration
            </label>
            <select name="expiration" id="expiration" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                <option value="30d" selected>30 days</option>
                <option value="2m">2 months</option>
                <option value="4m">4 months</option>
                <option value="6m">6 months</option>
                <option value="unlimited">Unlimited (never expires)</option>
            </select>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">File will be automatically deleted after expiration</p>
        </div>

        <!-- Password Protection -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Password Protection (Optional)
                </label>
                <button type="button" onclick="togglePassword()" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">
                    <span id="passwordToggleText">Show</span>
                </button>
            </div>
            <input type="password" name="password" id="password" autocomplete="new-password" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" placeholder="Leave empty for no password">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Require password to download this file</p>
        </div>

        <!-- Progress Bar (Hidden by default) -->
        <div id="progressContainer" class="mb-6 hidden">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Uploading...</span>
                <span id="progressPercent" class="text-sm text-gray-500 dark:text-gray-400">0%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div id="progressBar" class="bg-purple-600 h-2.5 rounded-full transition-all" style="width: 0%"></div>
            </div>
        </div>

        <!-- Error Message (Hidden by default) -->
        <div id="errorMessage" class="mb-6 hidden bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <div class="flex">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400 mr-3"></i>
                <p class="text-sm text-red-700 dark:text-red-300" id="errorText"></p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-between items-center pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="<?= View::url('/files') ?>" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                Cancel
            </a>
            <button type="submit" id="uploadButton" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <i data-lucide="upload" class="w-4 h-4 inline mr-2"></i>
                <span id="uploadButtonText">Upload File</span>
            </button>
        </div>
    </form>
</div>

<script>
// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const uploadForm = document.getElementById('uploadForm');
const uploadButton = document.getElementById('uploadButton');
const uploadButtonText = document.getElementById('uploadButtonText');
const progressContainer = document.getElementById('progressContainer');
const progressBar = document.getElementById('progressBar');
const progressPercent = document.getElementById('progressPercent');
const errorMessage = document.getElementById('errorMessage');
const errorText = document.getElementById('errorText');

dropZone.addEventListener('click', () => fileInput.click());

dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('border-purple-500', 'bg-purple-50', 'dark:bg-purple-900/20');

    if (e.dataTransfer.files.length > 0) {
        fileInput.files = e.dataTransfer.files;
        updateFileInfo();
    }
});

fileInput.addEventListener('change', updateFileInfo);

function updateFileInfo() {
    if (fileInput.files.length > 0) {
        fileName.textContent = fileInput.files[0].name;
        fileInfo.classList.remove('hidden');
        lucide.createIcons();
    }
}

function clearFile() {
    fileInput.value = '';
    fileInfo.classList.add('hidden');
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

function showError(message) {
    errorText.textContent = message;
    errorMessage.classList.remove('hidden');
    lucide.createIcons();

    // Scroll to error
    errorMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideError() {
    errorMessage.classList.add('hidden');
}

// Handle form submission with AJAX and progress tracking
uploadForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Hide any previous errors
    hideError();

    // Validate file selection
    if (!fileInput.files || fileInput.files.length === 0) {
        showError('Please select a file to upload');
        return;
    }

    // Validate file size (4.8GB)
    const maxSize = 4.8 * 1024 * 1024 * 1024; // 4.8GB
    if (fileInput.files[0].size > maxSize) {
        showError('File size exceeds 4.8 GB limit');
        return;
    }

    // Disable submit button
    uploadButton.disabled = true;
    uploadButtonText.textContent = 'Uploading...';

    // Show progress bar
    progressContainer.classList.remove('hidden');
    progressBar.style.width = '0%';
    progressPercent.textContent = '0%';

    // Prepare form data
    const formData = new FormData(uploadForm);

    try {
        // Create XMLHttpRequest for progress tracking
        const xhr = new XMLHttpRequest();

        // Track upload progress
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percent + '%';
                progressPercent.textContent = percent + '%';
            }
        });

        // Handle completion
        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                // Success
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Redirect to file list
                        window.location.href = response.redirect || '<?= View::url('/files') ?>';
                    } else {
                        showError(response.error || 'Upload failed');
                        uploadButton.disabled = false;
                        uploadButtonText.textContent = 'Upload File';
                        progressContainer.classList.add('hidden');
                    }
                } catch (e) {
                    // Fallback to direct redirect (non-AJAX response)
                    window.location.href = '<?= View::url('/files') ?>';
                }
            } else {
                // Error
                try {
                    const response = JSON.parse(xhr.responseText);
                    showError(response.error || 'Upload failed');
                } catch (e) {
                    showError('Upload failed. Please try again.');
                }

                // Re-enable button
                uploadButton.disabled = false;
                uploadButtonText.textContent = 'Upload File';
                progressContainer.classList.add('hidden');
            }
        });

        // Handle errors
        xhr.addEventListener('error', () => {
            showError('Network error. Please check your connection and try again.');
            uploadButton.disabled = false;
            uploadButtonText.textContent = 'Upload File';
            progressContainer.classList.add('hidden');
        });

        // Handle abort
        xhr.addEventListener('abort', () => {
            showError('Upload cancelled');
            uploadButton.disabled = false;
            uploadButtonText.textContent = 'Upload File';
            progressContainer.classList.add('hidden');
        });

        // Send request
        xhr.open('POST', uploadForm.action);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);

    } catch (error) {
        console.error('Upload error:', error);
        showError('An unexpected error occurred: ' + error.message);
        uploadButton.disabled = false;
        uploadButtonText.textContent = 'Upload File';
        progressContainer.classList.add('hidden');
    }
});
</script>
