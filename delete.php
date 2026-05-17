<?php
// ============================================================
// SHULKER — Delete Handler
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security.php';

send_security_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed.'], 405);
}

$user = get_authed_user();
if (!$user) {
    json_response(['error' => 'Unauthorized.'], 401);
}

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'ShulkerUpload') {
    json_response(['error' => 'Bad request.'], 400);
}

$body = json_decode(file_get_contents('php://input'), true);
$id   = $body['id'] ?? '';

if (!$id || !preg_match('/^[a-f0-9]{32}$/', $id)) {
    json_response(['error' => 'Invalid image ID.'], 400);
}

$images = load_user_images($user['id']);
$img    = find_image_by_id($images, $id);

if (!$img) {
    json_response(['error' => 'Image not found.'], 404);
}

// Remove physical file
$file_path = IMAGES_PATH . '/' . $user['id'] . '/' . $id . '.' . $img['type'];
if (file_exists($file_path)) {
    @unlink($file_path);
}

// Update JSON
remove_image_by_id($images, $id);
save_user_images($user['id'], $images);

json_response(['ok' => true]);
