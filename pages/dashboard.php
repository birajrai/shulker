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

<div x-data="shulker()" x-init="init()" class="min-h-screen flex flex-col justify-between">

  <!-- ─── Nav ──────────────────────────────────────────── -->
  <nav class="border-b-4 border-black bg-shulker-deep sticky top-0 z-40 px-4 sm:px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <img src="/shulker.webp" alt="Shulker Box" class="w-6 h-6 pixelated">
      <span class="font-retro text-xs tracking-tight text-white mc-shadow">
        shulker
      </span>
    </div>

    <div class="flex items-center gap-3">
      <!-- Storage chip -->
      <div class="flex items-center gap-2 border-2 border-black bg-shulker-dark px-2.5 py-1 text-white">
        <?php if ($avatar_url): ?>
        <img src="<?= $avatar_url ?>" alt="" class="w-4 h-4 border border-black pixelated" loading="lazy">
        <?php else: ?>
        <div class="w-4 h-4 bg-stone-light flex items-center justify-center text-ink text-[10px] font-bold border border-black">
          <?= mb_substr($display_name, 0, 1) ?>
        </div>
        <?php endif; ?>
        <span class="font-mono text-xs text-gray-200" x-text="images.length + ' / <?= MAX_IMAGES_PER_USER ?>'"></span>
      </div>

      <a href="/logout" class="mc-btn mc-btn-red text-[10px] py-1.5 px-3">
        Sign out
      </a>
    </div>
  </nav>

  <!-- ─── Main ─────────────────────────────────────────── -->
  <main class="flex-1 px-4 sm:px-6 py-8 max-w-5xl mx-auto w-full z-10">

    <div class="mc-panel p-6 sm:p-8">
      
      <!-- Upload zone -->
      <div
        class="upload-zone border-4 border-dashed border-shulker-light/50 bg-shulker-deep/20 rounded-none mb-8 cursor-pointer relative"
        @click="$clickUpload($event)"
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

        <div class="py-12 px-6 flex flex-col items-center gap-4 text-center" x-show="!uploading">
          <div class="w-14 h-14 border-2 border-black bg-shulker/30 flex items-center justify-center text-white shadow-inner relative group">
            <img src="/shulker.webp" alt="Shulker Box" class="w-10 h-10 pixelated transform group-hover:scale-110 transition-transform duration-200">
          </div>
          <div>
            <p class="font-retro text-[10px] sm:text-xs text-yellow-300 mc-shadow mb-1">
              Drop images here, or <span class="text-white underline underline-offset-4">click to browse</span>
            </p>
            <p class="font-mono text-xs text-gray-400 mt-2">JPG · PNG · WebP · AVIF · GIF &nbsp;·&nbsp; 5 MB max</p>
          </div>
        </div>

        <!-- Upload progress -->
        <div class="py-8 px-6 flex flex-col items-center gap-4 w-full" x-show="uploading" x-cloak>
          <div class="w-full max-w-sm">
            <div class="flex justify-between items-center mb-2 font-mono text-xs text-gray-300">
              <span x-text="uploadStatusText"></span>
              <span x-text="Math.round(uploadProgress) + '%'"></span>
            </div>
            <div class="h-4 bg-black border-2 border-gray-600 p-[2px] overflow-hidden">
              <div class="progress-bar h-full bg-green-500" :style="'width:' + uploadProgress + '%'"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Toast -->
      <div
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 p-4 border-4 border-black mc-shadow text-xs font-retro text-center min-w-[250px]"
        :class="toast.error ? 'bg-red-900 text-red-200 border-red-950 shadow-[inset_-2px_-2px_0px_#5e1c1c,inset_2px_2px_0px_#f06a6a]' : 'bg-shulker text-white border-black shadow-[inset_-2px_-2px_0px_#4a2c4a,inset_2px_2px_0px_#ac7cac]'"
        x-text="toast.message"
      ></div>

      <!-- Gallery header -->
      <div class="flex items-center justify-between mb-4 mt-8">
        <h2 class="font-retro text-xs text-yellow-300 mc-shadow uppercase tracking-widest">Shulker Inventory</h2>
        <span class="font-mono text-xs text-gray-400" x-text="images.length + ' item(s)'"></span>
      </div>

      <!-- Loading skeleton -->
      <div x-show="loading" class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-9 gap-2 bg-shulker-deep/30 border-4 border-black p-4" x-cloak>
        <template x-for="i in 27" :key="i">
          <div class="aspect-square bg-black/40 border-2 border-black shadow-[inset_2px_2px_0px_#070407] animate-pulse"></div>
        </template>
      </div>

      <!-- Inventory Grid -->
      <div
        x-show="!loading"
        x-cloak
        class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-9 gap-2 bg-shulker-deep/50 border-4 border-black p-4 shadow-inner"
      >
        <template x-for="index in getTotalSlots()" :key="index">
          <div class="aspect-square">
            
            <!-- Case 1: Slot has an uploaded image -->
            <template x-if="index - 1 < images.length">
              <div 
                class="mc-slot group cursor-pointer relative w-full h-full"
                @click="copyUrl(images[index - 1])"
              >
                <!-- Preview -->
                <template x-if="images[index - 1].type === 'webm'">
                  <video
                    :src="images[index - 1].url"
                    class="w-full h-full object-cover pixelated"
                    autoplay loop muted playsinline
                  ></video>
                </template>
                <template x-if="images[index - 1].type !== 'webm'">
                  <img
                    :src="images[index - 1].url"
                    :alt="images[index - 1].id"
                    class="w-full h-full object-cover pixelated"
                    loading="lazy"
                  >
                </template>
                
                <div class="mc-slot-highlight absolute inset-0 pointer-events-none"></div>

                <!-- Copy checkmark indicator -->
                <div x-show="images[index - 1].copied" class="absolute inset-0 bg-green-900/90 flex flex-col items-center justify-center text-center p-1 z-10" x-cloak>
                  <svg class="w-6 h-6 text-green-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span class="font-retro text-[8px] text-green-300 mt-1 mc-shadow">COPIED</span>
                </div>

                <!-- Delete progress spinner -->
                <div x-show="images[index - 1].deleting" class="absolute inset-0 bg-red-900/90 flex items-center justify-center z-10" x-cloak>
                  <svg class="w-6 h-6 text-red-400 spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                  </svg>
                </div>

                <!-- Hover overlays for actions -->
                <div class="absolute inset-x-0 bottom-0 bg-black/85 p-1 flex justify-between opacity-0 group-hover:opacity-100 transition-opacity z-20 pointer-events-auto" @click.stop>
                  <button @click.stop="copyUrl(images[index - 1])" class="w-6 h-6 bg-green-700 hover:bg-green-600 border border-black flex items-center justify-center text-white" title="Copy URL">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                  </button>
                  <button @click.stop="deleteImage(images[index - 1])" class="w-6 h-6 bg-red-700 hover:bg-red-600 border border-black flex items-center justify-center text-white" title="Delete Image">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    </svg>
                  </button>
                </div>

                <!-- Minecraft Tooltip -->
                <div class="mc-tooltip hidden group-hover:block absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 pointer-events-none p-3 text-left font-mono">
                  <div class="font-retro text-[9px] text-yellow-300 mb-1 truncate" x-text="'SHULKER_' + images[index - 1].id.substring(0, 8).toUpperCase()"></div>
                  <div class="text-[10px] text-cyan-300 font-bold mb-1" x-text="images[index - 1].type.toUpperCase() + ' Item'"></div>
                  <div class="text-[10px] text-gray-300" x-text="'Size: ' + formatSize(images[index - 1].size)"></div>
                  <div class="text-[10px] text-gray-400" x-text="'Date: ' + formatDate(images[index - 1].created)"></div>
                  <div class="border-t border-purple-900/50 my-1.5"></div>
                  <div class="text-[9px] text-purple-300 font-bold">Left-Click to Copy Link</div>
                  <div class="text-[9px] text-red-300 font-bold">Overlays to Delete</div>
                </div>

              </div>
            </template>

            <!-- Case 2: Slot is empty -->
            <template x-if="index - 1 >= images.length">
              <div class="mc-slot mc-slot-empty w-full h-full pointer-events-none">
                <div class="w-4 h-4 bg-black/25 border border-black/10"></div>
              </div>
            </template>

          </div>
        </template>
      </div>

    </div>

  </main>

  <footer class="border-t-4 border-black bg-shulker-deep px-6 py-4 text-center mt-8 z-10">
    <span class="font-mono text-xs text-gray-400 mc-shadow">shulker box v<?= SHULKER_VERSION ?></span>
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

    getTotalSlots() {
      return Math.max(27, Math.ceil(this.images.length / 9) * 9);
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

    $clickUpload(e) {
      // Avoid clicking input when inner elements with their own event triggers are clicked
      if (e.target.closest('input')) return;
      this.$refs.fileInput.click();
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
          if (files.length === 1) this.showToast('Uploaded successfully!');
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
        this.showToast('Link copied to clipboard!');
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
        this.showToast('Link copied to clipboard!');
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
          this.showToast('Deleted item successfully.');
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
