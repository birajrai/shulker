<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/layout_head.php';
layout_head('Home');
?>

<div class="min-h-screen flex flex-col justify-between">

  <!-- Nav -->
  <nav class="border-b-4 border-black bg-shulker-deep px-6 py-4 flex items-center justify-between z-10">
    <div class="flex items-center gap-3">
      <img src="/shulker.webp" alt="Shulker Box" class="w-8 h-8 pixelated hover:rotate-12 transition-transform duration-300">
      <span class="font-retro text-sm tracking-tight text-white mc-shadow">
        shulker
      </span>
    </div>
    <a href="/login" class="mc-btn mc-btn-shulker text-[10px] sm:text-xs">
      Sign in
    </a>
  </nav>

  <!-- Hero -->
  <main class="flex-1 flex flex-col items-center justify-center px-4 py-16 z-10">
    
    <div class="mc-panel max-w-md w-full p-6 sm:p-8 text-center">
      
      <!-- Big animated Shulker box -->
      <div class="mb-6 relative group inline-block">
        <div class="absolute inset-0 bg-shulker-light/20 blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <img src="/shulker.webp" alt="Shulker Box" class="w-32 h-32 mx-auto pixelated transform group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300 cursor-pointer">
      </div>

      <h1 class="font-retro text-xl sm:text-2xl text-yellow-300 leading-tight mc-shadow mb-4">
        SHULKER BOX
      </h1>

      <p class="text-gray-300 text-sm font-mono leading-relaxed mb-8">
        Store your images inside a secure, private shulker box. Drag & drop or paste from clipboard to upload instantly.
      </p>

      <a href="/login" class="mc-btn mc-btn-shulker w-full py-4 text-xs flex items-center justify-center gap-3">
        <svg width="18" height="14" viewBox="0 0 71 55" fill="none" xmlns="http://www.w3.org/2000/svg" class="fill-current">
          <path d="M60.1 4.9A58.8 58.8 0 0 0 45.7.7a40.7 40.7 0 0 0-1.8 3.7 54.4 54.4 0 0 0-16.3 0A38.7 38.7 0 0 0 25.8.7 58.6 58.6 0 0 0 11.4 5C1.6 19.4-1 33.5.3 47.4a59.2 59.2 0 0 0 18 9.1 44.8 44.8 0 0 0 3.9-6.3 38.4 38.4 0 0 1-6.1-2.9l1.5-1.1a42.2 42.2 0 0 0 35.9 0l1.5 1.1a38.3 38.3 0 0 1-6.1 2.9 44.5 44.5 0 0 0 3.8 6.3 59 59 0 0 0 18-9c1.6-16.2-2.7-30.1-11.5-42.5ZM23.7 38.8c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Zm23.6 0c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Z"/>
        </svg>
        Continue with Discord
      </a>

    </div>
  </main>

  <!-- Footer -->
  <footer class="border-t-4 border-black bg-shulker-deep px-6 py-4 text-center z-10">
    <span class="font-mono text-xs text-gray-400 mc-shadow">shulker box v<?= SHULKER_VERSION ?></span>
  </footer>

</div>
</body>
</html>

