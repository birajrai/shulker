<?php
// ============================================================
// SHULKER — Auth Helper
// ============================================================

require_once __DIR__ . '/../config.php';

function shulker_session_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 86400 * 7,
            'path'     => '/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('shulker_sess');
        session_start();
    }
}

function get_authed_user(): ?array {
    shulker_session_start();
    return $_SESSION['user'] ?? null;
}

function require_auth(): array {
    $user = get_authed_user();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    return $user;
}

function set_authed_user(array $user): void {
    shulker_session_start();
    $_SESSION['user'] = $user;
}

function logout(): void {
    shulker_session_start();
    $_SESSION = [];
    session_destroy();
}

// ---- User JSON storage ----

function user_json_path(string $user_id): string {
    return STORAGE_PATH . '/users/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $user_id) . '.json';
}

function load_user_images(string $user_id): array {
    $path = user_json_path($user_id);
    if (!file_exists($path)) return [];
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function save_user_images(string $user_id, array $images): bool {
    $path = user_json_path($user_id);
    $dir  = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0750, true);

    $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
    $ok  = file_put_contents($tmp, json_encode($images, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    if ($ok === false) return false;
    return rename($tmp, $path);
}

function find_image_by_id(array $images, string $id): ?array {
    foreach ($images as $img) {
        if (($img['id'] ?? '') === $id) return $img;
    }
    return null;
}

function remove_image_by_id(array &$images, string $id): bool {
    foreach ($images as $k => $img) {
        if (($img['id'] ?? '') === $id) {
            unset($images[$k]);
            $images = array_values($images);
            return true;
        }
    }
    return false;
}
