<?php
// ============================================================
// SHULKER — Main Router
// ============================================================

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/security.php';

send_security_headers();
shulker_session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/') ?: '/';

// Routes
switch ($path) {
    case '/':
        $user = get_authed_user();
        if ($user) {
            header('Location: /dashboard');
            exit;
        }
        require __DIR__ . '/pages/home.php';
        break;

    case '/login':
        $user = get_authed_user();
        if ($user) {
            header('Location: /dashboard');
            exit;
        }
        // Generate OAuth state
        $state = bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $state;
        $params = http_build_query([
            'client_id'     => DISCORD_CLIENT_ID,
            'redirect_uri'  => DISCORD_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'identify',
            'state'         => $state,
            'prompt'        => 'none',
        ]);
        header('Location: https://discord.com/api/oauth2/authorize?' . $params);
        exit;

    case '/oauth/callback':
        require __DIR__ . '/oauth/callback.php';
        break;

    case '/logout':
        logout();
        header('Location: /');
        exit;

    case '/dashboard':
        require_auth();
        require __DIR__ . '/pages/dashboard.php';
        break;

    case '/upload':
        require __DIR__ . '/upload.php';
        break;

    case '/delete':
        require __DIR__ . '/delete.php';
        break;

    case '/api/images':
        require __DIR__ . '/api_images.php';
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/pages/404.php';
        break;
}
