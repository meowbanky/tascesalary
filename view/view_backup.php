<?php
try {
    include_once(__DIR__ . '/../backup.php');
    
    // Initialize backup object (uses config constants automatically)
    $backup = new DatabaseBackup();
    
    // Get list of existing backups
    $backups = $backup->getBackupList();
    
    // Ensure $backups is an array
    if (!is_array($backups)) {
        $backups = [];
    }
    
} catch (Exception $e) {
    $backups = [];
    $error_message = $e->getMessage();
}
?>

<!-- Page Header -->
<div class="mb-6">
    <div class="flex items-center mb-2">
        <i class="fas fa-database text-blue-600 text-xl mr-3"></i>
        <h1 class="text-2xl font-bold text-gray-900">Database Backup</h1>
    </div>
    <p class="text-gray-600">Manage database backups</p>
</div>

<!-- Error Message -->
<?php if (isset($error_message)): ?>
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <div class="flex items-center">
        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
        <span class="text-red-800 font-medium">Error:</span>
        <span class="text-red-700 ml-2"><?php echo htmlspecialchars($error_message); ?></span>
    </div>
</div>
<?php endif; ?>

<!-- Backup Actions Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Backup Actions</h2>
    <div class="flex gap-3">
        <button id="create-backup-btn"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Create New Backup
        </button>
        <button id="refresh-list-btn"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center transition-colors">
            <i class="fas fa-refresh mr-2"></i>
            Refresh List
        </button>
    </div>
</div>

<!-- Backup Progress -->
<div id="backup-progress" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6"
    style="display: none !important;">
    <div class="flex items-center">
        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-3"></div>
        <span class="text-blue-800 font-medium">Creating backup... Please wait.</span>
    </div>
    <div id="backup-log" class="mt-3 text-sm text-blue-700 bg-blue-100 rounded p-2"
        style="max-height: 150px; overflow-y: auto;">
    </div>
</div>

<!-- Available Backups Card -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Available Backups</h2>

    <?php if (empty($backups)): ?>
    <div class="text-center py-8">
        <i class="fas fa-database text-gray-400 text-4xl mb-4"></i>
        <p class="text-gray-500">No backups found. Create your first backup to get started.</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-4 font-medium text-gray-700">Backup File</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-700">Size</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-700">Created</th>
                    <th class="text-left py-3 px-4 font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($backups as $backup_item): ?>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3 px-4">
                        <div class="flex items-center">
                            <i class="fas fa-file-archive text-blue-500 mr-2"></i>
                            <span
                                class="font-mono text-sm text-gray-900"><?php echo htmlspecialchars($backup_item['filename']); ?></span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-gray-600"><?php echo $backup_item['size']; ?></td>
                    <td class="py-3 px-4 text-gray-600"><?php echo $backup_item['date']; ?></td>
                    <td class="py-3 px-4">
                        <div class="flex gap-2">
                            <a href="../backup.php?action=download&file=<?php echo urlencode($backup_item['filename']); ?>"
                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm flex items-center transition-colors">
                                <i class="fas fa-download mr-1"></i>
                                Download
                            </a>
                            <button onclick="delete_backup('<?php echo htmlspecialchars($backup_item['filename']); ?>')"
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm flex items-center transition-colors">
                                <i class="fas fa-trash mr-1"></i>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Backup Information -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Backup Information</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Backup Retention -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-clock text-blue-600 text-xl mr-3"></i>
                <div>
                    <h3 class="text-blue-800 font-medium mb-1">Backup Retention</h3>
                    <p class="text-blue-600"><?php echo MAX_BACKUP_AGE; ?> days</p>
                </div>
            </div>
        </div>

        <!-- Compression -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-compress text-green-600 text-xl mr-3"></i>
                <div>
                    <h3 class="text-green-800 font-medium mb-1">Compression</h3>
                    <p class="text-green-600">GZIP (Level 9)</p>
                </div>
            </div>
        </div>

        <!-- Memory Limit -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-memory text-purple-600 text-xl mr-3"></i>
                <div>
                    <h3 class="text-purple-800 font-medium mb-1">Memory Limit</h3>
                    <p class="text-purple-600"><?php echo MAX_MEMORY_USAGE; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Excluded Tables -->
    <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <i class="fas fa-ban text-yellow-600 text-xl mr-3"></i>
                <div>
                    <h3 class="text-yellow-800 font-medium mb-1">Excluded Tables</h3>
                    <p class="text-yellow-600"><?php echo implode(', ', EXCLUDED_TABLES); ?></p>
                </div>
            </div>
        </div>

