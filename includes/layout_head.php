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
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<title><?= h($title) ?> — Shulker</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          mono: ['"Roboto"', 'sans-serif'],
          sans: ['"Roboto"', 'sans-serif'],
          pixel: ['"Roboto"', 'sans-serif'],
          retro: ['"Roboto"', 'sans-serif'],
        },
        colors: {
          ink:   { DEFAULT: '#0d0d0d', light: '#1a1a1a', soft: '#2e2e2e' },
          chalk: { DEFAULT: '#f5f4f0', warm: '#ede9e3', cool: '#f0f0ee' },
          stone: { DEFAULT: '#888885', light: '#b8b8b4', dark: '#555552' },
          rust:  { DEFAULT: '#c94f2a', light: '#e8683f', dark: '#9e3d1e' },
          sage:  { DEFAULT: '#4a7c59', light: '#5f9e70' },
          shulker: {
            DEFAULT: '#8c5c8c',
            dark: '#4a2c4a',
            light: '#ac7cac',
            deep: '#180e1a',
            gui: '#2c1e2d',
            slot: '#130c14',
          }
        },
        borderRadius: { DEFAULT: '0px', sm: '0px', md: '0px', lg: '0px', full: '9999px' },
      }
    }
  }
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
  *, *::before, *::after { box-sizing: border-box; }
  
  /* Roboto scrollbar */
  ::-webkit-scrollbar { width: 8px; height: 8px; }
  ::-webkit-scrollbar-track { background: #130c14; }
  ::-webkit-scrollbar-thumb { 
    background: #8c5c8c; 
    border: 2px solid #130c14;
    box-shadow: inset 1px 1px 0px #ac7cac, inset -1px -1px 0px #5c385c;
  }

  body { 
    background: radial-gradient(circle, #281830 0%, #08040a 100%); 
    color: #f3ecf7; 
    font-family: 'Roboto', sans-serif; 
  }

  /* Pixelated rendering for the Shulker box image logo */
  .pixelated {
    image-rendering: pixelated;
    image-rendering: crisp-edges;
  }

  /* Modern Flat 3D Voxel Container */
  .mc-panel {
    background-color: #24142a;
    border: 4px solid #000000;
    box-shadow: 8px 8px 0px 0px #000000;
    image-rendering: auto;
  }

  .mc-panel-outer {
    border: 4px solid #000000;
    box-shadow: 8px 8px 0px 0px #08040a;
    background: #180e1a;
  }

  /* Flat 3D oversized button styling */
  .mc-btn {
    font-family: 'Roboto', sans-serif;
    font-weight: 900;
    text-transform: uppercase;
    font-size: 13px;
    letter-spacing: 0.05em;
    color: #ffffff;
    background-color: #3e3e3e;
    border: 3px solid #000000;
    box-shadow: 4px 4px 0px 0px #000000;
    padding: 12px 24px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: transform 50ms ease, box-shadow 50ms ease;
    user-select: none;
  }
  .mc-btn:hover {
    background-color: #525252;
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0px 0px #000000;
  }
  .mc-btn:active {
    transform: translate(2px, 2px);
    box-shadow: 2px 2px 0px 0px #000000;
  }

  /* Purple Shulker Box Button */
  .mc-btn-shulker {
    background-color: #8c5c8c;
  }
  .mc-btn-shulker:hover {
    background-color: #9f6c9f;
  }

  /* Green Minecraft Button */
  .mc-btn-green {
    background-color: #2e7d32;
  }
  .mc-btn-green:hover {
    background-color: #388e3c;
  }

  /* Red Minecraft Button */
  .mc-btn-red {
    background-color: #c62828;
  }
  .mc-btn-red:hover {
    background-color: #d32f2f;
  }

  /* Disabled button */
  .mc-btn:disabled {
    background-color: #222222 !important;
    color: #555555 !important;
    box-shadow: none !important;
    transform: none !important;
    cursor: not-allowed;
  }

  /* Large Clean Voxel Slot */
  .mc-slot {
    background-color: #130c14;
    border: 3px solid #000000;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
    transition: transform 100ms ease, background-color 100ms ease;
  }
  .mc-slot:hover {
    background-color: #201322;
    transform: scale(1.05);
    z-index: 10;
  }

  .mc-slot-empty {
    border: 3px dashed rgba(255, 255, 255, 0.12);
    background-color: rgba(0, 0, 0, 0.2);
  }

  /* Highlight on hover */
  .mc-slot-highlight::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.08);
    opacity: 0;
    pointer-events: none;
  }
  .mc-slot:hover .mc-slot-highlight::after {
    opacity: 1;
  }

  /* Tooltip style using Roboto */
  .mc-tooltip {
    background-color: rgba(16, 1, 16, 0.98) !important;
    border: 3px solid #8c5c8c !important;
    box-shadow: 6px 6px 0px 0px #000000;
    color: #ffffff;
    padding: 12px 16px;
    z-index: 999;
    font-family: 'Roboto', sans-serif;
  }

  /* Upload zone drop effects */
  .upload-zone {
    transition: background-color 150ms ease, border-color 150ms ease;
  }
  .upload-zone.drag-over {
    border-color: #ac7cac !important;
    background-color: #241626 !important;
  }

  /* Floating particle animations */
  @keyframes particleFloat {
    0% { transform: translateY(100vh) scale(0); opacity: 0; }
    50% { opacity: 0.6; }
    100% { transform: translateY(-10vh) scale(1.5); opacity: 0; }
  }
  .particle {
    position: fixed;
    width: 8px;
    height: 8px;
    background: #8c5c8c;
    box-shadow: 0 0 10px #ac7cac;
    pointer-events: none;
    z-index: 0;
    animation: particleFloat 12s linear infinite;
  }
</style>
</head>
<body class="min-h-full flex flex-col">
  <!-- Dynamic floating particles -->
  <div class="particle" style="left: 10%; animation-delay: 0s; animation-duration: 10s;"></div>
  <div class="particle" style="left: 30%; animation-delay: 2s; animation-duration: 14s;"></div>
  <div class="particle" style="left: 55%; animation-delay: 5s; animation-duration: 12s;"></div>
  <div class="particle" style="left: 75%; animation-delay: 1s; animation-duration: 16s;"></div>
  <div class="particle" style="left: 90%; animation-delay: 7s; animation-duration: 11s;"></div>
<?php } ?>
