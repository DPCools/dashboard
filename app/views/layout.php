<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::escape(View::setting('site_title', DEFAULT_SITE_TITLE)) ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- SortableJS for drag-and-drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <!-- Icon Picker -->
    <script src="<?= View::url('/public/js/icon-picker.js') ?>"></script>

    <style>
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
            }
        }

        /* Sortable styling */
        .sortable-ghost {
            opacity: 0.4;
            background: #dbeafe;
        }

        .sortable-drag {
            cursor: move !important;
        }

        .dark .sortable-ghost {
            background: #1e3a8a;
        }

        /* Masonry Grid for Widgets using CSS Grid */
        .widgets-masonry-grid {
            display: grid;
            grid-template-columns: 1fr;
            grid-auto-rows: minmax(50px, auto);
            grid-auto-flow: dense;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .widgets-masonry-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .widgets-masonry-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .widget-card {
            /* Auto-fit content height */
            grid-row: span 1;
        }

        /* Widgets with lots of content should span more rows */
        .widget-card.widget-large {
            grid-row: span 2;
        }

        .widget-card.widget-xlarge {
            grid-row: span 3;
        }
    </style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i data-lucide="layout-dashboard" class="w-8 h-8 text-blue-600 dark:text-blue-400"></i>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <?= Security::escape(View::setting('site_title', DEFAULT_SITE_TITLE)) ?>
                    </h1>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- System Health Indicator -->
                    <div id="health-indicator" class="relative group" title="System Health">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 transition-colors cursor-pointer">
                            <i data-lucide="activity" class="w-5 h-5 text-gray-400 animate-pulse" id="health-icon"></i>
                        </div>
                        <!-- Tooltip -->
                        <div id="health-tooltip" class="absolute right-0 top-12 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg p-3 hidden group-hover:block z-50">
                            <div class="text-xs font-medium text-gray-900 dark:text-white mb-2">System Status</div>
                            <div id="health-details" class="text-xs text-gray-600 dark:text-gray-400">
                                Checking...
                            </div>
                        </div>
                    </div>

                    <?php if (Auth::isAdmin()): ?>
                        <a href="<?= View::url('/settings') ?>"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                            Settings
                        </a>
                        <a href="<?= View::url('/logout.php') ?>"
                           class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                            Logout
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Page Navigation Tabs -->
            <?php
            $pages = Page::all();
            if (count($pages) > 1):
            ?>
            <nav class="mt-4 flex space-x-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <?php foreach ($pages as $page): ?>
                    <a href="<?= View::url('/?page=' . Security::escape($page['slug'])) ?>"
                       class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium transition-colors
                              <?= (($_GET['page'] ?? 'main') === $page['slug'])
                                  ? 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200'
                                  : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' ?>">
                        <?php if (($page['icon_type'] ?? 'lucide') === 'custom'): ?>
                            <img src="<?= View::url('/public/icons/' . Security::escape($page['icon'])) ?>"
                                 alt="<?= Security::escape($page['name']) ?>"
                                 class="w-4 h-4 mr-2">
                        <?php else: ?>
                            <i data-lucide="<?= Security::escape($page['icon']) ?>" class="w-4 h-4 mr-2"></i>
                        <?php endif; ?>
                        <?= Security::escape($page['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <?php endif; ?>
        </div>
    </header>

    <!-- Flash Messages -->
    <?php
    $flashMessage = Auth::getFlashMessage();
    if ($flashMessage !== null):
        $alertColors = [
            'success' => 'bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200 border-green-200 dark:border-green-800',
            'error' => 'bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-200 border-red-200 dark:border-red-800',
            'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 border-yellow-200 dark:border-yellow-800',
            'info' => 'bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200 border-blue-200 dark:border-blue-800'
        ];
        $colorClass = $alertColors[$flashMessage['type']] ?? $alertColors['info'];
    ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="<?= $colorClass ?> border rounded-lg p-4" role="alert">
            <p class="font-medium"><?= Security::escape($flashMessage['message']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?= $content ?>
    </main>

    <!-- Password Modal -->
    <div id="password-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <i data-lucide="lock" class="w-5 h-5 mr-2 text-red-500"></i>
                        <span id="modal-title">Password Required</span>
                    </h3>
                    <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    This item is password-protected. Please enter the password to access it.
                </p>
                <form id="password-form" onsubmit="return verifyItemPassword(event)">
                    <input type="hidden" id="modal-item-id" name="item_id">
                    <div class="mb-4">
                        <label for="modal-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                        <input type="password" id="modal-password" name="password" required
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                               placeholder="Enter password">
                    </div>
                    <div id="password-error" class="hidden mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                        <p class="text-sm text-red-600 dark:text-red-400"></p>
                    </div>
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="closePasswordModal()"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                            Cancel
                        </button>
                        <button type="submit" id="password-submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                            Unlock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                <?= Security::escape(APP_NAME) ?> v<?= Security::escape(APP_VERSION) ?>
                <?php if (Auth::isAdmin()): ?>
                    | <a href="<?= View::url('/settings') ?>" class="text-blue-600 dark:text-blue-400 hover:underline">Admin Panel</a>
                <?php endif; ?>
            </p>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();

        // Reason: Adjust widget sizes after icons are loaded (they affect height)
        setTimeout(() => {
            if (window.adjustWidgetSizes) {
                window.adjustWidgetSizes();
            }
        }, 200);

        // System Health Check (runs asynchronously after page load)
        async function checkSystemHealth() {
            const indicator = document.getElementById('health-indicator');
            const icon = document.getElementById('health-icon');
            const details = document.getElementById('health-details');

            if (!indicator || !icon || !details) return;

            try {
                const response = await fetch('<?= View::url('/health/check') ?>');
                const data = await response.json();

                // Update icon based on health status
                if (data.healthy) {
                    // Green - all systems healthy
                    icon.setAttribute('data-lucide', 'check-circle');
                    icon.className = 'w-5 h-5 text-green-500';
                    indicator.querySelector('div').className = 'w-10 h-10 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900/30 transition-colors cursor-pointer';
                } else {
                    // Red - issues detected
                    icon.setAttribute('data-lucide', 'alert-circle');
                    icon.className = 'w-5 h-5 text-red-500';
                    indicator.querySelector('div').className = 'w-10 h-10 rounded-full flex items-center justify-center bg-red-100 dark:bg-red-900/30 transition-colors cursor-pointer';
                }

                // Update tooltip details
                let detailsHtml = '<div class="space-y-1">';
                for (const [key, check] of Object.entries(data.checks)) {
                    const statusIcon = check.status === 'healthy' ? '✓' : (check.status === 'warning' ? '⚠' : '✗');
                    const statusColor = check.status === 'healthy' ? 'text-green-600 dark:text-green-400' : (check.status === 'warning' ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                    detailsHtml += `
                        <div class="flex items-start gap-2">
                            <span class="${statusColor}">${statusIcon}</span>
                            <div class="flex-1">
                                <div class="font-medium">${check.message}</div>
                                <div class="text-gray-500 dark:text-gray-400">${check.details}</div>
                            </div>
                        </div>
                    `;
                }
                detailsHtml += `</div><div class="text-xs text-gray-400 dark:text-gray-500 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">Response: ${data.response_time_ms}ms</div>`;
                details.innerHTML = detailsHtml;

                // Reinitialize Lucide icons
                lucide.createIcons();
            } catch (error) {
                console.error('Health check failed:', error);
                // Yellow - check failed
                icon.setAttribute('data-lucide', 'alert-triangle');
                icon.className = 'w-5 h-5 text-yellow-500';
                indicator.querySelector('div').className = 'w-10 h-10 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900/30 transition-colors cursor-pointer';
                details.innerHTML = '<div class="text-red-600 dark:text-red-400">Health check failed</div>';
                lucide.createIcons();
            }
        }

        // Run health check after page loads (non-blocking)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(checkSystemHealth, 100); // Small delay to not block initial render
            });
        } else {
            setTimeout(checkSystemHealth, 100);
        }

        // Refresh health check every 30 seconds
        setInterval(checkSystemHealth, 30000);

        // Service Status Checker
        async function checkServiceStatuses() {
            const statusIndicators = document.querySelectorAll('.status-indicator');
            if (statusIndicators.length === 0) return;

            // Get current page_slug from URL
            const urlParams = new URLSearchParams(window.location.search);
            const pageSlug = urlParams.get('page') || 'main';

            try {
                const response = await fetch(`<?= View::url('/items/status') ?>?page_slug=${encodeURIComponent(pageSlug)}`);
                const data = await response.json();

                if (data.success) {
                    // Update each status indicator
                    statusIndicators.forEach(indicator => {
                        const itemId = indicator.dataset.itemId;
                        const status = data.statuses[itemId];

                        if (status) {
                            // Remove checking animation
                            indicator.classList.remove('animate-pulse', 'bg-gray-400');

                            // Set color based on status
                            if (status.status === 'up') {
                                indicator.classList.add('bg-green-500');
                                indicator.title = `Online (${status.response_time}ms)`;
                            } else {
                                indicator.classList.add('bg-red-500');
                                indicator.title = `Offline (${status.error || 'unreachable'})`;
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Status check failed:', error);
                // Leave indicators gray if check fails
            }
        }

        // Run status check after page loads (non-blocking)
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(checkServiceStatuses, 200); // 200ms delay
            });
        } else {
            setTimeout(checkServiceStatuses, 200);
        }

        // Auto-refresh status every 60 seconds
        setInterval(checkServiceStatuses, 60000);

        // Auto-dismiss flash messages after 5 seconds
        const alerts = document.querySelectorAll('[role="alert"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });

        <?php if (Auth::isAdmin()): ?>
        // Initialize drag-and-drop for item cards
        document.addEventListener('DOMContentLoaded', function() {
            const grids = document.querySelectorAll('.sortable-grid');

            grids.forEach(grid => {
                new Sortable(grid, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    handle: '.sortable-item',
                    onEnd: function(evt) {
                        // Re-initialize Lucide icons after drag completes
                        lucide.createIcons();

                        // Get the new order of items
                        const items = Array.from(evt.to.querySelectorAll('.sortable-item'));
                        const order = items.map(item => item.dataset.itemId);

                        // Send AJAX request to save new order
                        fetch('<?= View::url('/items/reorder') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                order: order,
                                csrf_token: '<?= Security::getCSRFToken() ?>'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Order saved successfully');
                            } else {
                                console.error('Failed to save order:', data.message);
                                // Optionally reload the page to restore original order
                            }
                        })
                        .catch(error => {
                            console.error('Error saving order:', error);
                        });
                    }
                });
            });

            // Initialize drag-and-drop for widgets
            const widgetsGrid = document.getElementById('widgets-grid');

            // Reason: Auto-calculate widget sizes based on content height for masonry layout
            // Make this function global so it can be called from widget refresh
            window.adjustWidgetSizes = function() {
                if (!widgetsGrid) return;

                const widgets = widgetsGrid.querySelectorAll('.widget-card');
                widgets.forEach(widget => {
                    const height = widget.offsetHeight;

                    // Remove existing size classes
                    widget.classList.remove('widget-large', 'widget-xlarge');

                    // Apply size class based on height (200px per grid row)
                    const rowSpan = Math.ceil(height / 200);
                    widget.style.gridRowEnd = `span ${rowSpan}`;

                    // Add visual classes for styling
                    if (height > 500) {
                        widget.classList.add('widget-xlarge');
                    } else if (height > 300) {
                        widget.classList.add('widget-large');
                    }
                });
            };

            if (widgetsGrid) {
                // Adjust sizes on load and after content changes
                setTimeout(adjustWidgetSizes, 100);
                window.addEventListener('resize', adjustWidgetSizes);

                new Sortable(widgetsGrid, {
                    animation: 150,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    handle: '.widget-drag-handle',
                    onEnd: function(evt) {
                        // Re-initialize Lucide icons after drag completes
                        lucide.createIcons();

                        // Re-adjust widget sizes after drag
                        setTimeout(adjustWidgetSizes, 100);

                        // Get the new order of widgets
                        const widgets = Array.from(evt.to.querySelectorAll('.widget-card'));
                        const order = widgets.map(widget => widget.dataset.widgetId);

                        // Send AJAX request to save new order
                        fetch('<?= View::url('/widgets/reorder') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                order: order,
                                csrf_token: '<?= Security::getCSRFToken() ?>'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Widget order saved successfully');
                            } else {
                                console.error('Failed to save widget order:', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error saving widget order:', error);
                        });
                    }
                });
            }

        });
        <?php endif; ?>

        // Password Modal Functions
        let currentItemUrl = null;
        let currentItemTarget = null;

        function openPasswordModal(itemId, itemTitle, itemUrl, target = '_blank') {
            const modal = document.getElementById('password-modal');
            const modalTitle = document.getElementById('modal-title');
            const itemIdInput = document.getElementById('modal-item-id');
            const passwordInput = document.getElementById('modal-password');
            const errorDiv = document.getElementById('password-error');

            // Set modal data
            modalTitle.textContent = `Unlock: ${itemTitle}`;
            itemIdInput.value = itemId;
            passwordInput.value = '';
            errorDiv.classList.add('hidden');
            currentItemUrl = itemUrl;
            currentItemTarget = target;

            // Show modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Focus password input
            setTimeout(() => passwordInput.focus(), 100);

            // Reinitialize Lucide icons
            lucide.createIcons();
        }

        function closePasswordModal() {
            const modal = document.getElementById('password-modal');
            const errorDiv = document.getElementById('password-error');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            errorDiv.classList.add('hidden');
            currentItemUrl = null;
            currentItemTarget = null;
        }

        async function verifyItemPassword(event) {
            event.preventDefault();

            const itemId = document.getElementById('modal-item-id').value;
            const password = document.getElementById('modal-password').value;
            const submitBtn = document.getElementById('password-submit');
            const errorDiv = document.getElementById('password-error');
            const errorText = errorDiv.querySelector('p');

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying...';

            try {
                const response = await fetch('<?= View::url('/items/verify-password') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        item_id: parseInt(itemId),
                        password: password,
                        csrf_token: '<?= Security::getCSRFToken() ?>'
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Password correct - reload page to show unlocked item
                    closePasswordModal();
                    window.location.reload();
                } else {
                    // Password incorrect - show error
                    errorText.textContent = data.message || 'Incorrect password';
                    errorDiv.classList.remove('hidden');

                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Unlock';
                }
            } catch (error) {
                console.error('Password verification failed:', error);
                errorText.textContent = 'An error occurred. Please try again.';
                errorDiv.classList.remove('hidden');

                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Unlock';
            }
        }

        // Handle clicks on locked items
        document.addEventListener('DOMContentLoaded', function() {
            document.addEventListener('click', function(e) {
                const lockedItem = e.target.closest('.locked-item');
                if (lockedItem) {
                    e.preventDefault();
                    const itemId = lockedItem.dataset.itemId;
                    const itemTitle = lockedItem.dataset.itemTitle;
                    const itemUrl = lockedItem.href;
                    const itemTarget = lockedItem.target || '_blank';
                    openPasswordModal(itemId, itemTitle, itemUrl, itemTarget);
                }
            });

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closePasswordModal();
                }
            });

            // Close modal on backdrop click
            document.getElementById('password-modal')?.addEventListener('click', function(e) {
                if (e.target === this) {
                    closePasswordModal();
                }
            });
        });

        // Widget Auto-Refresh Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const widgets = document.querySelectorAll('.widget-card[data-widget-id]');

            widgets.forEach(widget => {
                const widgetId = widget.dataset.widgetId;
                const refreshInterval = parseInt(widget.dataset.widgetRefresh) * 1000;

                // Only setup refresh if interval > 0
                if (refreshInterval > 0) {
                    // Initial fetch after 2 seconds
                    setTimeout(() => refreshWidget(widgetId), 2000);

                    // Periodic refresh
                    setInterval(() => refreshWidget(widgetId), refreshInterval);
                }
            });

            async function refreshWidget(widgetId) {
                try {
                    const response = await fetch(`<?= View::url('/widgets/fetch-data') ?>?id=${widgetId}`);
                    const result = await response.json();

                    if (result.success && result.html) {
                        // Update widget content
                        const widget = document.querySelector(`.widget-card[data-widget-id="${widgetId}"]`);
                        if (widget) {
                            const contentDiv = widget.querySelector('.widget-content');
                            if (contentDiv) {
                                contentDiv.innerHTML = result.html;
                                // Reinitialize Lucide icons
                                lucide.createIcons();
                                // Reinitialize live clocks if present
                                initializeLiveClocks();
                                // Reason: Recalculate widget sizes after content update
                                setTimeout(() => {
                                    if (window.adjustWidgetSizes) {
                                        window.adjustWidgetSizes();
                                    }
                                }, 50);
                            }
                        }
                    }
                } catch (error) {
                    console.error(`Widget refresh failed (${widgetId}):`, error);
                }
            }

            // Clock widget live updates (client-side, no AJAX)
            function initializeLiveClocks() {
                const clocks = document.querySelectorAll('.live-time[data-timestamp]');

                clocks.forEach(clock => {
                    // Clear existing interval if any
                    if (clock.clockInterval) {
                        clearInterval(clock.clockInterval);
                    }

                    const timezone = clock.dataset.timezone || 'UTC';
                    const format = clock.dataset.format || '24h';
                    const showSeconds = clock.dataset.showSeconds === '1';

                    // Update every second
                    clock.clockInterval = setInterval(() => {
                        try {
                            const now = new Date();

                            // Format time based on preferences
                            let options = {
                                timeZone: timezone,
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: format === '12h'
                            };

                            if (showSeconds) {
                                options.second = '2-digit';
                            }

                            const timeString = now.toLocaleTimeString('en-US', options);
                            clock.textContent = timeString;
                        } catch (error) {
                            console.error('Clock update failed:', error);
                        }
                    }, 1000);
                });
            }

            // Initialize live clocks on page load
            initializeLiveClocks();
        });
    </script>
</body>
</html>
