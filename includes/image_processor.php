<?php
// ============================================================
// SHULKER — Image Processor
// ============================================================

require_once __DIR__ . '/../config.php';

// Allowed real MIME types
const ALLOWED_MIME = [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/avif',
    'image/gif',
];

// Rejected types (explicit blocklist)
const BLOCKED_MIME = [
    'image/svg+xml',
    'image/bmp',
    'image/tiff',
    'image/heic',
    'image/heif',
];

/**
 * Validate that the file is a real, allowed image.
 * Returns ['ok' => true, 'mime' => '...', 'animated' => bool]
 * or      ['ok' => false, 'error' => '...']
 */
function validate_image(string $tmp_path): array {
    if (!file_exists($tmp_path)) {
        return ['ok' => false, 'error' => 'Upload failed.'];
    }

    $size = filesize($tmp_path);
    if ($size > MAX_UPLOAD_BYTES) {
        return ['ok' => false, 'error' => 'File exceeds 5 MB limit.'];
    }
    if ($size === 0) {
        return ['ok' => false, 'error' => 'Empty file.'];
    }

    // Use finfo for real MIME detection
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($tmp_path);

    if (in_array($mime, BLOCKED_MIME, true)) {
        return ['ok' => false, 'error' => 'File type not allowed.'];
    }
    if (!in_array($mime, ALLOWED_MIME, true)) {
        return ['ok' => false, 'error' => 'Unsupported file type.'];
    }

    // Actually try to decode the image (GD or Imagick)
    if (!try_decode_image($tmp_path, $mime)) {
        return ['ok' => false, 'error' => 'File does not appear to be a valid image.'];
    }

    $animated = is_animated($tmp_path, $mime);

    return ['ok' => true, 'mime' => $mime, 'animated' => $animated];
}

/**
 * Attempt to decode with GD or Imagick to confirm it's a real image.
 */
