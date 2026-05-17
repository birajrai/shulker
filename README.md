# Shulker — Image Hosting

Fast, private image hosting with Discord OAuth2. No database. No bloat.

---

## Requirements

- PHP 8.0+
- Apache (with mod_rewrite) or Nginx
- One of:
  - **Imagick** PHP extension (preferred) — for static image AVIF conversion
  - **FFmpeg** with `libaom-av1` and `libvpx-vp9` — for AVIF + WebM animated conversion
- GD extension as fallback for image validation

> For animated GIF/WebP → WebM, FFmpeg with `libvpx-vp9` is required regardless.

---

## Installation

### 1. Clone / upload files

```
/var/www/shulker/
```

### 2. Configure

Copy `config.php` and fill in:

```php
define('BASE_URL', 'https://yourdomain.com');
define('DISCORD_CLIENT_ID', 'your_id');
define('DISCORD_CLIENT_SECRET', 'your_secret');
define('DISCORD_REDIRECT_URI', 'https://yourdomain.com/oauth/callback');
define('SESSION_SECRET', 'generate_with_bin2hex(random_bytes(32))');
```

### 3. Create a Discord Application

1. Go to https://discord.com/developers/applications
2. Create a new application
3. Under **OAuth2**, add redirect: `https://yourdomain.com/oauth/callback`
4. Copy **Client ID** and **Client Secret** into `config.php`

### 4. Set permissions

```bash
chmod 750 storage/ storage/users/ storage/rate_limits/
chmod 750 images/
chown -R www-data:www-data storage/ images/
```

### 5. Web server

**Apache**: `.htaccess` is included. Ensure `mod_rewrite` and `mod_headers` are enabled.

```bash
a2enmod rewrite headers
```

**Nginx**: Copy `nginx.conf.example` to `/etc/nginx/sites-available/shulker`, adjust paths, enable site.

### 6. PHP config (php.ini or pool)

```ini
upload_max_filesize = 6M
post_max_size = 7M
max_execution_time = 60
memory_limit = 256M
```

---

## File structure

```
shulker/
├── config.php              ← Your configuration
├── index.php               ← Router
├── upload.php              ← Upload API
├── delete.php              ← Delete API
├── api_images.php          ← Images list API
├── .htaccess               ← Apache config + security
├── nginx.conf.example      ← Nginx template
├── oauth/
│   └── callback.php        ← Discord OAuth callback
├── includes/
│   ├── auth.php            ← Session + user JSON storage
│   ├── security.php        ← Headers, helpers
│   ├── rate_limit.php      ← Rate limiter
│   ├── image_processor.php ← Validate + convert images
│   └── layout_head.php     ← HTML head partial
├── pages/
│   ├── home.php            ← Landing page
│   ├── dashboard.php       ← Main app UI
│   └── 404.php             ← 404 page
├── images/                 ← Public image storage
│   └── {discord_id}/
│       └── {random_id}.avif / .webm
└── storage/                ← Private data (blocked from web)
    ├── users/
    │   └── {discord_id}.json
    └── rate_limits/
        └── *.json
```

---

## JSON format (`storage/users/{id}.json`)

```json
[
  {
    "id": "a3f2b1c4d5e6f7a8b9c0d1e2f3a4b5c6",
    "type": "avif",
    "url": "/images/123456789/a3f2b1c4d5e6f7a8b9c0d1e2f3a4b5c6.avif",
    "hash": "sha256hashoforiginalfile",
    "size": 84211,
    "created": 1711111111
  }
]
```

---

## Supported formats

| Input         | Output | Notes                         |
|---------------|--------|-------------------------------|
| JPG / JPEG    | AVIF   | Metadata stripped             |
| PNG           | AVIF   | Metadata stripped             |
| WebP (static) | AVIF   | Metadata stripped             |
| AVIF          | AVIF   | Metadata stripped             |
| GIF (animated)| WebM   | VP9, no audio                 |
| WebP (animated)| WebM  | VP9, no audio                 |

Rejected: SVG, BMP, TIFF, HEIC, video files.

---

## Security notes

- Passwords are never involved — Discord handles auth
- All outputs are HTML-escaped
- Filenames are `bin2hex(random_bytes(16))` — unguessable
- JSON writes are atomic (write-temp-then-rename)
- `storage/` is blocked from public access via both `.htaccess` and Nginx
- MIME types are validated with `finfo`, not extension
- Images are decoded with Imagick/GD before acceptance
- Rate limiting: 5 uploads/minute per IP and per user
- CSRF protection via custom `X-Requested-With` header on API calls
- Session cookies: `HttpOnly`, `Secure`, `SameSite=Lax`
