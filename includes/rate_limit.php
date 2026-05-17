<?php
// ============================================================
// SHULKER — Rate Limiter
// ============================================================

require_once __DIR__ . '/../config.php';

function rate_limit_check(string $identifier, int $limit = RATE_LIMIT_UPLOADS, int $window = RATE_LIMIT_WINDOW): bool {
    $safe = preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $identifier);
    $path = STORAGE_PATH . '/rate_limits/' . $safe . '.json';
    $dir  = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0750, true);

    $now   = time();
    $state = [];

    if (file_exists($path)) {
        $raw = json_decode(file_get_contents($path), true);
        if (is_array($raw)) $state = $raw;
    }

    // Purge timestamps outside the window
    $state = array_values(array_filter($state, fn($t) => $t > $now - $window));

    if (count($state) >= $limit) return false; // limit hit

    $state[] = $now;
    $tmp = $path . '.tmp.' . bin2hex(random_bytes(4));
    file_put_contents($tmp, json_encode($state), LOCK_EX);
    rename($tmp, $path);
    return true;
}

// Cleanup old rate limit files (call occasionally)
function rate_limit_gc(): void {
    $dir = STORAGE_PATH . '/rate_limits/';
    if (!is_dir($dir)) return;
    $cutoff = time() - RATE_LIMIT_WINDOW * 2;
    foreach (glob($dir . '*.json') as $f) {
        if (filemtime($f) < $cutoff) @unlink($f);
    }
}
