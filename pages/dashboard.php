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

<div 
  x-data="shulker()" 
  x-init="init()" 
  class="h-screen w-screen overflow-hidden flex flex-col justify-between select-none relative"
  @dragover.prevent="dragOver = true"
  @dragleave.prevent="dragOver = false"
  @drop.prevent="handleDrop($event)"
>

  <!-- Hidden File Input for Global Click Triggers -->
  <input
    type="file"
    x-ref="fileInput"
    class="hidden"
    accept="image/jpeg,image/png,image/webp,image/avif,image/gif"
    multiple
    @change="handleFileInput($event)"
  >

  <!-- Full-Screen Drag & Drop Overlay -->
  <div 
    x-show="dragOver" 
    class="fixed inset-0 bg-shulker-deep/85 border-[6px] border-dashed border-shulker-light z-50 flex items-center justify-center pointer-events-none"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
  >
    <div class="text-center p-6">
      <img src="/shulker.webp" class="w-20 h-20 mx-auto pixelated animate-bounce mb-4">
      <p class="font-black text-2xl text-yellow-300 uppercase tracking-widest">
        Drop items into Shulker Box
      </p>
      <p class="font-normal text-sm text-gray-400 mt-2">
        Release to store directly in empty inventory slots
      </p>
    </div>
  </div>

  <!-- ─── Nav ──────────────────────────────────────────── -->
  <nav class="border-b-4 border-black bg-shulker-deep px-6 py-3.5 flex items-center justify-between flex-shrink-0 z-40">
    <div class="flex items-center gap-3">
      <img src="/shulker.webp" alt="Shulker Box" class="w-7 h-7 pixelated">
      <span class="font-black text-sm tracking-wider text-white uppercase">
        shulker
      </span>
    </div>

    <div class="flex items-center gap-4">
      <!-- Storage chip -->
      <div class="flex items-center gap-2 border-2 border-black bg-shulker-dark px-3 py-1.5 text-white">
        <?php if ($avatar_url): ?>
        <img src="<?= $avatar_url ?>" alt="" class="w-5 h-5 border border-black pixelated" loading="lazy">
        <?php else: ?>
        <div class="w-5 h-5 bg-stone-light flex items-center justify-center text-ink text-[10px] font-black border border-black uppercase">
          <?= mb_substr($display_name, 0, 1) ?>
        </div>
        <?php endif; ?>
        <span class="font-medium text-xs tracking-wider text-gray-200" x-text="images.length + ' / <?= MAX_IMAGES_PER_USER ?>'"></span>
      </div>

      <button @click="$refs.fileInput.click()" class="mc-btn mc-btn-shulker text-xs py-1.5 px-4">
        Add Item
      </button>

      <a href="/logout" class="mc-btn mc-btn-red text-xs py-1.5 px-4">
        Sign out
      </a>
    </div>
  </nav>

  <!-- ─── Main Content (Vertically & Horizontally Centered) ─── -->
  <main class="flex-1 flex items-center justify-center p-6 overflow-hidden min-h-0 z-10">

    <div class="mc-panel p-6 sm:p-8 max-w-4xl w-full flex flex-col justify-center">

      <!-- Compact header -->
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-black text-sm text-yellow-300 uppercase tracking-widest">Shulker Box Inventory</h2>
        <span class="font-medium text-xs text-gray-400 uppercase tracking-widest" x-text="images.length + ' / 27 Slots'"></span>
      </div>

      <!-- Toast Alerts inside GUI -->
      <div
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-cloak
        class="fixed bottom-5 left-1/2 -translate-x-1/2 z-50 p-4 border-4 border-black text-xs font-black tracking-wide text-center min-w-[280px]"
        :class="toast.error ? 'bg-red-900 text-red-100 border-red-950 shadow-[inset_-2px_-2px_0px_#5e1c1c,inset_2px_2px_0px_#f06a6a]' : 'bg-shulker text-white border-black shadow-[inset_-2px_-2px_0px_#4a2c4a,inset_2px_2px_0px_#ac7cac]'"
        x-text="toast.message"
      ></div>

      <!-- Loading skeleton -->
      <div x-show="loading" class="grid grid-cols-3 sm:grid-cols-9 gap-3 bg-shulker-deep/30 border-4 border-black p-4" x-cloak>
        <template x-for="i in 27" :key="i">
          <div class="aspect-square bg-black/45 border-2 border-black animate-pulse"></div>
        </template>
      </div>

      <!-- Inventory Grid (Strictly 27 slots on desktop/tablet) -->
      <div
        x-show="!loading"
        x-cloak
        class="grid grid-cols-3 sm:grid-cols-9 gap-3 bg-shulker-deep/40 border-4 border-black p-4 shadow-inner min-h-0 overflow-y-auto sm:overflow-visible"
      >
        <template x-for="index in 27" :key="index">
          <div class="aspect-square relative group">
            
            <!-- Case 1: Slot has an uploaded image -->
            <template x-if="index - 1 < images.length">
              <div class="w-full h-full">
                
                <!-- Subcase 1a: Slot is pending upload in background -->
                <div 
                  x-show="images[index - 1].isPending"
                  class="mc-slot relative w-full h-full cursor-not-allowed"
                >
                  <img :src="images[index - 1].url" class="w-full h-full object-cover pixelated opacity-30">
                  <div class="absolute inset-0 bg-black/70 flex flex-col items-center justify-center p-1">
                    <svg class="w-6 h-6 text-yellow-400 spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    <span class="font-black text-[9px] text-yellow-300 mt-2 tracking-wider" x-text="Math.round(images[index - 1].progress) + '%'"></span>
                  </div>
                </div>

                <!-- Subcase 1b: Slot has completed upload -->
                <div 
                  x-show="!images[index - 1].isPending"
                  class="mc-slot group cursor-pointer relative w-full h-full"
                  @click="copyUrl(images[index - 1])"
                  @mouseenter="showTooltip($event, images[index - 1])"
                  @mousemove="moveTooltip($event)"
                  @mouseleave="hideTooltip()"
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
                    <span class="font-black text-[8px] text-green-300 mt-1 uppercase tracking-wider">COPIED</span>
                  </div>

                  <!-- Delete progress spinner -->
                  <div x-show="images[index - 1].deleting" class="absolute inset-0 bg-red-900/90 flex items-center justify-center z-10" x-cloak>
                    <svg class="w-6 h-6 text-red-400 spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                  </div>

                  <!-- Hover overlays for actions -->
                  <div class="absolute inset-x-0 bottom-0 bg-black/85 p-1 flex justify-between opacity-0 group-hover:opacity-100 transition-opacity z-20 pointer-events-auto" @click.stop>
                    <button @click.stop="copyUrl(images[index - 1])" class="w-6 h-6 bg-green-700 hover:bg-green-600 border-2 border-black flex items-center justify-center text-white" title="Copy URL">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                      </svg>
                    </button>
                    <button @click.stop="deleteImage(images[index - 1])" class="w-6 h-6 bg-red-700 hover:bg-red-600 border-2 border-black flex items-center justify-center text-white" title="Delete Image">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                      </svg>
                    </button>
                  </div>

                </div>
              </div>
            </template>

            <!-- Case 2: Slot is empty -->
            <template x-if="index - 1 >= images.length">
              <div 
                class="mc-slot mc-slot-empty w-full h-full cursor-pointer flex items-center justify-center hover:bg-black/40 hover:border-shulker-light/30 transition-colors pointer-events-auto"
                @click="$refs.fileInput.click()"
                title="Click to Upload File"
              >
                <!-- Large clean plus icon inside slot -->
                <svg class="w-5 h-5 text-white/10 group-hover:text-white/30 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <line x1="12" y1="5" x2="12" y2="19" />
                  <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
              </div>
            </template>

          </div>
        </template>
      </div>

    </div>

  </main>

  <!-- Dummy spacer for alignment without footer -->
  <div class="h-6 flex-shrink-0"></div>

  <!-- Global Minecraft Tooltip (Mouse-following, Responsive & Boundary Aware) -->
  <div 
    x-show="activeTooltip.show" 
    class="mc-tooltip fixed pointer-events-none z-50 w-60"
    :style="`left: ${activeTooltip.x}px; top: ${activeTooltip.y}px;`"
    x-cloak
  >
    <div class="font-black text-sm text-yellow-300 mb-1.5 truncate" x-text="activeTooltip.title"></div>
    <div class="text-xs text-cyan-300 font-bold mb-1.5" x-text="activeTooltip.type + ' ITEM'"></div>
    <div class="text-xs text-gray-300 font-medium" x-text="'SIZE: ' + activeTooltip.size"></div>
    <div class="text-xs text-gray-400 font-medium" x-text="'DATE: ' + activeTooltip.date"></div>
    <div class="border-t border-purple-900/50 my-2"></div>
    <div class="text-[10px] text-purple-300 font-black tracking-wider uppercase">Left-Click to Copy Link</div>
    <div class="text-[10px] text-red-300 font-black tracking-wider uppercase">Overlays to Delete</div>
  </div>