function try_decode_image(string $path, string $mime): bool {
    if (extension_loaded('imagick')) {
        try {
            $im = new Imagick($path);
            $im->clear();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // GD fallback
    if (function_exists('imagecreatefromstring')) {
        $data = @file_get_contents($path);
        if ($data === false) return false;
        $img = @imagecreatefromstring($data);
        if ($img === false) return false;
        imagedestroy($img);
        return true;
    }

    return false; // No image lib available — fail safe
}

/**
 * Detect animated GIF or animated WEBP.
 */
function is_animated(string $path, string $mime): bool {
    if ($mime === 'image/gif') return is_animated_gif($path);
    if ($mime === 'image/webp') return is_animated_webp($path);
    return false;
}

function is_animated_gif(string $path): bool {
    $content = file_get_contents($path);
    if ($content === false) return false;
    // Count GIF frame markers
    return substr_count($content, "\x00\x21\xF9\x04") > 1;
}

function is_animated_webp(string $path): bool {
    $data = file_get_contents($path, false, null, 0, 64);
    if ($data === false || strlen($data) < 30) return false;
    // Check for ANIM chunk in RIFF container
    return strpos($data, 'ANIM') !== false;
}

/**
 * Process an uploaded file: convert, strip metadata, save.
 * Returns ['ok' => true, 'type' => 'avif'|'webm', 'path' => '...', 'size' => int]
 * or      ['ok' => false, 'error' => '...']
 */
function process_image(string $tmp_path, string $out_dir, string $random_id, string $mime, bool $animated): array {
    // Ensure parent images directory is 0755
    $parent_dir = dirname($out_dir); // IMAGES_PATH
    if (is_dir($parent_dir)) {
        @chmod($parent_dir, 0755);
    }

    if (!is_dir($out_dir)) {
        mkdir($out_dir, 0755, true);
    }
    @chmod($out_dir, 0755);

    if ($animated) {
        $res = convert_to_webm($tmp_path, $out_dir, $random_id);
    } else {
        $res = convert_to_avif($tmp_path, $out_dir, $random_id, $mime);
    }

    // Ensure the created file is readable by the web server (0644)
    if ($res['ok'] && isset($res['path']) && file_exists($res['path'])) {
        @chmod($res['path'], 0644);
    }

    return $res;
}

/**
 * Convert static image to AVIF using Imagick or FFmpeg.
 */
function convert_to_avif(string $src, string $out_dir, string $id, string $mime): array {
    $dest = $out_dir . '/' . $id . '.avif';

    // 1. Try Imagick if available and has AVIF write support
    if (extension_loaded('imagick')) {
        try {
            $im = new Imagick($src);
            // Check if AVIF format is supported by the system's ImageMagick
            if (in_array('AVIF', $im->queryFormats('AVIF'), true)) {
                // Strip all metadata
                $im->stripImage();
                // Handle multi-layer (just take first)
                if ($im->getNumberImages() > 1) {
                    $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                }
                $im->setImageFormat('avif');
                $im->setImageCompressionQuality(AVIF_QUALITY);
                $im->writeImage($dest);
                $im->clear();

                if (file_exists($dest)) {
                    return ['ok' => true, 'type' => 'avif', 'path' => $dest, 'size' => filesize($dest)];
                }
            }
        } catch (Exception $e) {
            // Fall through to GD
        }
    }

    // 2. GD Fallback (PHP 8.1+ supports AVIF natively in GD)
    if (function_exists('imageavif') && function_exists('imagecreatefromstring')) {
        try {
            $data = @file_get_contents($src);
            if ($data !== false) {
                $im = @imagecreatefromstring($data);
                if ($im !== false) {
                    // Convert palette-based images (like GIF/8-bit PNG) to truecolor to preserve quality
                    if (!imageistruecolor($im)) {
                        imagepalettetotruecolor($im);
                    }
                    
                    // Save as AVIF
                    $ok = @imageavif($im, $dest, AVIF_QUALITY);
                    imagedestroy($im);

                    if ($ok && file_exists($dest)) {
                        return ['ok' => true, 'type' => 'avif', 'path' => $dest, 'size' => filesize($dest)];
                    }
                }
            }
        } catch (Exception $e) {
            // Fall through to FFmpeg
        }
    }

    // 3. FFmpeg fallback for AVIF
    $escaped_src  = escapeshellarg($src);
    $escaped_dest = escapeshellarg($dest);
    $quality = AVIF_QUALITY;
    // Map quality 0-100 to crf 63-0 (lower crf = better quality for libaom)
    $crf = (int)round(63 - ($quality / 100 * 63));

    $cmd = "ffmpeg -y -i {$escaped_src} -vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' "
         . "-c:v libaom-av1 -crf {$crf} -b:v 0 -still-picture 1 "
         . "-map_metadata -1 {$escaped_dest} 2>/dev/null";
    exec($cmd, $output, $code);

    if ($code !== 0 || !file_exists($dest)) {
        return ['ok' => false, 'error' => 'Image conversion failed.'];
    }

    return ['ok' => true, 'type' => 'avif', 'path' => $dest, 'size' => filesize($dest)];
}

/**
 * Convert animated GIF/WEBP to WEBM VP9 using FFmpeg.
 */
function convert_to_webm(string $src, string $out_dir, string $id): array {
    $dest         = $out_dir . '/' . $id . '.webm';
    $escaped_src  = escapeshellarg($src);
    $escaped_dest = escapeshellarg($dest);

    // VP9 two-pass would be ideal but for simplicity use CRF mode
    $cmd = "ffmpeg -y -i {$escaped_src} "
         . "-c:v libvpx-vp9 -crf 33 -b:v 0 -an "
         . "-map_metadata -1 "
         . "-vf 'scale=trunc(iw/2)*2:trunc(ih/2)*2' "
         . "{$escaped_dest} 2>/dev/null";
    exec($cmd, $output, $code);

    if ($code !== 0 || !file_exists($dest)) {
        return ['ok' => false, 'error' => 'Animation conversion failed. Ensure FFmpeg with libvpx-vp9 is installed.'];
    }

    return ['ok' => true, 'type' => 'webm', 'path' => $dest, 'size' => filesize($dest)];
}

/**
 * Automatically heal permissions for the user's images directory and files.
 */
function heal_user_permissions(string $user_id): void {
    $parent = IMAGES_PATH;
    if (is_dir($parent)) {
        @chmod($parent, 0755);
    }
    
    $user_dir = $parent . '/' . $user_id;
    if (is_dir($user_dir)) {
        @chmod($user_dir, 0755);
        
        // Scan directory and fix all file permissions
        $files = scandir($user_dir);
        if ($files !== false) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $file_path = $user_dir . '/' . $file;
                if (is_file($file_path)) {
                    @chmod($file_path, 0644);
                }
            }
        }
    }
}

