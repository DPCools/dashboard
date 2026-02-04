<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/helpers/Auth.php';
require_once BASE_PATH . '/app/helpers/View.php';
require_once BASE_PATH . '/app/helpers/Security.php';
require_once BASE_PATH . '/app/helpers/FileHelper.php';
require_once BASE_PATH . '/app/models/SharedFile.php';
require_once BASE_PATH . '/app/models/SharedFileAccessLog.php';

/**
 * FileShareController - Manages file sharing system
 */
class FileShareController
{
    /**
     * Display file list (admin only)
     */
    public function index(): void
    {
        Auth::requireAdmin();

        $files = SharedFile::getAll();
        $stats = SharedFile::getStatistics();
        $storageInfo = FileHelper::checkStorageLimit(0);

        View::render('fileshare/index', [
            'title' => 'File Sharing',
            'files' => $files,
            'stats' => $stats,
            'storage_info' => $storageInfo
        ]);
    }

    /**
     * Display upload form (admin only)
     */
    public function upload(): void
    {
        Auth::requireAdmin();

        View::render('fileshare/upload', [
            'title' => 'Upload File'
        ]);
    }

    /**
     * Handle file upload (admin only)
     */
    public function store(): void
    {
        Auth::requireAdmin();

        // Detect AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Helper function to handle errors
        $handleError = function(string $message) use ($isAjax) {
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => $message]);
                exit;
            } else {
                $_SESSION['error'] = $message;
                View::redirect(View::url('/files/upload'));
            }
        };

        // Verify CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $handleError('Invalid security token. Please refresh the page and try again.');
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
            $handleError('Please select a file to upload');
            return;
        }

        // Validate file
        $validation = FileHelper::validateFile($_FILES['file']);
        if (!$validation['success']) {
            $handleError($validation['error']);
            return;
        }

        // Check storage limit
        $storageCheck = FileHelper::checkStorageLimit($_FILES['file']['size']);
        if (!$storageCheck['within_limit']) {
            $handleError('Storage limit exceeded. Available: ' . FileHelper::formatFileSize($storageCheck['available']));
            return;
        }

        // Generate unique identifiers
        $token = FileHelper::generateToken();
        $storedFilename = FileHelper::generateStoredFilename($_FILES['file']['name']);

        // Save file to storage
        $saveResult = FileHelper::saveFile($_FILES['file'], $storedFilename);
        if (!$saveResult['success']) {
            $handleError($saveResult['error']);
            return;
        }

        // Calculate expiration
        $expirationPreset = $_POST['expiration'] ?? '30d';
        $expiresAt = FileHelper::calculateExpiration($expirationPreset);

        // Hash password if provided
        $passwordHash = null;
        if (!empty($_POST['password'])) {
            $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $saveResult['path']);
        finfo_close($finfo);

        // Create database record
        try {
            SharedFile::create([
                'token' => $token,
                'stored_filename' => $storedFilename,
                'original_filename' => $_FILES['file']['name'],
                'file_size' => $_FILES['file']['size'],
                'mime_type' => $mimeType,
                'description' => trim($_POST['description'] ?? ''),
                'expires_at' => $expiresAt,
                'password_hash' => $passwordHash,
                'uploaded_by' => 'admin'
            ]);

            $_SESSION['success'] = 'File uploaded successfully!';
            $_SESSION['share_url'] = SharedFile::getShareUrl($token);

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'File uploaded successfully!',
                    'share_url' => SharedFile::getShareUrl($token),
                    'redirect' => View::url('/files')
                ]);
                exit;
            } else {
                View::redirect(View::url('/files'));
            }
        } catch (Exception $e) {
            // Clean up file if database insert fails
            FileHelper::deleteFile($storedFilename);
            $handleError('Failed to save file: ' . $e->getMessage());
        }
    }

    /**
     * Display edit form (admin only)
     */
    public function edit(): void
    {
        Auth::requireAdmin();

        $id = (int) ($_GET['id'] ?? 0);
        $file = SharedFile::find($id);

        if ($file === null) {
            $_SESSION['error'] = 'File not found';
            View::redirect(View::url('/files'));
            return;
        }

        View::render('fileshare/edit', [
            'title' => 'Edit File',
            'file' => $file
        ]);
    }

    /**
     * Handle file metadata update (admin only)
     */
    public function update(): void
    {
        Auth::requireAdmin();

        // Verify CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid security token';
            View::redirect(View::url('/files'));
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $file = SharedFile::find($id);

        if ($file === null) {
            $_SESSION['error'] = 'File not found';
            View::redirect(View::url('/files'));
            return;
        }

        // Prepare update data
        $updateData = [
            'description' => trim($_POST['description'] ?? '')
        ];

        // Update expiration
        if (isset($_POST['expiration'])) {
            $updateData['expires_at'] = FileHelper::calculateExpiration($_POST['expiration']);
        }

        // Update password if provided
        if (isset($_POST['password'])) {
            if (!empty($_POST['password'])) {
                $updateData['password_hash'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            } elseif (isset($_POST['remove_password'])) {
                $updateData['password_hash'] = null;
            }
        }

        if (SharedFile::update($id, $updateData)) {
            $_SESSION['success'] = 'File updated successfully';
        } else {
            $_SESSION['error'] = 'Failed to update file';
        }

        View::redirect(View::url('/files'));
    }

    /**
     * Delete a file (admin only)
     */
    public function delete(): void
    {
        Auth::requireAdmin();

        // Verify CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid security token';
            View::redirect(View::url('/files'));
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $file = SharedFile::find($id);

        if ($file === null) {
            $_SESSION['error'] = 'File not found';
            View::redirect(View::url('/files'));
            return;
        }

        // Delete file from filesystem
        if (FileHelper::deleteFile($file['stored_filename'])) {
            // Delete database record (CASCADE will remove access logs)
            if (SharedFile::delete($id)) {
                $_SESSION['success'] = 'File deleted successfully';
            } else {
                $_SESSION['error'] = 'Failed to delete file record';
            }
        } else {
            $_SESSION['error'] = 'Failed to delete file from storage';
        }

        View::redirect(View::url('/files'));
    }

    /**
     * Manual cleanup of expired files (admin only)
     */
    public function cleanup(): void
    {
        Auth::requireAdmin();

        // Verify CSRF token
        if (!Security::validateCSRFToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = 'Invalid security token';
            View::redirect(View::url('/files'));
            return;
        }

        $result = SharedFile::cleanupExpired();

        if ($result['deleted_files'] > 0) {
            $_SESSION['success'] = sprintf(
                'Cleaned up %d expired file(s), freed %s',
                $result['deleted_files'],
                FileHelper::formatFileSize($result['freed_space'])
            );
        } else {
            $_SESSION['info'] = 'No expired files to clean up';
        }

        View::redirect(View::url('/files'));
    }

    /**
     * Show file info or password prompt (public)
     */
    public function show(): void
    {
        $token = $_GET['token'] ?? '';
        $file = SharedFile::findByToken($token);

        if ($file === null) {
            View::render('errors/404', ['title' => 'File Not Found']);
            return;
        }

        // Check if expired
        if (SharedFile::isExpired($file)) {
            SharedFileAccessLog::create($file['id'], 'expired', null, null);
            View::render('errors/404', ['title' => 'File Expired']);
            return;
        }

        // Update access time
        SharedFile::updateAccessTime($file['id']);

        // Log view
        SharedFileAccessLog::create($file['id'], 'view', null, null);

        // Check if password protected and not unlocked
        $needsPassword = SharedFile::isPasswordProtected($file) && !SharedFile::isUnlocked($file['id']);

        View::render('fileshare/view', [
            'title' => $file['original_filename'],
            'file' => $file,
            'needs_password' => $needsPassword,
            'share_url' => SharedFile::getShareUrl($token)
        ]);
    }

    /**
     * Verify password for file access (AJAX, public)
     */
    public function verifyPassword(): void
    {
        header('Content-Type: application/json');

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';

        $file = SharedFile::findByToken($token);

        if ($file === null) {
            View::json(['success' => false, 'error' => 'File not found']);
            return;
        }

        // Verify password
        if (SharedFile::verifyPassword($file, $password)) {
            // Unlock in session
            SharedFile::unlock($file['id']);

            // Log successful access
            SharedFileAccessLog::create($file['id'], 'unlocked', null, null);

            View::json(['success' => true]);
        } else {
            // Log failed password attempt
            SharedFileAccessLog::create($file['id'], 'failed_password', null, null);

            View::json(['success' => false, 'error' => 'Incorrect password']);
        }
    }

    /**
     * Download a file (public)
     */
    public function download(): void
    {
        $token = $_GET['token'] ?? '';
        $file = SharedFile::findByToken($token);

        if ($file === null) {
            http_response_code(404);
            echo 'File not found';
            return;
        }

        // Check if expired
        if (SharedFile::isExpired($file)) {
            http_response_code(410);
            echo 'File has expired';
            return;
        }

        // Check if password protected and not unlocked
        if (SharedFile::isPasswordProtected($file) && !SharedFile::isUnlocked($file['id'])) {
            http_response_code(403);
            echo 'Password required';
            return;
        }

        // Get file path
        $filepath = FileHelper::getStoragePath($file['stored_filename']);

        if (!file_exists($filepath)) {
            http_response_code(404);
            echo 'File not found on disk';
            return;
        }

        // Increment download count
        SharedFile::incrementDownloadCount($file['id']);

        // Log download
        SharedFileAccessLog::create($file['id'], 'download', null, null);

        // Send file headers
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Length: ' . $file['file_size']);
        header('Content-Disposition: attachment; filename="' . $file['original_filename'] . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output file
        readfile($filepath);
        exit;
    }
}
