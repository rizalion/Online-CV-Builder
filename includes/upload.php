<?php
/**
 * File Upload Handler
 * 
 * Validates, renames, and stores uploaded profile pictures.
 */

require_once __DIR__ . '/config.php';

/**
 * Handle a profile picture upload.
 *
 * @param array $file The $_FILES['profile_picture'] entry
 * @return array ['success' => bool, 'path' => string|null, 'errors' => string[]]
 */
function handle_profile_upload(array $file): array
{
    $errors = [];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = get_upload_error_message($file['error']);
        return ['success' => false, 'path' => null, 'errors' => $errors];
    }

    // Validate file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $errors[] = 'File size exceeds the maximum allowed size of 2 MB.';
        return ['success' => false, 'path' => null, 'errors' => $errors];
    }

    // Validate MIME type using finfo (not the unreliable $_FILES['type'])
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_MIME_TYPES, true)) {
        $errors[] = 'Invalid file type. Allowed types: JPG, PNG, GIF.';
        return ['success' => false, 'path' => null, 'errors' => $errors];
    }

    // Verify it's actually an image (prevents disguised PHP files)
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $errors[] = 'The uploaded file is not a valid image.';
        return ['success' => false, 'path' => null, 'errors' => $errors];
    }

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS, true)) {
        $errors[] = 'Invalid file extension. Allowed: jpg, jpeg, png, gif.';
        return ['success' => false, 'path' => null, 'errors' => $errors];
    }

    // Generate unique filename
    $newFilename = 'pfp_' . uniqid('', true) . '.' . $ext;
    $destination = UPLOAD_PATH . $newFilename;

    // Ensure upload directory exists
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }

    // Move the file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $errors[] = 'Failed to save the uploaded file. Please try again.';
        return ['success' => false, 'path' => null, 'errors' => $errors];
    }

    // Return the relative path (stored in DB)
    $relativePath = 'assets/uploads/' . $newFilename;
    return ['success' => true, 'path' => $relativePath, 'errors' => []];
}

/**
 * Delete a previously uploaded file.
 */
function delete_upload(string $relativePath): bool
{
    $fullPath = ROOT_PATH . $relativePath;
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Translate PHP upload error codes to human-readable messages.
 */
function get_upload_error_message(int $errorCode): string
{
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE   => 'The file exceeds the server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'The file exceeds the form upload limit.',
        UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error: missing temp directory.',
        UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'File upload was blocked by a server extension.',
        default               => 'An unknown upload error occurred.',
    };
}