</div>

<!-- ─── Alpine.js App ──────────────────────────────────── -->
<script>
function shulker() {
  return {
    images:           [],
    loading:          true,
    dragOver:         false,
    toast:            { show: false, message: '', error: false },
    activeTooltip:    { show: false, x: 0, y: 0, title: '', type: '', size: '', date: '' },
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
        // Constrain local state to max 27 items
        this.images = (d.images || []).slice(0, 27).map(img => ({ ...img, copied: false, deleting: false, isPending: false }));
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
      // Enforce absolute cap of 27 uploads
      if (this.images.length >= 27) {
        this.showToast('Your Shulker Box is full! Delete items to add more.', true);
        return;
      }

      // Filter to only upload up to slot limit
      const slotsLeft = 27 - this.images.length;
      const filesToUpload = Array.from(files).slice(0, slotsLeft);

      if (files.length > slotsLeft) {
        this.showToast(`Only space for ${slotsLeft} item(s). Excluded the rest.`, true);
      }

      // Create concurrent parallel uploads!
      const uploadPromises = filesToUpload.map(async (file) => {
        // Generate placeholder item
        const tempId = 'pending-' + Math.random().toString(36).substr(2, 9);
        const pendingItem = {
          id: tempId,
          isPending: true,
          type: file.type.includes('webm') ? 'webm' : (file.type.split('/')[1] || 'png'),
          url: URL.createObjectURL(file),
          size: file.size,
          created: Math.floor(Date.now() / 1000),
          progress: 0,
          copied: false,
          deleting: false
        };

        // Prepend uploader grid item instantly for visual feedback
        this.images.unshift(pendingItem);

        // Perform parallel upload
        const result = await this.uploadOne(file, p => {
          pendingItem.progress = p;
        });

        if (result.ok) {
          // Replace placeholder item with server record
          const idx = this.images.findIndex(img => img.id === tempId);
          if (idx !== -1) {
            this.images[idx] = {
              id:       result.id,
              type:     result.type,
              url:      result.url,
              size:     result.size,
              created:  result.created,
              copied:   false,
              deleting: false,
              isPending: false
            };
          }
          this.showToast('Uploaded item successfully.');
        } else {
          // Remove placeholder on fail
          this.images = this.images.filter(img => img.id !== tempId);
          this.showToast(result.error || `Upload failed for ${file.name}`, true);
        }

        // Revoke temporary object URL to prevent memory leaks
        URL.revokeObjectURL(pendingItem.url);
      });

      // Fire parallel uploads in the background!
      await Promise.all(uploadPromises);
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
      if (img.isPending) return;
      this.hideTooltip();
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
      if (img.deleting || img.isPending) return;
      this.hideTooltip();
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

    showTooltip(e, img) {
      if (img.isPending) return;
      this.activeTooltip.title = 'SHULKER_' + img.id.substring(0, 8).toUpperCase();
      this.activeTooltip.type = img.type.toUpperCase();
      this.activeTooltip.size = this.formatSize(img.size);
      this.activeTooltip.date = this.formatDate(img.created);
      this.activeTooltip.show = true;
      this.moveTooltip(e);
    },

    moveTooltip(e) {
      const tooltipW = 240;
      const tooltipH = 160;
      let x = e.clientX + 15;
      let y = e.clientY + 15;

      // Bound checking so tooltip is never off-screen
      if (x + tooltipW > window.innerWidth) {
        x = e.clientX - tooltipW - 15;
      }
      if (y + tooltipH > window.innerHeight) {
        y = e.clientY - tooltipH - 15;
      }

      this.activeTooltip.x = x;
      this.activeTooltip.y = y;
    },

    hideTooltip() {
      this.activeTooltip.show = false;
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
