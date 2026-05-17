<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/layout_head.php';
layout_head('Home');
?>

<div class="min-h-screen flex flex-col">

  <!-- Nav -->
  <nav class="border-b border-chalk-warm bg-chalk px-6 py-4 flex items-center justify-between">
    <span class="font-mono text-sm font-500 tracking-tight text-ink">
      <span class="text-rust font-500">▪</span> shulker
    </span>
    <a href="/login" class="btn inline-flex items-center gap-2 bg-ink text-chalk text-sm font-400 px-4 py-2 rounded hover:bg-ink-light">
      <svg width="18" height="14" viewBox="0 0 71 55" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M60.1 4.9A58.8 58.8 0 0 0 45.7.7a40.7 40.7 0 0 0-1.8 3.7 54.4 54.4 0 0 0-16.3 0A38.7 38.7 0 0 0 25.8.7 58.6 58.6 0 0 0 11.4 5C1.6 19.4-1 33.5.3 47.4a59.2 59.2 0 0 0 18 9.1 44.8 44.8 0 0 0 3.9-6.3 38.4 38.4 0 0 1-6.1-2.9l1.5-1.1a42.2 42.2 0 0 0 35.9 0l1.5 1.1a38.3 38.3 0 0 1-6.1 2.9 44.5 44.5 0 0 0 3.8 6.3 59 59 0 0 0 18-9c1.6-16.2-2.7-30.1-11.5-42.5ZM23.7 38.8c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Zm23.6 0c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Z" fill="currentColor"/>
      </svg>
      Sign in with Discord
    </a>
  </nav>

  <!-- Hero -->
  <main class="flex-1 flex flex-col items-center justify-center px-6 py-20 text-center">
    <div class="inline-flex items-center gap-2 border border-chalk-warm bg-white text-stone text-xs font-mono px-3 py-1.5 rounded mb-8">
      <span class="w-1.5 h-1.5 rounded-full bg-rust inline-block"></span>
      image hosting, stripped down
    </div>

    <h1 class="font-sans text-5xl sm:text-6xl font-300 text-ink leading-tight tracking-tight mb-6 max-w-2xl">
      Your images.<br>
      <em class="not-italic font-500 text-rust">Nothing else.</em>
    </h1>

    <p class="text-stone text-lg font-300 max-w-md mb-10 leading-relaxed">
      Upload once. Get a clean, permanent URL. No bloat, no ads, no tracking. Private to you.
    </p>

    <a href="/login" class="btn inline-flex items-center gap-3 bg-ink text-chalk px-7 py-3.5 rounded text-sm font-400 hover:bg-ink-light mb-16">
      <svg width="20" height="16" viewBox="0 0 71 55" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M60.1 4.9A58.8 58.8 0 0 0 45.7.7a40.7 40.7 0 0 0-1.8 3.7 54.4 54.4 0 0 0-16.3 0A38.7 38.7 0 0 0 25.8.7 58.6 58.6 0 0 0 11.4 5C1.6 19.4-1 33.5.3 47.4a59.2 59.2 0 0 0 18 9.1 44.8 44.8 0 0 0 3.9-6.3 38.4 38.4 0 0 1-6.1-2.9l1.5-1.1a42.2 42.2 0 0 0 35.9 0l1.5 1.1a38.3 38.3 0 0 1-6.1 2.9 44.5 44.5 0 0 0 3.8 6.3 59 59 0 0 0 18-9c1.6-16.2-2.7-30.1-11.5-42.5ZM23.7 38.8c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Zm23.6 0c-3.5 0-6.4-3.2-6.4-7.2s2.8-7.2 6.4-7.2c3.5 0 6.3 3.2 6.3 7.2s-2.8 7.2-6.3 7.2Z" fill="currentColor"/>
      </svg>
      Continue with Discord
    </a>

    <!-- Feature pills -->
    <div class="flex flex-wrap justify-center gap-3 max-w-lg">
      <?php
      $features = [
        'AVIF + WebM output',
        'Metadata stripped',
        'Drag & drop',
        'Clipboard paste',
        'Duplicate detection',
        'Private by default',
      ];
      foreach ($features as $f): ?>
      <span class="border border-chalk-warm bg-white text-stone-dark text-xs font-mono px-3 py-1.5 rounded"><?= h($f) ?></span>
      <?php endforeach; ?>
    </div>
  </main>

  <footer class="border-t border-chalk-warm px-6 py-4 text-center">
    <span class="font-mono text-xs text-stone">shulker <?= SHULKER_VERSION ?></span>
  </footer>

</div>
</body>
</html>
