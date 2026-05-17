<?php
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/layout_head.php';
require_once __DIR__ . '/../includes/image_processor.php';

$user = require_auth();
heal_user_permissions($user['id']);

// Avatar URL
$avatar_url = '';
if ($user['avatar']) {
    $avatar_url = 'https://cdn.discordapp.com/avatars/' . h($user['id']) . '/' . h($user['avatar']) . '.webp?size=64';
}
$display_name = h($user['global_name'] ?: $user['username']);

layout_head('Dashboard');
?>

<div x-data="shulker()" x-init="init()" class="min-h-screen flex flex-col">

  <!-- ─── Nav ──────────────────────────────────────────── -->
  <nav class="border-b border-chalk-warm bg-chalk sticky top-0 z-40 px-4 sm:px-6 py-3 flex items-center justify-between">
    <span class="font-mono text-sm font-500 text-ink">
      <span class="text-rust">▪</span> shulker
    </span>

    <div class="flex items-center gap-3">
      <!-- Storage counter -->
      <span class="font-mono text-xs text-stone" x-text="images.length + ' / <?= MAX_IMAGES_PER_USER ?>'"></span>

      <!-- User chip -->
      <div class="flex items-center gap-2 border border-chalk-warm bg-white rounded px-2.5 py-1.5">
        <?php if ($avatar_url): ?>
        <img src="<?= $avatar_url ?>" alt="" class="w-5 h-5 rounded-full" loading="lazy">
        <?php else: ?>
        <div class="w-5 h-5 rounded-full bg-stone-light flex items-center justify-center text-ink text-xs font-500">
          <?= mb_substr($display_name, 0, 1) ?>
        </div>
        <?php endif; ?>
        <span class="text-xs text-ink font-400 max-w-[120px] truncate"><?= $display_name ?></span>
      </div>

      <a href="/logout" class="btn border border-chalk-warm bg-white text-stone text-xs px-3 py-1.5 rounded hover:border-ink hover:text-ink">
        Sign out
      </a>
    </div>
  </nav>

  <!-- ─── Main ─────────────────────────────────────────── -->
  <main class="flex-1 px-4 sm:px-6 py-8 max-w-5xl mx-auto w-full">

    <!-- Upload zone -->
    <div
      class="upload-zone border-2 border-dashed border-chalk-warm rounded-md bg-white mb-8 cursor-pointer"
      @click="$refs.fileInput.click()"
      @dragover.prevent="dragOver = true"
      @dragleave.prevent="dragOver = false"
      @drop.prevent="handleDrop($event)"
      :class="{ 'drag-over': dragOver }"
    >
      <input
        type="file"
        x-ref="fileInput"
        class="hidden"
        accept="image/jpeg,image/png,image/webp,image/avif,image/gif"
        multiple
        @change="handleFileInput($event)"
      >

      <div class="py-10 px-6 flex flex-col items-center gap-3" x-show="!uploading">
        <div class="w-12 h-12 border border-chalk-warm rounded-md flex items-center justify-center text-stone mb-1">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
          </svg>
        </div>
        <p class="text-ink text-sm font-400">Drop images here, or <span class="text-rust underline underline-offset-2">click to browse</span></p>
        <p class="font-mono text-xs text-stone">JPG · PNG · WebP · AVIF · GIF · Animated WebP &nbsp;·&nbsp; 5 MB max</p>
      </div>

      <!-- Upload progress -->
      <div class="py-8 px-6 flex flex-col items-center gap-4 w-full" x-show="uploading" x-cloak>
        <div class="w-full max-w-sm">
          <div class="flex justify-between items-center mb-2">
            <span class="font-mono text-xs text-stone" x-text="uploadStatusText"></span>
            <span class="font-mono text-xs text-stone" x-text="Math.round(uploadProgress) + '%'"></span>
          </div>
          <div class="h-1 bg-chalk-warm rounded-full overflow-hidden">
            <div class="progress-bar h-full bg-rust rounded-full" :style="'width:' + uploadProgress + '%'"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div
      x-show="toast.show"
      x-transition:enter="transition ease-out duration-150"
      x-transition:enter-start="opacity-0 translate-y-1"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-100"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0"
      x-cloak
      class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 border rounded px-4 py-2.5 text-sm font-400 shadow-sm"
      :class="toast.error ? 'bg-white border-rust text-rust' : 'bg-ink text-chalk border-ink'"
      x-text="toast.message"
    ></div>

    <!-- Gallery header -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-sm font-mono text-stone uppercase tracking-widest">Uploads</h2>
      <span class="font-mono text-xs text-stone" x-show="images.length === 0" x-cloak>empty</span>
    </div>

    <!-- Loading skeleton -->
    <div x-show="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3" x-cloak>
      <template x-for="i in 8" :key="i">
        <div class="aspect-square bg-chalk-warm rounded-md animate-pulse"></div>
      </template>
    </div>

    <!-- Empty state -->
    <div x-show="!loading && images.length === 0" x-cloak class="py-20 text-center">
      <p class="text-stone text-sm font-300">No images yet. Upload something above.</p>
    </div>

    <!-- Image grid -->
    <div
      x-show="!loading && images.length > 0"
      x-cloak
      class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
    >
      <template x-for="img in images" :key="img.id">
        <div class="img-card border border-chalk-warm bg-white rounded-md overflow-hidden flex flex-col fade-up">

          <!-- Preview -->
          <div class="aspect-square bg-chalk-warm relative overflow-hidden">
            <template x-if="img.type === 'webm'">
              <video
                :src="img.url"
                class="w-full h-full object-cover"
                autoplay loop muted playsinline
              ></video>
            </template>
            <template x-if="img.type !== 'webm'">
              <img
                :src="img.url"
                :alt="img.id"
                class="w-full h-full object-cover"
                loading="lazy"
              >
            </template>
          </div>

          <!-- Card footer -->
          <div class="px-2.5 py-2 flex flex-col gap-2">
            <div class="flex items-center gap-1">
              <span class="font-mono text-[10px] text-stone uppercase" x-text="img.type"></span>
              <span class="font-mono text-[10px] text-stone-light">·</span>
              <span class="font-mono text-[10px] text-stone" x-text="formatSize(img.size)"></span>
            </div>
            <p class="font-mono text-[10px] text-stone-light" x-text="formatDate(img.created)"></p>

            <div class="flex gap-1.5 mt-0.5">
              <!-- Copy URL -->
              <button
                @click="copyUrl(img)"
                class="btn flex-1 border border-chalk-warm bg-chalk text-stone text-xs py-1.5 rounded hover:border-ink hover:text-ink flex items-center justify-center gap-1"
                :class="{ 'border-sage text-sage': img.copied }"
              >
                <svg x-show="!img.copied" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
                <svg x-show="img.copied" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" x-cloak>
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span x-text="img.copied ? 'Copied' : 'Copy'"></span>
              </button>

              <!-- Delete -->
              <button
                @click="deleteImage(img)"
                class="btn border border-chalk-warm bg-chalk text-stone text-xs px-2.5 py-1.5 rounded hover:border-rust hover:text-rust"
                :disabled="img.deleting"
                :class="{ 'opacity-50 cursor-not-allowed': img.deleting }"
              >
                <svg x-show="!img.deleting" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                  <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                </svg>
                <svg x-show="img.deleting" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spinner" x-cloak>
                  <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>

  </main>

  <footer class="border-t border-chalk-warm px-6 py-4 mt-8">
    <span class="font-mono text-xs text-stone">shulker <?= SHULKER_VERSION ?></span>
  </footer>

