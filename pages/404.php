<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/layout_head.php';
layout_head('Not Found');
?>
<div class="min-h-screen flex flex-col items-center justify-center px-6 text-center">
  <p class="font-mono text-xs text-stone mb-3">404</p>
  <h1 class="text-2xl font-300 text-ink mb-6">Page not found.</h1>
  <a href="/" class="btn border border-chalk-warm bg-white text-ink text-sm px-4 py-2 rounded hover:border-ink">
    Go home
  </a>
</div>
</body>
</html>