<style>
/* Fix SweetAlert button visibility - override app.scss transparent background */
.swal2-actions {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.swal2-confirm,
.swal2-cancel {
    opacity: 1 !important;
    visibility: visible !important;
    background-color: #3085d6 !important; /* Override transparent background */
    color: white !important;
    border: none !important;
    border-radius: 0.25rem !important;
    padding: 0.5rem 1rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    transition: background-color 0.2s !important;
}

.swal2-confirm:hover {
    background-color: #2563eb !important;
}

.swal2-cancel {
    background-color: #dc2626 !important;
    margin-left: 0.5rem !important;
}

.swal2-cancel:hover {
    background-color: #b91c1c !important;
}
</style>

        <script>
        $(document).ready(function() {
            console.log('Document ready - jQuery is working');

            // Ensure loading state is hidden on page load
            $('#backup-progress').hide();
            console.log('Backup progress element hidden');

            // Check if SweetAlert is loaded
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert is not loaded!');
                alert('SweetAlert library is not loaded. Please refresh the page.');
                return;
            }

            console.log('SweetAlert version:', Swal.version);
            console.log('All libraries loaded successfully');

            // Create backup button
            $('#create-backup-btn').click(function() {
                const btn = $(this);
                const originalText = btn.html();

                console.log('Create backup button clicked');

                // Show confirmation
                Swal.fire({
                    title: 'Create Database Backup?',
                    text: 'This will create a new backup of the entire database. This process may take several minutes.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, create backup!',
                    cancelButtonText: 'Cancel',
                    allowOutsideClick: false,
                    allowEscapeKey: true,
                    focusConfirm: false,
                    reverseButtons: false
                }).then((result) => {
                    console.log('SweetAlert result:', result);
                    if (result.isConfirmed) {
                        console.log('User confirmed backup creation');
                        create_backup();
                    } else {
                        console.log('User cancelled backup creation');
                    }
                }).catch((error) => {
                    console.error('SweetAlert error:', error);
                });
            });

            // Refresh list button
            $('#refresh-list-btn').click(function() {
                $('#loadContent').load('view/view_backup.php');
            });
        });

        function create_backup() {
            const btn = $('#create-backup-btn');
            const originalText = btn.html();

            // Disable button and show progress
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Creating Backup...');

            // Check if elements exist before manipulating them
            const progressElement = $('#backup-progress');
            const logElement = $('#backup-log');

            if (progressElement.length) {
                progressElement.show();
            }

            if (logElement.length) {
                logElement.html('');
            }

            // Make AJAX request
            $.ajax({
                url: '../backup.php',
                type: 'POST',
                data: {
                    action: 'create_backup'
                },
                dataType: 'json',
                success: function(response) {
                    // Hide loading immediately on success
                    if (progressElement.length) {
                        progressElement.hide();
                    }

                    // Show log output in progress area
                    if (response.log && logElement.length) {
                        logElement.html(response.log.replace(/\n/g, '<br>'));
                    }

                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Backup Created Successfully!',
                            html: `
                        <div class="text-start">
                            <p><strong>File:</strong> ${response.filename}</p>
                            <p><strong>Message:</strong> ${response.message}</p>
                            <div class="mt-3">
                                <strong>Backup Log:</strong>
                                <div class="bg-light p-2 rounded mt-1" style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                                    ${response.log ? response.log.replace(/\n/g, '<br>') : 'No log available'}
                                </div>
                            </div>
                        </div>
                    `,
                            confirmButtonText: 'OK',
                            width: '600px'
                        }).then(() => {
                            // Reload the page content
                            $('#loadContent').load('view/view_backup.php');
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Backup Failed',
                            text: response.message
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading immediately on error
                    if (progressElement.length) {
                        progressElement.hide();
                    }

                    console.log('AJAX Error:', xhr.responseText);
                    console.log('Status:', status);
                    console.log('Error:', error);

                    let errorMessage = 'An error occurred while creating the backup.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                        if (response.error_details) {
                            console.log('Error Details:', response.error_details);
                        }
                    } catch (e) {
                        console.log('Could not parse error response');
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Backup Failed',
                        text: errorMessage,
                        footer: 'Check the browser console for more details'
                    });
                },
                complete: function() {
                    // Re-enable button (loading is hidden in success/error callbacks)
                    btn.prop('disabled', false).html(originalText);
                }
            });
        }

        function delete_backup(filename) {
            if (!filename || filename.trim() === '') {
                console.error('Empty filename passed to delete_backup function');
                return;
            }

            Swal.fire({
                title: 'Delete Backup?',
                text: `Are you sure you want to delete "${filename}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading state
                    Swal.fire({
                        title: 'Deleting Backup...',
                        text: 'Please wait while the backup is being deleted.',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Make AJAX request to delete backup
                    $.ajax({
                        url: '../backup.php',
                        type: 'POST',
                        data: {
                            action: 'delete_backup',
                            file: filename
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Backup Deleted!',
                                    text: response.message,
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    // Reload the page content to refresh the backup list
                                    $('#loadContent').load('view/view_backup.php');
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Delete Failed',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Delete error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Delete Failed',
                                text: 'An error occurred while deleting the backup. Please try again.',
                                footer: 'Check the browser console for more details'
                            });
                        }
                    });
                }
            });
        }
        </script>