<?php
// ============================================================
// SHULKER — Security Headers
// ============================================================

function send_security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Robots-Tag: noindex,nofollow');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: no-referrer');
    header('Content-Security-Policy: default-src \'self\'; '
         . 'script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://cdn.tailwindcss.com https://unpkg.com https://static.cloudflareinsights.com; '
         . 'style-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com https://fonts.googleapis.com; '
         . 'font-src https://fonts.gstatic.com; '
         . 'img-src \'self\' data: https://cdn.discordapp.com; '
         . 'connect-src \'self\' https://cloudflareinsights.com;');
}

function json_response(array $data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function h(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