</div>

<!-- ─── Alpine.js App ──────────────────────────────────── -->
<script>
function shulker() {
  return {
    images:           [],
    loading:          true,
    uploading:        false,
    uploadProgress:   0,
    uploadStatusText: '',
    dragOver:         false,
    toast:            { show: false, message: '', error: false },
    _toastTimer:      null,

    async init() {
      await this.loadImages();
      this.setupPaste();
    },

    async loadImages() {
      this.loading = true;
      try {
        const r = await fetch('/api/images');
        const d = await r.json();
        this.images = (d.images || []).map(img => ({ ...img, copied: false, deleting: false }));
      } catch(e) {
        this.showToast('Failed to load images.', true);
      } finally {
        this.loading = false;
      }
    },

    setupPaste() {
      document.addEventListener('paste', (e) => {
        const items = e.clipboardData?.items;
        if (!items) return;
        const files = [];
        for (const item of items) {
          if (item.kind === 'file' && item.type.startsWith('image/')) {
            const f = item.getAsFile();
            if (f) files.push(f);
          }
        }
        if (files.length > 0) this.uploadFiles(files);
      });
    },

    handleFileInput(event) {
      const files = Array.from(event.target.files || []);
      if (files.length) this.uploadFiles(files);
      event.target.value = '';
    },

    handleDrop(event) {
      this.dragOver = false;
      const files = Array.from(event.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'));
      if (files.length) this.uploadFiles(files);
    },

    async uploadFiles(files) {
      if (this.uploading) return;
      this.uploading = true;

      for (let i = 0; i < files.length; i++) {
        const file = files[i];
        this.uploadStatusText = files.length > 1
          ? `Uploading ${i + 1} of ${files.length}…`
          : `Uploading ${file.name || 'image'}…`;
        this.uploadProgress = 0;

        const result = await this.uploadOne(file, p => { this.uploadProgress = p; });

        if (result.ok) {
          const newImg = {
            id:       result.id,
            type:     result.type,
            url:      result.url,
            size:     result.size,
            created:  result.created,
            copied:   false,
            deleting: false,
          };
          this.images.unshift(newImg);
          if (files.length === 1) this.showToast('Uploaded!');
        } else {
          this.showToast(result.error || 'Upload failed.', true);
        }
      }

      if (files.length > 1) this.showToast(`${files.length} images uploaded.`);
      this.uploading = false;
      this.uploadProgress = 0;
    },

    uploadOne(file, onProgress) {
      return new Promise(resolve => {
        const fd = new FormData();
        fd.append('file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/upload');
        xhr.setRequestHeader('X-Requested-With', 'ShulkerUpload');

        xhr.upload.onprogress = e => {
          if (e.lengthComputable) onProgress((e.loaded / e.total) * 90);
        };

        xhr.onload = () => {
          onProgress(100);
          try {
            const d = JSON.parse(xhr.responseText);
            resolve(d.ok ? d : { ok: false, error: d.error || 'Upload failed.' });
          } catch {
            resolve({ ok: false, error: 'Server error.' });
          }
        };

        xhr.onerror = () => resolve({ ok: false, error: 'Network error.' });
        xhr.send(fd);
      });
    },

    async copyUrl(img) {
      try {
        await navigator.clipboard.writeText(img.url);
        img.copied = true;
        setTimeout(() => { img.copied = false; }, 2000);
      } catch {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = img.url;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        img.copied = true;
        setTimeout(() => { img.copied = false; }, 2000);
      }
    },

    async deleteImage(img) {
      if (img.deleting) return;
      img.deleting = true;

      try {
        const r = await fetch('/delete', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'ShulkerUpload',
          },
          body: JSON.stringify({ id: img.id }),
        });
        const d = await r.json();
        if (d.ok) {
          this.images = this.images.filter(i => i.id !== img.id);
          this.showToast('Deleted.');
        } else {
          img.deleting = false;
          this.showToast(d.error || 'Delete failed.', true);
        }
      } catch {
        img.deleting = false;
        this.showToast('Network error.', true);
      }
    },

    showToast(message, error = false) {
      clearTimeout(this._toastTimer);
      this.toast = { show: true, message, error };
      this._toastTimer = setTimeout(() => { this.toast.show = false; }, 3000);
    },

    formatSize(bytes) {
      if (!bytes) return '–';
      if (bytes < 1024) return bytes + ' B';
      if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
      return (bytes / 1048576).toFixed(1) + ' MB';
    },

    formatDate(ts) {
      if (!ts) return '';
      const d = new Date(ts * 1000);
      return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },
  };
}
</script>
</body>
</html>
