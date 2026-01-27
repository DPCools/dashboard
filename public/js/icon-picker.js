/**
 * Icon Picker Component
 * Allows selecting between Lucide icons and custom uploaded SVG icons
 */

let iconPickerModal = null;
let iconPickerCallback = null;
let customIconsLoaded = false;
let currentPage = 1;
let hasMoreIcons = false;
let isLoadingIcons = false;

// Initialize icon picker
function initIconPicker() {
    // Create modal if it doesn't exist
    if (!document.getElementById('icon-picker-modal')) {
        const modal = document.createElement('div');
        modal.id = 'icon-picker-modal';
        modal.className = 'fixed inset-0 z-50 hidden';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeIconPicker()"></div>
            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Choose Icon</h3>
                            <button onclick="closeIconPicker()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <i data-lucide="x" class="w-5 h-5"></i>
                            </button>
                        </div>

                        <!-- Tabs -->
                        <div class="border-b border-gray-200 dark:border-gray-700">
                            <nav class="flex -mb-px">
                                <button onclick="switchIconTab('lucide')"
                                        class="icon-tab px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                                        data-tab="lucide">
                                    Lucide Icons
                                </button>
                                <button onclick="switchIconTab('custom')"
                                        class="icon-tab px-6 py-3 text-sm font-medium border-b-2 transition-colors"
                                        data-tab="custom">
                                    Custom Icons
                                </button>
                            </nav>
                        </div>

                        <!-- Search -->
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="relative">
                                <input type="text"
                                       id="icon-search"
                                       placeholder="Search icons..."
                                       class="w-full px-4 py-2 pl-10 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       oninput="filterIcons(this.value)">
                                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-2.5 text-gray-400"></i>
                            </div>
                        </div>

                        <!-- Icon Grid -->
                        <div class="px-6 py-4 overflow-y-auto max-h-96">
                            <div id="lucide-icons" class="icon-content grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-3">
                                <!-- Lucide icons will be loaded here -->
                            </div>
                            <div id="custom-icons" class="icon-content hidden grid grid-cols-6 sm:grid-cols-8 md:grid-cols-10 gap-3">
                                <!-- Custom icons will be loaded here -->
                            </div>
                            <div id="no-results" class="hidden text-center py-8 text-gray-500 dark:text-gray-400">
                                No icons found
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        iconPickerModal = modal;

        // Load Lucide icons immediately
        loadLucideIcons();

        // Don't load custom icons until needed (lazy loading)
        // They will be loaded when the Custom Icons tab is clicked
    }
}

// Load Lucide icons
function loadLucideIcons() {
    const lucideIcons = [
        'home', 'server', 'monitor', 'cpu', 'hard-drive', 'database',
        'cloud', 'wifi', 'globe', 'link', 'link-2', 'external-link',
        'video', 'film', 'music', 'image', 'camera', 'play',
        'settings', 'tool', 'wrench', 'sliders', 'toggle-left', 'power',
        'download', 'upload', 'folder', 'file', 'file-text', 'archive',
        'lock', 'unlock', 'shield', 'key', 'eye', 'eye-off',
        'user', 'users', 'mail', 'message-square', 'bell', 'calendar',
        'search', 'filter', 'list', 'grid', 'layers', 'box',
        'package', 'shopping-cart', 'credit-card', 'dollar-sign',
        'chart-bar', 'activity', 'trending-up', 'pie-chart',
        'bookmark', 'star', 'heart', 'flag', 'tag', 'paperclip',
        'printer', 'scan', 'smartphone', 'tablet', 'laptop', 'watch',
        'thermometer', 'lightbulb', 'zap', 'battery', 'plug',
        'git-branch', 'github', 'gitlab', 'code', 'terminal', 'command',
        'layout-dashboard', 'layout-grid', 'layout-list', 'square', 'circle',
        'check', 'x', 'plus', 'minus', 'edit', 'trash-2'
    ];

    const container = document.getElementById('lucide-icons');
    container.innerHTML = lucideIcons.map(icon => `
        <button type="button"
                class="icon-option flex flex-col items-center p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                data-icon="${icon}"
                data-type="lucide"
                data-search="${icon}"
                onclick="selectIcon('${icon}', 'lucide')">
            <i data-lucide="${icon}" class="w-6 h-6 text-gray-700 dark:text-gray-300 mb-1"></i>
            <span class="text-xs text-gray-600 dark:text-gray-400 text-center truncate w-full">${icon}</span>
        </button>
    `).join('');

    // Reinitialize Lucide for new icons
    if (window.lucide) {
        lucide.createIcons();
    }
}

