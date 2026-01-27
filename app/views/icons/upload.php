<?php
declare(strict_types=1);
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <a href="<?= View::url('/icons') ?>"
                   class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Back to Icon Library
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Upload Custom Icons</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Upload multiple SVG files at once to use as custom icons in your dashboard
            </p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                    <p class="ml-3 text-sm text-red-700 dark:text-red-300">
                        <?= Security::escape($_SESSION['error']) ?>
                    </p>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 space-y-6">
                <!-- File Upload Drop Zone -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        SVG Files (Multiple Selection Supported)
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-lg hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                         id="drop-zone">
                        <div class="space-y-1 text-center">
                            <i data-lucide="upload-cloud" class="mx-auto h-12 w-12 text-gray-400"></i>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                <label for="icon-upload"
                                       class="relative cursor-pointer rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500">
                                    <span>Select files</span>
                                    <input id="icon-upload"
                                           name="icons[]"
                                           type="file"
                                           accept=".svg"
                                           multiple
                                           class="sr-only">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                SVG files only, max 500KB each
                            </p>
                            <p class="text-xs text-blue-600 dark:text-blue-400 font-medium mt-2">
                                <i data-lucide="layers" class="w-3 h-3 inline"></i>
                                Multiple files supported
                            </p>
                        </div>
                    </div>
                </div>

                <!-- File List with Progress -->
                <div id="file-list" class="space-y-3 hidden">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Selected Files (<span id="file-count">0</span>)
                    </h3>
                    <div id="files-container" class="space-y-2"></div>
                </div>

                <!-- Guidelines -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">Icon Guidelines</h3>
                    <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1 list-disc list-inside">
                        <li>Only SVG format is supported</li>
                        <li>Maximum file size: 500KB per file</li>
                        <li>Icons should be square (1:1 aspect ratio) for best results</li>
                        <li>Avoid embedded JavaScript or external references</li>
                        <li>Monochrome icons work best (can be styled with CSS)</li>
                    </ul>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <div class="text-sm text-gray-600 dark:text-gray-400" id="upload-status">
                    Ready to upload
                </div>
                <div class="flex gap-3">
                    <a href="<?= View::url('/icons') ?>"
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </a>
                    <button type="button"
                            id="upload-btn"
                            disabled
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="upload" class="w-4 h-4 inline mr-2"></i>
                        Upload Icons
                    </button>
                </div>
            </div>
        </div>

        <!-- Upload Results -->
        <div id="upload-results" class="mt-6 hidden">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Upload Results</h3>
                <div id="results-container" class="space-y-2"></div>
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <a href="<?= View::url('/icons') ?>"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                        <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                        View Icon Library
                    </a>
                </div>
            </div>
        </div>

        <!-- Manual Upload Info -->
        <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2 flex items-center">
                <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400"></i>
                Alternative: Manual Upload
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                You can also manually copy SVG files to the server via SFTP/SSH:
            </p>
            <code class="block bg-gray-100 dark:bg-gray-900 text-sm p-3 rounded border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200">
                /var/www/html/dashboard/public/icons/
            </code>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-3">
                After copying files, return to the Icon Library and click "Scan Folder" to register them.
            </p>
        </div>
    </div>
</div>

<script>
// State management
let selectedFiles = [];
const maxFileSize = 500 * 1024; // 500KB

// DOM elements
const fileInput = document.getElementById('icon-upload');
const dropZone = document.getElementById('drop-zone');
const fileList = document.getElementById('file-list');
const filesContainer = document.getElementById('files-container');
const fileCount = document.getElementById('file-count');
const uploadBtn = document.getElementById('upload-btn');
const uploadStatus = document.getElementById('upload-status');
const uploadResults = document.getElementById('upload-results');
const resultsContainer = document.getElementById('results-container');

// File input handler
fileInput.addEventListener('change', function(e) {
    handleFiles(Array.from(e.target.files));
});

// Drag and drop handlers
dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    dropZone.classList.add('border-blue-500', 'dark:border-blue-400');
});

dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'dark:border-blue-400');
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('border-blue-500', 'dark:border-blue-400');

    const files = Array.from(e.dataTransfer.files).filter(f => f.name.endsWith('.svg'));
    handleFiles(files);
});

