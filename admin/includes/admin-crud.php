<?php

function admin_require_auth(): void
{
    if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function admin_slug(string $value): string
{
    $slug = preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($value)));
    return trim((string)$slug, '-');
}

function admin_upload_image(string $field, string $folder, ?string $existing = null): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $existing;
    }

    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed. Please choose the file again.');
    }

    if (($_FILES[$field]['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be 5MB or smaller.');
    }

    $tmp_path = $_FILES[$field]['tmp_name'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp_path);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Only JPG, PNG, WEBP, and GIF images are allowed.');
    }

    $upload_dir = __DIR__ . '/../../assets/uploads/' . trim($folder, '/');
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0775, true);
    }

    $base_name = admin_slug(pathinfo($_FILES[$field]['name'], PATHINFO_FILENAME)) ?: 'image';
    $filename = $base_name . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp_path, $destination)) {
        throw new RuntimeException('Could not save uploaded image.');
    }

    return 'assets/uploads/' . trim($folder, '/') . '/' . $filename;
}

function admin_delete_local_upload(?string $path): void
{
    if (!$path || !str_starts_with($path, 'assets/uploads/')) {
        return;
    }

    $absolute = realpath(__DIR__ . '/../../' . $path);
    $uploads_root = realpath(__DIR__ . '/../../assets/uploads');
    if ($absolute && $uploads_root && str_starts_with($absolute, $uploads_root) && is_file($absolute)) {
        @unlink($absolute);
    }
}
