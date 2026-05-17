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
    if (!is_dir($out_dir)) mkdir($out_dir, 0750, true);

    if ($animated) {
        return convert_to_webm($tmp_path, $out_dir, $random_id);
    } else {
        return convert_to_avif($tmp_path, $out_dir, $random_id, $mime);
    }
}

/**
 * Convert static image to AVIF using Imagick or FFmpeg.
 */
function convert_to_avif(string $src, string $out_dir, string $id, string $mime): array {
    $dest = $out_dir . '/' . $id . '.avif';

    if (extension_loaded('imagick')) {
        try {
            $im = new Imagick($src);
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

            if (!file_exists($dest)) {
                return ['ok' => false, 'error' => 'AVIF output not created.'];
            }
            return ['ok' => true, 'type' => 'avif', 'path' => $dest, 'size' => filesize($dest)];
        } catch (Exception $e) {
            // Fall through to FFmpeg
        }
    }

    // FFmpeg fallback for AVIF
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
