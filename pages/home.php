<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/layout_head.php';
layout_head('Home');
?>

<div class="h-screen w-screen overflow-hidden flex flex-col justify-between select-none">

  <!-- Nav -->
  <nav class="border-b-4 border-black bg-shulker-deep px-6 py-4 flex items-center justify-between z-10 flex-shrink-0">
    <div class="flex items-center gap-3">
      <img src="/shulker.webp" alt="Shulker Box" class="w-7 h-7 pixelated hover:rotate-12 transition-transform duration-300">
      <span class="font-black text-sm tracking-wider text-white uppercase">
        shulker
      </span>
    </div>
    <a href="/login" class="mc-btn mc-btn-shulker text-xs py-1.5 px-4">
      Sign in
    </a>
  </nav>

  <!-- Centered Hero Box -->
  <main class="flex-1 flex items-center justify-center p-6 z-10 min-h-0">
    
    <div class="mc-panel max-w-md w-full p-8 sm:p-10 text-center">
      
      <!-- Big animated Shulker box -->
      <div class="mb-6 relative group inline-block">
        <div class="absolute inset-0 bg-shulker-light/10 blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <img src="/shulker.webp" alt="Shulker Box" class="w-24 h-24 mx-auto pixelated transform group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300 cursor-pointer">
      </div>

      <h1 class="font-black text-2xl sm:text-3xl text-yellow-300 uppercase tracking-wider mb-4">
        SHULKER BOX
      </h1>

      <p class="text-gray-300 text-sm font-normal leading-relaxed mb-8 max-w-sm mx-auto">
        A clean, private uploader for your images. Drag & drop or paste directly from your clipboard to store images instantly.
      </p>

      <a href="/login" class="mc-btn mc-btn-shulker w-full py-3.5 text-xs flex items-center justify-center gap-2.5">
        <svg width="18" height="15" viewBox="0 0 71 55" fill="none" xmlns="http://www.w3.org/2000/svg" class="fill-current">
          <path d="M60.1 4.9A58.8 58.8 0 0 0 45.7.7a40.7 40.7 0 0 0-1.8 3.7 54.4 54.4 0 0 0-16.3 0A38.7 38.7 0 0 0 25.8.7 58.6 58.6 0 0 0 11.4 5C1.6 19.4-1 33.5.3 47.4a59.2 59.2 0 0 0 18 9.1 44.8 44.8 0 0 0 3.9-6.3 38.4 38.4 0 0 1-6.1-2.9l1.5-1.1a42.2 42.2 0 0 0 35.9 0l1.5 1.1a38.3 38.3 0 0 1-6.1 2.9 44.5 44.5 0 0 0 3.8 6.3 59 59 0 0 0 18-9c1.6-16.2-2.7-30.1-11.5-42.5ZM23.7 38.8c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Zm23.6 0c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Z"/>
        </svg>
        Continue with Discord
      </a>

    </div>
  </main>

  <!-- Dummy spacer for alignment without footer -->
  <div class="h-6 flex-shrink-0"></div>

</div>
</body>
</html>
