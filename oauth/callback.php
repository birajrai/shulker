<?php
// ============================================================
// SHULKER — Discord OAuth2 Callback
// ============================================================

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/security.php';

send_security_headers();
shulker_session_start();

// CSRF state check
$state = $_GET['state'] ?? '';
if (!$state || !hash_equals($_SESSION['oauth_state'] ?? '', $state)) {
    http_response_code(400);
    die('Invalid OAuth state. <a href="/">Go home</a>');
}
unset($_SESSION['oauth_state']);

$code = $_GET['code'] ?? '';
if (!$code) {
    http_response_code(400);
    die('Missing code. <a href="/">Go home</a>');
}

// Exchange code for token
$token_response = discord_post('https://discord.com/api/oauth2/token', [
    'client_id'     => DISCORD_CLIENT_ID,
    'client_secret' => DISCORD_CLIENT_SECRET,
    'grant_type'    => 'authorization_code',
    'code'          => $code,
    'redirect_uri'  => DISCORD_REDIRECT_URI,
]);

if (!$token_response || isset($token_response['error'])) {
    http_response_code(500);
    die('OAuth token exchange failed. <a href="/">Go home</a>');
}

$access_token = $token_response['access_token'] ?? '';
if (!$access_token) {
    http_response_code(500);
    die('No access token received. <a href="/">Go home</a>');
}

// Fetch Discord user info
$user_data = discord_get('https://discord.com/api/users/@me', $access_token);
if (!$user_data || !isset($user_data['id'])) {
    http_response_code(500);
    die('Failed to fetch Discord user. <a href="/">Go home</a>');
}

// Store minimal user info in session (never store tokens server-side persistently)
set_authed_user([
    'id'            => $user_data['id'],
    'username'      => $user_data['username'],
    'discriminator' => $user_data['discriminator'] ?? '0',
    'avatar'        => $user_data['avatar'] ?? null,
    'global_name'   => $user_data['global_name'] ?? $user_data['username'],
]);

header('Location: /dashboard');
exit;

// ---- Helpers ----

function discord_post(string $url, array $fields): ?array {
    $body = http_build_query($fields);
    $ctx  = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n"
                       . "Content-Length: " . strlen($body) . "\r\n"
                       . "User-Agent: Shulker/1.0\r\n",
            'content' => $body,
            'timeout' => 10,
        ],
    ]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res === false) return null;
    return json_decode($res, true);
}

function discord_get(string $url, string $token): ?array {
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'GET',
            'header'  => "Authorization: Bearer {$token}\r\nUser-Agent: Shulker/1.0\r\n",
            'timeout' => 10,
        ],
    ]);
    $res = @file_get_contents($url, false, $ctx);
    if ($res === false) return null;
    return json_decode($res, true);
}