// Load custom icons from server (with pagination)
async function loadCustomIcons(append = false) {
    if (isLoadingIcons) return;
    isLoadingIcons = true;

    try {
        const response = await fetch(`/dashboard/icons/get?page=${currentPage}`);
        const data = await response.json();

        if (data.success && data.icons.custom) {
            const container = document.getElementById('custom-icons');

            if (data.icons.custom.length === 0 && currentPage === 1) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-8">
                        <i data-lucide="image-off" class="w-12 h-12 mx-auto text-gray-400 mb-3"></i>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No custom icons uploaded yet</p>
                        <a href="/dashboard/icons/upload" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-2 inline-block">
                            Upload your first icon
                        </a>
                    </div>
                `;
            } else {
                const iconsHtml = data.icons.custom.map(icon => `
                    <button type="button"
                            class="icon-option flex flex-col items-center p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                            data-icon="${icon.filename}"
                            data-type="custom"
                            data-search="${icon.display_name.toLowerCase()}"
                            onclick="selectIcon('${icon.filename}', 'custom')">
                        <img src="/dashboard/public/icons/${icon.filename}"
                             alt="${icon.display_name}"
                             class="w-6 h-6 mb-1">
                        <span class="text-xs text-gray-600 dark:text-gray-400 text-center truncate w-full">${icon.display_name}</span>
                    </button>
                `).join('');

                if (append) {
                    // Remove load more button if it exists
                    const loadMoreBtn = container.querySelector('.load-more-btn');
                    if (loadMoreBtn) loadMoreBtn.remove();

                    // Append new icons
                    container.insertAdjacentHTML('beforeend', iconsHtml);
                } else {
                    container.innerHTML = iconsHtml;
                }

                // Add load more button if there are more pages
                if (data.pagination && data.pagination.has_more) {
                    hasMoreIcons = true;
                    const loadMoreHtml = `
                        <div class="col-span-full text-center py-4 load-more-btn">
                            <button type="button"
                                    onclick="loadMoreCustomIcons()"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                Load More Icons (${data.pagination.page * data.pagination.per_page} / ${data.pagination.total})
                            </button>
                        </div>
                    `;
                    container.insertAdjacentHTML('beforeend', loadMoreHtml);
                } else {
                    hasMoreIcons = false;
                }
            }

            if (window.lucide) {
                lucide.createIcons();
            }
        }
    } catch (error) {
        console.error('Failed to load custom icons:', error);
        const container = document.getElementById('custom-icons');
        container.innerHTML = `
            <div class="col-span-full text-center py-8 text-red-600 dark:text-red-400">
                Failed to load custom icons. Please try again.
            </div>
        `;
    } finally {
        isLoadingIcons = false;
    }
}

// Load more custom icons
function loadMoreCustomIcons() {
    currentPage++;
    loadCustomIcons(true);
}

// Open icon picker
function openIconPicker(callback) {
    initIconPicker();
    iconPickerCallback = callback;
    iconPickerModal.classList.remove('hidden');

    // Reset custom icons state
    customIconsLoaded = false;
    currentPage = 1;

    switchIconTab('lucide');
    document.getElementById('icon-search').value = '';
    filterIcons('');
}

// Close icon picker
function closeIconPicker() {
    if (iconPickerModal) {
        iconPickerModal.classList.add('hidden');
        iconPickerCallback = null;
    }
}

// Switch between tabs
function switchIconTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.icon-tab').forEach(btn => {
        const isActive = btn.dataset.tab === tab;
        btn.className = 'icon-tab px-6 py-3 text-sm font-medium border-b-2 transition-colors ' +
            (isActive
                ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300');
    });

    // Clear search when switching tabs
    const searchInput = document.getElementById('icon-search');
    if (searchInput) {
        searchInput.value = '';
    }

    // Lazy load custom icons only when the tab is clicked
    if (tab === 'custom' && !customIconsLoaded) {
        const container = document.getElementById('custom-icons');
        container.innerHTML = '<div class="col-span-full text-center py-8"><div class="animate-pulse text-gray-500 dark:text-gray-400">Loading custom icons...</div></div>';
        loadCustomIcons();
        customIconsLoaded = true;
    }

    // Update content
    document.querySelectorAll('.icon-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.getElementById(tab + '-icons').classList.remove('hidden');
}

// Filter icons by search query
let searchTimeout = null;
async function filterIcons(query) {
    const searchQuery = query.toLowerCase().trim();
    const activeTab = document.querySelector('.icon-tab[class*="border-blue"]');
    const tabName = activeTab ? activeTab.dataset.tab : 'lucide';
    const container = document.getElementById(tabName + '-icons');

    // For custom icons with search query, use server-side search
    if (tabName === 'custom' && searchQuery !== '') {
        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Debounce search - wait 300ms after user stops typing
        searchTimeout = setTimeout(async () => {
            container.innerHTML = '<div class="col-span-full text-center py-8"><div class="animate-pulse text-gray-500 dark:text-gray-400">Searching...</div></div>';

            try {
                const response = await fetch(`/dashboard/icons/get?search=${encodeURIComponent(searchQuery)}&page=1`);
                const data = await response.json();

                if (data.success) {
                    if (data.icons.custom.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-full text-center py-8">
                                <i data-lucide="search-x" class="w-12 h-12 mx-auto text-gray-400 mb-3"></i>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No icons found matching "${searchQuery}"</p>
                            </div>
                        `;
                        if (window.lucide) lucide.createIcons();
                    } else {
                        const iconsHtml = data.icons.custom.map(icon => `
                            <button type="button"
                                    class="icon-option flex flex-col items-center p-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-colors"
                                    data-icon="${icon.filename}"
                                    data-type="custom"
                                    data-search="${icon.display_name.toLowerCase()}"
                                    onclick="selectIcon('${icon.filename}', 'custom')">
                                <img src="/dashboard/public/icons/${icon.filename}"
                                     alt="${icon.display_name}"
                                     class="w-6 h-6 mb-1">
                                <span class="text-xs text-gray-600 dark:text-gray-400 text-center truncate w-full">${icon.display_name}</span>
                            </button>
                        `).join('');

                        container.innerHTML = iconsHtml;

                        // Add note about results
                        if (data.pagination && data.pagination.total > 0) {
                            container.insertAdjacentHTML('beforeend', `
                                <div class="col-span-full text-center py-4 text-sm text-gray-500 dark:text-gray-400">
                                    Found ${data.pagination.total} icon${data.pagination.total === 1 ? '' : 's'} matching "${searchQuery}"
                                </div>
                            `);
                        }

                        if (window.lucide) lucide.createIcons();
                    }
                }
            } catch (error) {
                console.error('Search failed:', error);
                container.innerHTML = `
                    <div class="col-span-full text-center py-8 text-red-600 dark:text-red-400">
                        Search failed. Please try again.
                    </div>
                `;
            }
        }, 300); // Wait 300ms after user stops typing

        return;
    }

    // For Lucide icons or empty custom search, use client-side filtering
    const options = container.querySelectorAll('.icon-option');
    let visibleCount = 0;

    options.forEach(option => {
        const searchText = option.dataset.search || '';
        if (searchQuery === '' || searchText.includes(searchQuery)) {
            option.classList.remove('hidden');
            visibleCount++;
        } else {
            option.classList.add('hidden');
        }
    });

    // Hide load more button during search on custom icons tab
    if (tabName === 'custom') {
        const loadMoreBtn = container.querySelector('.load-more-btn');
        if (loadMoreBtn) {
            if (searchQuery !== '') {
                loadMoreBtn.classList.add('hidden');
            } else {
                loadMoreBtn.classList.remove('hidden');
            }
        }
    }

    // Show/hide no results message
    const noResults = document.getElementById('no-results');
    if (visibleCount === 0 && searchQuery !== '') {
        noResults.classList.remove('hidden');
        container.classList.add('hidden');
    } else {
        noResults.classList.add('hidden');
        container.classList.remove('hidden');
    }
}

// Select an icon
function selectIcon(iconName, iconType) {
    if (iconPickerCallback) {
        iconPickerCallback(iconName, iconType);
    }
    closeIconPicker();
}

// Helper function to set form icon values
function setFormIcon(iconName, iconType, previewElementId) {
    // Set hidden form fields
    const iconInput = document.querySelector('input[name="icon"]');
    const iconTypeInput = document.querySelector('input[name="icon_type"]');

    if (iconInput) iconInput.value = iconName;
    if (iconTypeInput) iconTypeInput.value = iconType;

    // Update preview
    const preview = document.getElementById(previewElementId);
    if (preview) {
        if (iconType === 'custom') {
            preview.innerHTML = `<img src="/dashboard/public/icons/${iconName}" alt="Selected icon" class="w-6 h-6">`;
        } else {
            preview.innerHTML = `<i data-lucide="${iconName}" class="w-6 h-6"></i>`;
            if (window.lucide) {
                lucide.createIcons();
            }
        }
    }
}
