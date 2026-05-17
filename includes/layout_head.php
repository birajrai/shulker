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
          mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
          sans: ['"DM Sans"', 'ui-sans-serif', 'system-ui'],
          pixel: ['"VT323"', 'monospace'],
          retro: ['"Press Start 2P"', 'monospace'],
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
            deep: '#1e1420',
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
<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&family=VT323&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
  *, *::before, *::after { box-sizing: border-box; }
  
  /* Minecraft scrollbar */
  ::-webkit-scrollbar { width: 8px; height: 8px; }
  ::-webkit-scrollbar-track { background: #130c14; }
  ::-webkit-scrollbar-thumb { 
    background: #8c5c8c; 
    border: 2px solid #130c14;
    box-shadow: inset 1px 1px 0px #ac7cac, inset -1px -1px 0px #5c385c;
  }

  body { 
    background: radial-gradient(circle, #25162a 0%, #0c070e 100%); 
    color: #e0e0e0; 
    font-family: 'JetBrains Mono', monospace; 
  }

  /* Pixelated rendering */
  .pixelated {
    image-rendering: pixelated;
    image-rendering: crisp-edges;
  }

  /* Minecraft block container */
  .mc-panel {
    background-color: #2c1d30;
    border: 4px solid #000000;
    box-shadow: inset -4px -4px 0px 0px #180f1b, inset 4px 4px 0px 0px #4d3354;
    image-rendering: pixelated;
  }

  .mc-panel-outer {
    border: 4px solid #000000;
    box-shadow: inset -4px -4px 0px 0px #0c070e, inset 4px 4px 0px 0px #35213b;
    background: #190f1d;
  }

  /* Minecraft button styling */
  .mc-btn {
    font-family: 'Press Start 2P', monospace;
    font-size: 11px;
    color: #e0e0e0;
    background-color: #4a4a4a;
    border: 2px solid #000000;
    box-shadow: inset -2px -2px 0px 0px #2b2b2b, inset 2px 2px 0px 0px #8b8b8b;
    padding: 8px 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-shadow: 2px 2px 0px #1c1c1c;
    transition: none;
    user-select: none;
  }
  .mc-btn:hover {
    background-color: #5c5c5c;
    color: #ffffff;
    box-shadow: inset -2px -2px 0px 0px #3b3b3b, inset 2px 2px 0px 0px #ababab;
  }
  .mc-btn:active {
    box-shadow: inset 2px 2px 0px 0px #1c1c1c, inset -2px -2px 0px 0px #4a4a4a;
    padding-top: 9px;
    padding-bottom: 7px;
  }

  /* Purple Shulker Box Button */
  .mc-btn-shulker {
    background-color: #8c5c8c;
    box-shadow: inset -2px -2px 0px 0px #4a2c4a, inset 2px 2px 0px 0px #ac7cac;
    text-shadow: 2px 2px 0px #2a152a;
  }
  .mc-btn-shulker:hover {
    background-color: #9c6c9c;
    box-shadow: inset -2px -2px 0px 0px #5c385c, inset 2px 2px 0px 0px #bc8cbc;
  }
  .mc-btn-shulker:active {
    box-shadow: inset 2px 2px 0px 0px #1e0e1e, inset -2px -2px 0px 0px #8c5c8c;
    padding-top: 9px;
    padding-bottom: 7px;
  }

  /* Green Minecraft Button */
  .mc-btn-green {
    background-color: #5c8c5c;
    box-shadow: inset -2px -2px 0px 0px #2c4a2c, inset 2px 2px 0px 0px #7cac7c;
    text-shadow: 2px 2px 0px #1a2c1a;
  }
  .mc-btn-green:hover {
    background-color: #6c9c6c;
    box-shadow: inset -2px -2px 0px 0px #3c5c3c, inset 2px 2px 0px 0px #8cbc8c;
  }
  .mc-btn-green:active {
    box-shadow: inset 2px 2px 0px 0px #121e12, inset -2px -2px 0px 0px #5c8c5c;
    padding-top: 9px;
    padding-bottom: 7px;
  }

  /* Red Minecraft Button */
  .mc-btn-red {
    background-color: #b83838;
    box-shadow: inset -2px -2px 0px 0px #5e1c1c, inset 2px 2px 0px 0px #f06a6a;
    text-shadow: 2px 2px 0px #3a0e0e;
  }
  .mc-btn-red:hover {
    background-color: #c84848;
    box-shadow: inset -2px -2px 0px 0px #6e2c2c, inset 2px 2px 0px 0px #ff7a7a;
  }
  .mc-btn-red:active {
    box-shadow: inset 2px 2px 0px 0px #2c0808, inset -2px -2px 0px 0px #b83838;
    padding-top: 9px;
    padding-bottom: 7px;
  }

  /* Disabled button */
  .mc-btn:disabled {
    background-color: #333333 !important;
    color: #666666 !important;
    box-shadow: inset -2px -2px 0px 0px #1e1e1e, inset 2px 2px 0px 0px #555555 !important;
    text-shadow: none !important;
    cursor: not-allowed;
  }

  /* Minecraft inventory slot styling */
  .mc-slot {
    background-color: #130c14;
    border: 2px solid #000000;
    box-shadow: inset 3px 3px 0px 0px #070407, inset -3px -3px 0px 0px #241626;
    image-rendering: pixelated;
    aspect-ratio: 1/1;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
  }
  .mc-slot:hover {
    background-color: #241626;
    box-shadow: inset 3px 3px 0px 0px #0e090f, inset -3px -3px 0px 0px #311e33;
  }

  .mc-slot-empty {
    opacity: 0.15;
  }

  /* Minecraft slot item highlight */
  .mc-slot-highlight::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.1);
    opacity: 0;
    pointer-events: none;
  }
  .mc-slot:hover .mc-slot-highlight::after {
    opacity: 1;
  }

  /* Minecraft standard text shadow */
  .mc-shadow {
    text-shadow: 2px 2px 0px #000;
  }
  .mc-shadow-purple {
    text-shadow: 2px 2px 0px #2a152a;
  }

  /* Minecraft tooltip style */
  .mc-tooltip {
    background-color: rgba(16, 1, 16, 0.96) !important;
    border: 2px solid #2e0854 !important;
    box-shadow: 0 0 0 2px #100110, inset 0 0 0 1px #4a0d8c !important;
    border-radius: 0px !important;
    color: #ffffff;
    padding: 8px 12px;
    z-index: 999;
    font-family: 'JetBrains Mono', monospace;
    text-shadow: 2px 2px 0px #100110;
    image-rendering: pixelated;
  }

  /* Upload zone drop effects */
  .upload-zone.drag-over {
    border-color: #ac7cac !important;
    background-color: #241626 !important;
  }

  /* Floating particle animations */
  @keyframes particleFloat {
    0% { transform: translateY(100vh) scale(0); opacity: 0; }
    50% { opacity: 0.7; }
    100% { transform: translateY(-10vh) scale(1); opacity: 0; }
  }
  .particle {
    position: fixed;
    width: 6px;
    height: 6px;
    background: #ac7cac;
    box-shadow: 0 0 8px #bc8cbc;
    pointer-events: none;
    z-index: 0;
    animation: particleFloat 12s linear infinite;
  }

  /* Pixel borders helper */
  .pixel-border-4 {
    border: 4px solid black;
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

