<?php
require_once 'db_connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

$userId = intval($_SESSION['user_id']);

if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['avatar'];
$maxSize = 2 * 1024 * 1024; // 2MB
$allowed = ['image/jpeg', 'image/png', 'image/webp'];

if ($file['size'] > $maxSize) {
    echo json_encode(['status' => 'error', 'message' => 'File too large (max 2MB)']);
    exit();
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
if (!in_array($mime, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Unsupported file type']);
    exit();
}

$ext = '';
switch ($mime) {
    case 'image/jpeg': $ext = 'jpg'; break;
    case 'image/png': $ext = 'png'; break;
    case 'image/webp': $ext = 'webp'; break;
}

$uploadDir = __DIR__ . '/../uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$targetFilename = $userId . '.' . $ext;
$targetPath = $uploadDir . $targetFilename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save file']);
    exit();
}

// Optional: update users table with avatar filename if column exists
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM users LIKE 'avatar'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $stmt2 = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt2->execute([$targetFilename, $userId]);
    }
} catch (Exception $e) {
    // ignore DB update errors
}

// Return URL relative to webroot
$publicUrl = 'uploads/avatars/' . $targetFilename;

echo json_encode(['status' => 'success', 'message' => 'Avatar uploaded', 'url' => $publicUrl]);
exit();
