<?php
// ============================================================
// SHULKER — Configuration
// ============================================================
// Copy this file to config.php and fill in your values.

define('SHULKER_VERSION', '1.0.0');

// Base URL (no trailing slash)
define('BASE_URL', 'https://yourdomain.com');

// Discord OAuth2 credentials
// https://discord.com/developers/applications
define('DISCORD_CLIENT_ID', 'YOUR_CLIENT_ID');
define('DISCORD_CLIENT_SECRET', 'YOUR_CLIENT_SECRET');
define('DISCORD_REDIRECT_URI', BASE_URL . '/oauth/callback');

// Session secret (use: bin2hex(random_bytes(32)))
define('SESSION_SECRET', 'CHANGE_THIS_TO_A_RANDOM_SECRET');

// Upload limits
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);   // 5MB
define('MAX_IMAGES_PER_USER', 27);

// Rate limiting
define('RATE_LIMIT_UPLOADS', 5);    // uploads
define('RATE_LIMIT_WINDOW', 60);    // per second window

// AVIF quality (82-85 = visually lossless)
define('AVIF_QUALITY', 84);

// Paths (relative to document root)
define('STORAGE_PATH', __DIR__ . '/storage');
define('IMAGES_PATH', __DIR__ . '/images');
