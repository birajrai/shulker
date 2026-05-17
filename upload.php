<?php
// ============================================================
// SHULKER — Upload Handler
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/rate_limit.php';
require_once __DIR__ . '/includes/image_processor.php';

send_security_headers();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed.'], 405);
}

$user = get_authed_user();
if (!$user) {
    json_response(['error' => 'Unauthorized.'], 401);
}

// CSRF check via custom header (AJAX only)
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'ShulkerUpload') {
    json_response(['error' => 'Bad request.'], 400);
}

// Rate limiting — per IP
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] 
   ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
   ?? $_SERVER['REMOTE_ADDR'] 
   ?? 'unknown';
$ip = preg_replace('/[^a-f0-9.:,]/', '', strtolower($ip));
// Take first IP if comma-separated
$ip = explode(',', $ip)[0];

if (!rate_limit_check('ip:' . $ip)) {
    json_response(['error' => 'Too many uploads. Please wait a moment.'], 429);
}

// Rate limiting — per user
if (!rate_limit_check('user:' . $user['id'])) {
    json_response(['error' => 'Too many uploads. Please wait a moment.'], 429);
}

// Check file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $err_map = [
        UPLOAD_ERR_INI_SIZE   => 'File too large (server limit).',
        UPLOAD_ERR_FORM_SIZE  => 'File too large.',
        UPLOAD_ERR_PARTIAL    => 'Upload was incomplete.',
        UPLOAD_ERR_NO_FILE    => 'No file uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server misconfiguration.',
        UPLOAD_ERR_CANT_WRITE => 'Server write error.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by server.',
    ];
    $code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    json_response(['error' => $err_map[$code] ?? 'Upload failed.'], 400);
}

$tmp_path = $_FILES['file']['tmp_name'];

// Validate image
$validation = validate_image($tmp_path);
if (!$validation['ok']) {
    @unlink($tmp_path);
    json_response(['error' => $validation['error']], 400);
}

$mime     = $validation['mime'];
$animated = $validation['animated'];

// SHA256 duplicate detection
$hash = hash_file('sha256', $tmp_path);
if ($hash === false) {
    json_response(['error' => 'Could not hash file.'], 500);
}

$images = load_user_images($user['id']);

// Check image limit
if (count($images) >= MAX_IMAGES_PER_USER) {
    @unlink($tmp_path);
    json_response(['error' => 'Upload limit reached. Delete some images first.'], 400);
}

// Check duplicate
foreach ($images as $existing) {
    if (($existing['hash'] ?? '') === $hash) {
        @unlink($tmp_path);
        json_response(['error' => 'You have already uploaded this image.'], 409);
    }
}

// Generate secure random ID
$random_id = bin2hex(random_bytes(16));
$user_dir  = IMAGES_PATH . '/' . $user['id'];

// Process image
$result = process_image($tmp_path, $user_dir, $random_id, $mime, $animated);

// Always delete temp file
@unlink($tmp_path);

if (!$result['ok']) {
    json_response(['error' => $result['error']], 500);
}

// Build record
$type    = $result['type']; // 'avif' or 'webm'
$url     = '/images/' . $user['id'] . '/' . $random_id . '.' . $type;
$abs_url = BASE_URL . $url;

$record = [
    'id'      => $random_id,
    'type'    => $type,
    'url'     => $url,
    'hash'    => $hash,
    'size'    => $result['size'],
    'created' => time(),
];

// Prepend so newest first
array_unshift($images, $record);
save_user_images($user['id'], $images);

// Occasional GC
if (random_int(1, 20) === 1) rate_limit_gc();

json_response([
    'ok'      => true,
    'id'      => $random_id,
    'type'    => $type,
    'url'     => $abs_url,
    'size'    => $result['size'],
    'created' => $record['created'],
]);
