<?php
// ============================================================
// SHULKER — Layout: Head
// ============================================================
// Usage: layout_head('Page Title');
function layout_head(string $title = 'Shulker'): void {
?><!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title><?= h($title) ?> — Shulker</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
          sans: ['"DM Sans"', 'ui-sans-serif', 'system-ui'],
        },
        colors: {
          ink:   { DEFAULT: '#0d0d0d', light: '#1a1a1a', soft: '#2e2e2e' },
          chalk: { DEFAULT: '#f5f4f0', warm: '#ede9e3', cool: '#f0f0ee' },
          stone: { DEFAULT: '#888885', light: '#b8b8b4', dark: '#555552' },
          rust:  { DEFAULT: '#c94f2a', light: '#e8683f', dark: '#9e3d1e' },
          sage:  { DEFAULT: '#4a7c59', light: '#5f9e70' },
        },
        borderRadius: { DEFAULT: '3px', sm: '2px', md: '4px', lg: '6px' },
      }
    }
  }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;1,9..40,300&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
  *, *::before, *::after { box-sizing: border-box; }
  body { background: #f5f4f0; color: #0d0d0d; font-family: 'DM Sans', sans-serif; }
  .font-mono { font-family: 'JetBrains Mono', monospace; }
  ::-webkit-scrollbar { width: 6px; height: 6px; }
  ::-webkit-scrollbar-track { background: #ede9e3; }
  ::-webkit-scrollbar-thumb { background: #b8b8b4; border-radius: 3px; }
  [x-cloak] { display: none !important; }
  .upload-zone { transition: border-color 120ms ease, background 120ms ease; }
  .upload-zone.drag-over { border-color: #c94f2a !important; background: #fdf4f1 !important; }
  .img-card { transition: box-shadow 120ms ease; }
  .img-card:hover { box-shadow: 0 4px 16px rgba(13,13,13,0.10); }
  .btn { transition: background 100ms ease, color 100ms ease; }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .fade-up { animation: fadeUp 200ms ease forwards; }
  @keyframes spin { to { transform: rotate(360deg); } }
  .spinner { animation: spin 0.7s linear infinite; }
  .progress-bar { transition: width 200ms linear; }
</style>
</head>
<body class="min-h-full">
<?php } ?>
