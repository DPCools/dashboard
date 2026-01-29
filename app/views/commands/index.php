<div class="space-y-8">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Remote Commands</h2>
        <a href="<?= View::url('/settings') ?>"
           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            Back to Settings
        </a>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8">
            <a href="<?= View::url('/commands?tab=hosts') ?>"
               class="<?= ($tab === 'hosts') ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <i data-lucide="server" class="w-4 h-4 inline-block mr-2"></i>
                Hosts
            </a>
            <a href="<?= View::url('/commands?tab=templates') ?>"
               class="<?= ($tab === 'templates') ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <i data-lucide="file-text" class="w-4 h-4 inline-block mr-2"></i>
                Templates
            </a>
            <a href="<?= View::url('/commands?tab=history') ?>"
               class="<?= ($tab === 'history') ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                <i data-lucide="history" class="w-4 h-4 inline-block mr-2"></i>
                History
            </a>
        </nav>
    </div>

    <!-- Hosts Tab -->
    <?php if ($tab === 'hosts'): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Remote Hosts</h3>
            <button onclick="showAddHostModal()"
                    class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Add Host
            </button>
        </div>

        <?php if (empty($hosts)): ?>
        <div class="px-6 py-8 text-center">
            <i data-lucide="server" class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3"></i>
            <p class="text-gray-500 dark:text-gray-400">No hosts configured. Add your first host to get started.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Hostname</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach ($hosts as $host): ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                            <?= Security::escape($host['name']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                <?= Security::escape(strtoupper($host['host_type'])) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <?= Security::escape($host['hostname']) ?>:<?= Security::escape((string)$host['port']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($host['last_connection_status'] === 'success'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> Connected
                            </span>
                            <?php elseif ($host['last_connection_status'] === 'failed'): ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i> Failed
                            </span>
                            <?php else: ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Not Tested
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button onclick="testHost(<?= $host['id'] ?>)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                Test
                            </button>
                            <button onclick="editHost(<?= $host['id'] ?>)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                Edit
                            </button>
                            <button onclick="deleteHost(<?= $host['id'] ?>, '<?= Security::escape($host['name']) ?>')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Templates Tab -->
    <?php if ($tab === 'templates'): ?>
    <div class="space-y-6">
        <?php foreach ($templates as $category => $categoryTemplates): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white capitalize">
                    <?= Security::escape($category) ?> Commands
                </h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <?php foreach ($categoryTemplates as $template): ?>
                <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                <?= Security::escape($template['name']) ?>
                            </h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                <?= Security::escape($template['description'] ?? 'No description') ?>
                            </p>
                            <code class="mt-2 block text-xs bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded font-mono text-gray-700 dark:text-gray-300">
                                <?= Security::escape($template['command_template']) ?>
                            </code>
                        </div>
                        <button onclick='executeTemplate(<?= $template['id'] ?>, <?= json_encode($template['name']) ?>, <?= json_encode($template['command_template']) ?>, <?= $template['parameters'] ?? '[]' ?>, <?= $template['requires_confirmation'] ? 'true' : 'false' ?>)'
                                class="ml-4 inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors">
                            <i data-lucide="play" class="w-4 h-4 mr-1"></i>
                            Execute
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- History Tab -->
    <?php if ($tab === 'history'): ?>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Execution History</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Total: <?= $statistics['total'] ?? 0 ?> executions
                (<?= $statistics['success'] ?? 0 ?> successful, <?= $statistics['failed'] ?? 0 ?> failed)
            </p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Add Host Modal -->
<div id="addHostModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 id="hostModalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">Add Remote Host</h3>
        </div>
        <form id="addHostForm" class="px-6 py-4 space-y-4">
            <input type="hidden" id="host_id" name="id" value="">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Host Name</label>
                <input type="text" name="name" required
                       placeholder="e.g., Proxmox Main"
                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Host Type</label>
                    <select name="host_type" required
                            class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                        <option value="ssh">SSH</option>
                        <option value="proxmox">Proxmox (SSH)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Port</label>
                    <input type="number" name="port" value="22" required
                           class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hostname/IP</label>
                <input type="text" name="hostname" required
                       placeholder="e.g., 192.168.1.100 or pve.example.com"
                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
                <input type="text" name="username" value="root" required
                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Authentication</label>
                <div class="space-y-3">
                    <label class="flex items-center">
                        <input type="radio" name="auth_method" value="password" checked
                               class="mr-2" onchange="toggleAuthFields()">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Password</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="auth_method" value="ssh_key"
                               class="mr-2" onchange="toggleAuthFields()">
                        <span class="text-sm text-gray-700 dark:text-gray-300">SSH Private Key</span>
                    </label>
                </div>
            </div>
            <div id="passwordField">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
                <input type="password" name="password"
                       class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div id="sshKeyField" class="hidden">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">SSH Private Key</label>
                <textarea name="ssh_key" rows="8"
                          placeholder="Paste your private key here (e.g., ~/.ssh/id_rsa)"
                          class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 font-mono text-xs focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <!-- Su Elevation Section -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
                    Privilege Elevation (Optional)
                </h4>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-4">
                    <p class="text-xs text-blue-800 dark:text-blue-200">
                        Enable this if commands need to run as a different user (e.g., root or docker user).
                        After SSH login, the system will use 'su' to switch to the specified user.
                    </p>
                </div>

                <div class="mb-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="use_su_elevation" name="use_su_elevation"
                               onchange="toggleSuFields(this.checked)"
                               class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                        <label for="use_su_elevation" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                            Use su elevation
                        </label>
                    </div>
                </div>

                <div id="su_fields" style="display: none;" class="space-y-3">
                    <div>
                        <label for="su_username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Su Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="su_username" name="su_username"
                               placeholder="root or docker"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Username to switch to (e.g., root, docker, dockeradmin)
                        </p>
                    </div>

                    <div>
                        <label for="su_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Su Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="su_password" name="su_password"
                               placeholder="Password for su user"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Password for the su user (encrypted at rest)
                        </p>
                    </div>

                    <div>
                        <label for="su_shell" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Shell
                        </label>
                        <input type="text" id="su_shell" name="su_shell"
                               value="/bin/bash" placeholder="/bin/bash"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2">
                    </div>
                </div>
            </div>
        </form>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
            <button type="button" onclick="closeAddHostModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </button>
            <button type="button" id="hostModalSubmitBtn" onclick="submitAddHost()"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md text-sm font-medium">
                Add Host
            </button>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Confirm Deletion</h3>
        </div>
        <div class="px-6 py-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Are you sure you want to delete this host? This action cannot be undone.
            </p>
            <p class="mt-2 text-sm font-medium text-gray-900 dark:text-white" id="deleteHostName"></p>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
            <button type="button" onclick="closeDeleteModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancel
            </button>
            <button type="button" onclick="confirmDelete()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm font-medium">
                Delete Host
            </button>
        </div>
    </div>
</div>

<!-- Execute Command Modal -->
<div id="executeModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" id="executeModalTitle">Execute Command</h3>
        </div>
        <form id="executeForm" class="px-6 py-4 space-y-4">
            <input type="hidden" id="executeTemplateId" name="template_id">

            <!-- Template Info -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2">Command Template</h4>
                <code id="executeCommandTemplate" class="text-xs text-blue-800 dark:text-blue-200 font-mono"></code>
            </div>

            <!-- Host Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Host</label>
                <select name="host_id" id="executeHostSelect" required
                        class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500">
                    <option value="">-- Select a host --</option>
                    <?php foreach ($hosts as $host): ?>
                        <option value="<?= $host['id'] ?>"><?= Security::escape($host['name']) ?> (<?= Security::escape($host['hostname']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Parameters Container -->
            <div id="executeParameters"></div>

            <!-- Confirmation Warning -->
            <div id="executeWarning" class="hidden bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-start">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-900 dark:text-yellow-300">Confirmation Required</h4>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">This command requires confirmation before execution.</p>
                    </div>
                </div>
            </div>

            <!-- Execution Results -->
            <div id="executeResults" class="hidden space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Execution Results</h4>
                    <span id="executeStatus" class="px-2 py-1 text-xs font-semibold rounded-full"></span>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                        <span>Exit Code: <span id="executeExitCode" class="font-mono">-</span></span>
                        <span>Time: <span id="executeTime" class="font-mono">-</span>ms</span>
                    </div>
                    <div class="bg-black text-green-400 font-mono text-xs p-3 rounded overflow-x-auto max-h-64 overflow-y-auto">
                        <pre id="executeOutput" class="whitespace-pre-wrap">Waiting for execution...</pre>
                    </div>
                </div>
            </div>
        </form>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
            <button type="button" onclick="closeExecuteModal()"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                <span id="executeCancelText">Cancel</span>
            </button>
            <button type="button" id="executeButton" onclick="submitExecute()"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium">
                <i data-lucide="play" class="w-4 h-4 inline-block mr-1"></i>
                Execute
            </button>
        </div>
    </div>
</div>

<!-- Success/Error Message Toast -->
<div id="messageToast" class="hidden fixed top-4 right-4 z-50 max-w-md">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4">
        <div class="flex items-center">
            <div id="toastIcon" class="mr-3"></div>
            <p id="toastMessage" class="text-sm text-gray-700 dark:text-gray-300"></p>
        </div>
    </div>
</div>

<script>
let deleteHostId = null;
let hostModalMode = 'add'; // 'add' or 'edit'

// Helper function to check for expired session and redirect to login
function checkSessionExpired(data) {
    if (data.expired && data.redirect) {
        // For commands page, redirect back to /commands after login
        const returnUrl = '/commands';

        // Redirect to login with expiry flag and return URL
        window.location.href = data.redirect + '&expired=1&return=' + encodeURIComponent(returnUrl);
        return true;
    }
    return false;
}

function showAddHostModal() {
    hostModalMode = 'add';
    document.getElementById('hostModalTitle').textContent = 'Add Remote Host';
    document.getElementById('hostModalSubmitBtn').textContent = 'Add Host';
    document.getElementById('host_id').value = '';
    document.getElementById('addHostModal').classList.remove('hidden');
    document.querySelector('#addHostForm input[name="name"]').focus();
}

function closeAddHostModal() {
    document.getElementById('addHostModal').classList.add('hidden');
    document.getElementById('addHostForm').reset();
    document.getElementById('host_id').value = '';
    document.querySelector('input[name="password"]').placeholder = '';
    hostModalMode = 'add';
}

function toggleAuthFields() {
    const method = document.querySelector('input[name="auth_method"]:checked').value;
    const passwordField = document.getElementById('passwordField');
    const sshKeyField = document.getElementById('sshKeyField');

    if (method === 'password') {
        passwordField.classList.remove('hidden');
        sshKeyField.classList.add('hidden');
    } else {
        passwordField.classList.add('hidden');
        sshKeyField.classList.remove('hidden');
    }
}

function toggleSuFields(enabled) {
    const suFields = document.getElementById('su_fields');
    suFields.style.display = enabled ? 'block' : 'none';

    // Make fields required when enabled
    document.getElementById('su_username').required = enabled;
    document.getElementById('su_password').required = enabled;
}

function submitAddHost() {
    const form = document.getElementById('addHostForm');
    const hostId = document.getElementById('host_id').value;

    // Client-side validation for su elevation
    const useSuElevation = document.getElementById('use_su_elevation').checked;
    if (useSuElevation) {
        const suUsername = document.getElementById('su_username').value.trim();
        const suPassword = document.getElementById('su_password').value.trim();

        // Only require su_password for new hosts or when changed
        if (!suUsername || (!suPassword && !hostId)) {
            showMessage('Su username and password are required when su elevation is enabled', 'error');
            return;
        }
    }

    const formData = new FormData(form);
    formData.append('csrf_token', '<?= Security::getCsrfToken() ?>');

    // Remove empty auth fields
    const authMethod = document.querySelector('input[name="auth_method"]:checked').value;
    if (authMethod === 'password') {
        formData.delete('ssh_key');
    } else {
        formData.delete('password');
    }

    // Determine endpoint based on mode
    const endpoint = hostId
        ? '<?= View::url('/commands/hosts/update') ?>'
        : '<?= View::url('/commands/hosts/create') ?>';

    const successMessage = hostId ? 'Host updated successfully!' : 'Host added successfully!';

    fetch(endpoint, {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(r => {
        if (!r.ok) {
            return r.text().then(text => {
                console.error('Server response:', text);
                try {
                    const json = JSON.parse(text);
                    throw new Error(json.error || 'Server returned error ' + r.status);
                } catch (e) {
                    throw new Error('Server error: ' + r.status + ' - ' + text.substring(0, 200));
                }
            });
        }
        return r.json();
    })
    .then(data => {
        if (checkSessionExpired(data)) return;

        if (data.success) {
            showMessage(successMessage, 'success');
            closeAddHostModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage('Error: ' + data.error, 'error');
        }
    })
    .catch(err => {
        console.error('Full error:', err);
        showMessage('Error: ' + err.message, 'error');
    });
}

function editHost(hostId) {
    hostModalMode = 'edit';

    // Update modal title and button
    document.getElementById('hostModalTitle').textContent = 'Edit Remote Host';
    document.getElementById('hostModalSubmitBtn').textContent = 'Update Host';

    // Fetch host data and populate form
    fetch('<?= View::url('/commands/hosts/get?id=') ?>' + hostId, {
        credentials: 'include'
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            showMessage('Error loading host: ' + data.error, 'error');
            return;
        }

        const host = data.host;
        const settings = JSON.parse(host.connection_settings || '{}');

        // Populate form fields
        document.getElementById('host_id').value = host.id;
        document.querySelector('input[name="name"]').value = host.name;
        document.querySelector('select[name="host_type"]').value = host.host_type;
        document.querySelector('input[name="port"]').value = host.port;
        document.querySelector('input[name="hostname"]').value = host.hostname;
        document.querySelector('input[name="username"]').value = host.username;

        // Determine auth method (check if has_ssh_key field exists)
        if (host.has_ssh_key) {
            document.querySelector('input[name="auth_method"][value="ssh_key"]').checked = true;
            toggleAuthFields();
            document.querySelector('textarea[name="ssh_key"]').placeholder = 'Leave blank to keep existing SSH key';
        } else {
            document.querySelector('input[name="auth_method"][value="password"]').checked = true;
            toggleAuthFields();
        }

        // Su elevation settings
        if (settings.use_su_elevation) {
            document.getElementById('use_su_elevation').checked = true;
            toggleSuFields(true);
            document.getElementById('su_username').value = settings.su_username || '';
            document.getElementById('su_shell').value = settings.su_shell || '/bin/bash';
            document.getElementById('su_password').placeholder = 'Leave blank to keep existing password';
        } else {
            // IMPORTANT: Explicitly uncheck if disabled to prevent state carryover
            document.getElementById('use_su_elevation').checked = false;
            toggleSuFields(false);
            document.getElementById('su_username').value = '';
            document.getElementById('su_password').value = '';
            document.getElementById('su_shell').value = '/bin/bash';
        }

        // Show placeholder for existing credentials
        document.querySelector('input[name="password"]').placeholder = 'Leave blank to keep existing password';

        // Show modal
        document.getElementById('addHostModal').classList.remove('hidden');
    })
    .catch(err => {
        showMessage('Error: ' + err.message, 'error');
    });
}

function testHost(hostId) {
    showMessage('Testing connection...', 'info');

    const formData = new FormData();
    formData.append('host_id', hostId);
    formData.append('csrf_token', '<?= Security::getCsrfToken() ?>');

    fetch('<?= View::url('/commands/hosts/test') ?>', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(r => r.json())
    .then(data => {
        if (checkSessionExpired(data)) return;

        if (data.success) {
            const message = data.message || ('Connection successful! (' + data.time_ms + 'ms)');
            showMessage(message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage('Connection failed: ' + data.error, 'error');
        }
    })
    .catch(err => {
        showMessage('Error: ' + err.message, 'error');
    });
}

function deleteHost(hostId, hostName) {
    deleteHostId = hostId;
    document.getElementById('deleteHostName').textContent = hostName;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteHostId = null;
}

function confirmDelete() {
    if (!deleteHostId) return;

    const formData = new FormData();
    formData.append('id', deleteHostId);
    formData.append('csrf_token', '<?= Security::getCsrfToken() ?>');

    fetch('<?= View::url('/commands/hosts/delete') ?>', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(r => r.json())
    .then(data => {
        if (checkSessionExpired(data)) return;

        if (data.success) {
            showMessage('Host deleted successfully', 'success');
            closeDeleteModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage('Error: ' + data.error, 'error');
        }
    })
    .catch(err => {
        showMessage('Error: ' + err.message, 'error');
    });
}

function executeTemplate(templateId, templateName, commandTemplate, parameters, requiresConfirmation) {
    // Store template data
    document.getElementById('executeTemplateId').value = templateId;
    document.getElementById('executeModalTitle').textContent = 'Execute: ' + templateName;
    document.getElementById('executeCommandTemplate').textContent = commandTemplate;

    // Show/hide confirmation warning
    if (requiresConfirmation) {
        document.getElementById('executeWarning').classList.remove('hidden');
    } else {
        document.getElementById('executeWarning').classList.add('hidden');
    }

    // Build parameter fields
    const paramsContainer = document.getElementById('executeParameters');
    paramsContainer.innerHTML = '';

    if (parameters && parameters.length > 0) {
        const paramsDiv = document.createElement('div');
        paramsDiv.className = 'space-y-3';

        const heading = document.createElement('h4');
        heading.className = 'text-sm font-medium text-gray-700 dark:text-gray-300 mb-2';
        heading.textContent = 'Parameters';
        paramsDiv.appendChild(heading);

        parameters.forEach(param => {
            const fieldDiv = document.createElement('div');

            const label = document.createElement('label');
            label.className = 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2';
            label.textContent = param.label || param.name;
            if (param.required) {
                const req = document.createElement('span');
                req.className = 'text-red-500 ml-1';
                req.textContent = '*';
                label.appendChild(req);
            }
            fieldDiv.appendChild(label);

            let input;
            if (param.type === 'integer') {
                input = document.createElement('input');
                input.type = 'number';
                input.name = param.name;
                input.className = 'w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500';
                input.required = param.required || false;
                input.placeholder = param.default || '';
                if (param.min !== undefined) input.min = param.min;
                if (param.max !== undefined) input.max = param.max;
                if (param.default !== undefined) input.value = param.default;
            } else if (param.type === 'enum') {
                input = document.createElement('select');
                input.name = param.name;
                input.className = 'w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500';
                input.required = param.required || false;

                const emptyOption = document.createElement('option');
                emptyOption.value = '';
                emptyOption.textContent = '-- Select --';
                input.appendChild(emptyOption);

                if (param.options) {
                    param.options.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt;
                        option.textContent = opt;
                        input.appendChild(option);
                    });
                }
            } else {
                input = document.createElement('input');
                input.type = 'text';
                input.name = param.name;
                input.className = 'w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-4 py-2 focus:border-blue-500 focus:ring-blue-500';
                input.required = param.required || false;
                input.placeholder = param.default || '';
                if (param.pattern) input.pattern = param.pattern;
                if (param.default !== undefined) input.value = param.default;
            }

            fieldDiv.appendChild(input);
            paramsDiv.appendChild(fieldDiv);
        });

        paramsContainer.appendChild(paramsDiv);
    }

    // Reset results
    document.getElementById('executeResults').classList.add('hidden');
    document.getElementById('executeOutput').textContent = 'Waiting for execution...';
    document.getElementById('executeButton').disabled = false;

    // Show modal
    document.getElementById('executeModal').classList.remove('hidden');

    // Re-init icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Focus on host select
    document.getElementById('executeHostSelect').focus();
}

function closeExecuteModal() {
    document.getElementById('executeModal').classList.add('hidden');
    document.getElementById('executeForm').reset();
}

function submitExecute() {
    const templateId = document.getElementById('executeTemplateId').value;
    const hostId = document.getElementById('executeHostSelect').value;

    if (!hostId) {
        showMessage('Please select a host', 'error');
        return;
    }

    // Gather parameters
    const parameters = {};
    const paramInputs = document.querySelectorAll('#executeParameters input, #executeParameters select');
    paramInputs.forEach(input => {
        if (input.value) {
            parameters[input.name] = input.value;
        }
    });

    // Disable execute button
    document.getElementById('executeButton').disabled = true;
    document.getElementById('executeButton').innerHTML = '<i data-lucide="loader" class="w-4 h-4 inline-block mr-1 animate-spin"></i> Executing...';

    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Show results section
    document.getElementById('executeResults').classList.remove('hidden');
    document.getElementById('executeOutput').textContent = 'Executing command...';

    const formData = new FormData();
    formData.append('template_id', templateId);
    formData.append('host_id', hostId);
    formData.append('parameters', JSON.stringify(parameters));
    formData.append('csrf_token', '<?= Security::getCsrfToken() ?>');

    fetch('<?= View::url('/commands/execute') ?>', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    })
    .then(r => r.json())
    .then(data => {
        // Check if session expired - force redirect to login
        if (data.expired && data.redirect) {
            alert('Your session has expired. Please log in again.');
            window.location.href = data.redirect;
            return;
        }

        if (data.success) {
            // Success
            document.getElementById('executeStatus').textContent = 'SUCCESS';
            document.getElementById('executeStatus').className = 'px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            document.getElementById('executeExitCode').textContent = data.result.exit_code;
            document.getElementById('executeTime').textContent = data.result.execution_time_ms;

            const output = data.result.stdout || data.result.stderr || '(no output)';
            document.getElementById('executeOutput').textContent = output;

            showMessage('Command executed successfully!', 'success');
            document.getElementById('executeCancelText').textContent = 'Close';
        } else {
            // Failed
            document.getElementById('executeStatus').textContent = 'FAILED';
            document.getElementById('executeStatus').className = 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';

            if (data.result) {
                document.getElementById('executeExitCode').textContent = data.result.exit_code || '-';
                document.getElementById('executeTime').textContent = data.result.execution_time_ms || '-';
                document.getElementById('executeOutput').textContent = data.result.stderr || data.error;
            } else {
                document.getElementById('executeOutput').textContent = data.error;
            }

            showMessage('Command execution failed: ' + data.error, 'error');
        }

        // Re-enable button
        document.getElementById('executeButton').disabled = false;
        document.getElementById('executeButton').innerHTML = '<i data-lucide="play" class="w-4 h-4 inline-block mr-1"></i> Execute Again';

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    })
    .catch(err => {
        document.getElementById('executeStatus').textContent = 'ERROR';
        document.getElementById('executeStatus').className = 'px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
        document.getElementById('executeOutput').textContent = 'Network error: ' + err.message;
        showMessage('Error: ' + err.message, 'error');

        document.getElementById('executeButton').disabled = false;
        document.getElementById('executeButton').innerHTML = '<i data-lucide="play" class="w-4 h-4 inline-block mr-1"></i> Execute Again';

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });
}

function showMessage(message, type) {
    const toast = document.getElementById('messageToast');
    const icon = document.getElementById('toastIcon');
    const messageEl = document.getElementById('toastMessage');

    // Set icon based on type
    if (type === 'success') {
        icon.innerHTML = '<i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>';
    } else if (type === 'error') {
        icon.innerHTML = '<i data-lucide="x-circle" class="w-5 h-5 text-red-600"></i>';
    } else if (type === 'info') {
        icon.innerHTML = '<i data-lucide="info" class="w-5 h-5 text-blue-600"></i>';
    }

    messageEl.textContent = message;
    toast.classList.remove('hidden');

    // Re-initialize lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Auto-hide after 5 seconds
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 5000);
}

// Close modals on Escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAddHostModal();
        closeDeleteModal();
        closeExecuteModal();
    }
});

// Close modals on outside click
document.getElementById('addHostModal').addEventListener('click', (e) => {
    if (e.target.id === 'addHostModal') {
        closeAddHostModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', (e) => {
    if (e.target.id === 'deleteModal') {
        closeDeleteModal();
    }
});

document.getElementById('executeModal').addEventListener('click', (e) => {
    if (e.target.id === 'executeModal') {
        closeExecuteModal();
    }
});
</script>