// Handle file selection
function handleFiles(files) {
    selectedFiles = files.filter(file => {
        // Validate file type and size
        if (!file.name.endsWith('.svg')) {
            return false;
        }
        if (file.size > maxFileSize) {
            alert(`${file.name} is too large (max 500KB)`);
            return false;
        }
        return true;
    });

    if (selectedFiles.length === 0) {
        fileList.classList.add('hidden');
        uploadBtn.disabled = true;
        return;
    }

    // Update UI
    fileList.classList.remove('hidden');
    fileCount.textContent = selectedFiles.length;
    uploadBtn.disabled = false;
    uploadStatus.textContent = `${selectedFiles.length} file(s) ready`;

    // Render file list
    filesContainer.innerHTML = '';
    selectedFiles.forEach((file, index) => {
        const fileCard = document.createElement('div');
        fileCard.className = 'flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700';
        fileCard.id = `file-${index}`;
        fileCard.innerHTML = `
            <div class="flex items-center space-x-3 flex-1">
                <i data-lucide="file-text" class="w-5 h-5 text-gray-400"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${file.name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">${formatFileSize(file.size)}</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <div class="hidden" id="progress-${index}">
                    <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full transition-all" style="width: 0%" id="progress-bar-${index}"></div>
                    </div>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400" id="status-${index}">Ready</span>
                <button type="button" onclick="removeFile(${index})" class="text-red-600 hover:text-red-700">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        `;
        filesContainer.appendChild(fileCard);
    });

    lucide.createIcons();
}

// Remove file from selection
function removeFile(index) {
    selectedFiles.splice(index, 1);
    handleFiles(selectedFiles);
}

// Format file size
function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

// Upload files
uploadBtn.addEventListener('click', async function() {
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 inline mr-2 animate-spin"></i> Uploading...';
    lucide.createIcons();

    let successCount = 0;
    let errorCount = 0;
    const results = [];

    for (let i = 0; i < selectedFiles.length; i++) {
        const file = selectedFiles[i];
        const statusEl = document.getElementById(`status-${i}`);
        const progressEl = document.getElementById(`progress-${i}`);
        const progressBar = document.getElementById(`progress-bar-${i}`);

        try {
            statusEl.textContent = 'Uploading...';
            progressEl.classList.remove('hidden');

            const formData = new FormData();
            formData.append('icon', file);
            formData.append('csrf_token', '<?= Security::getCSRFToken() ?>');

            const response = await fetch('/dashboard/icons/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Simulate progress (since we can't track actual upload progress easily with PHP)
            for (let p = 0; p <= 100; p += 20) {
                progressBar.style.width = p + '%';
                await new Promise(resolve => setTimeout(resolve, 50));
            }

            const data = await response.json();

            if (data.success) {
                statusEl.textContent = 'Success';
                statusEl.className = 'text-sm text-green-600 dark:text-green-400';
                successCount++;
                results.push({ file: file.name, success: true, message: data.message });
            } else {
                statusEl.textContent = 'Failed';
                statusEl.className = 'text-sm text-red-600 dark:text-red-400';
                errorCount++;
                results.push({ file: file.name, success: false, message: data.message || 'Upload failed' });
            }

        } catch (error) {
            console.error('Upload error:', error);
            statusEl.textContent = 'Error';
            statusEl.className = 'text-sm text-red-600 dark:text-red-400';
            errorCount++;
            results.push({ file: file.name, success: false, message: 'Network error' });
        }
    }

    // Show results
    uploadStatus.textContent = `Completed: ${successCount} succeeded, ${errorCount} failed`;
    uploadBtn.disabled = false;
    uploadBtn.innerHTML = '<i data-lucide="upload" class="w-4 h-4 inline mr-2"></i> Upload More';
    lucide.createIcons();

    // Display detailed results
    uploadResults.classList.remove('hidden');
    resultsContainer.innerHTML = '';
    results.forEach(result => {
        const resultDiv = document.createElement('div');
        resultDiv.className = `flex items-start space-x-3 p-3 rounded-lg ${result.success ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'}`;
        resultDiv.innerHTML = `
            <i data-lucide="${result.success ? 'check-circle' : 'x-circle'}" class="w-5 h-5 ${result.success ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'} flex-shrink-0"></i>
            <div class="flex-1">
                <p class="text-sm font-medium ${result.success ? 'text-green-900 dark:text-green-100' : 'text-red-900 dark:text-red-100'}">${result.file}</p>
                <p class="text-xs ${result.success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'}">${result.message}</p>
            </div>
        `;
        resultsContainer.appendChild(resultDiv);
    });
    lucide.createIcons();
});
</script>
