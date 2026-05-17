<?php
// ============================================================
// SHULKER — Images API
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security.php';

send_security_headers();

$user = get_authed_user();
if (!$user) {
    json_response(['error' => 'Unauthorized.'], 401);
}

$images = load_user_images($user['id']);

// Strip hash from public API response, add absolute URLs
$public = array_map(function($img) {
    return [
        'id'      => $img['id'],
        'type'    => $img['type'],
        'url'     => BASE_URL . $img['url'],
        'size'    => $img['size'],
        'created' => $img['created'],
    ];
}, $images);

json_response(['images' => $public, 'count' => count($public)]);
