<script lang="ts" module>
  export type ImageGalleryProps = {
    images: string[];
    columns?: number;
    gap?: number;
    class?: string;
    onImageClick?: (index: number) => void;
  };
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  import * as Dialog from '../dialog/index.js';
  
  let {
    images,
    columns = 3,
    gap = 4,
    class: className,
    onImageClick,
    ...restProps
  }: ImageGalleryProps = $props();
  
  let selectedImageIndex = $state<number | null>(null);
  let isLightboxOpen = $state(false);
  
  function openLightbox(index: number) {
    selectedImageIndex = index;
    isLightboxOpen = true;
    
    if (onImageClick) {
      onImageClick(index);
    }
  }
  
  function closeLightbox() {
    isLightboxOpen = false;
  }
  
  function nextImage() {
    if (selectedImageIndex !== null && selectedImageIndex < images.length - 1) {
      selectedImageIndex++;
    }
  }
  
  function previousImage() {
    if (selectedImageIndex !== null && selectedImageIndex > 0) {
      selectedImageIndex--;
    }
  }
  
  function handleKeydown(e: KeyboardEvent) {
    if (!isLightboxOpen) return;
    
    if (e.key === 'ArrowRight') {
      nextImage();
    } else if (e.key === 'ArrowLeft') {
      previousImage();
    } else if (e.key === 'Escape') {
      closeLightbox();
    }
  }
  
  $effect(() => {
    if (isLightboxOpen) {
      window.addEventListener('keydown', handleKeydown);
      return () => {
        window.removeEventListener('keydown', handleKeydown);
      };
    }
  });
</script>

<div class={cn('w-full', className)} {...restProps}>
  <!-- Image Grid -->
  <div
    class="grid gap-{gap}"
    style="grid-template-columns: repeat({columns}, minmax(0, 1fr));"
  >
    {#each images as image, index}
      <button
        type="button"
        class="group relative aspect-square overflow-hidden rounded-lg bg-muted cursor-pointer"
        onclick={() => openLightbox(index)}
      >
        <img
          src={image}
          alt="Gallery image {index + 1}"
          class="size-full object-cover transition-transform group-hover:scale-105"
        />
        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
      </button>
    {/each}
  </div>
  
  <!-- Lightbox -->
  {#if isLightboxOpen && selectedImageIndex !== null}
    <Dialog.Root open={isLightboxOpen} onOpenChange={(open: boolean) => { if (!open) closeLightbox(); }}>
      <Dialog.Content class="max-w-(--breakpoint-lg) p-0">
        <div class="relative">
          <!-- Image -->
          <img
            src={images[selectedImageIndex]}
            alt="Gallery image {selectedImageIndex + 1}"
            class="w-full h-auto max-h-[80vh] object-contain"
          />
          
          <!-- Navigation Buttons -->
          {#if selectedImageIndex > 0}
            <button
              type="button"
              class="absolute left-4 top-1/2 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
              aria-label="Previous image"
              onclick={previousImage}
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polyline points="15 18 9 12 15 6" />
              </svg>
            </button>
          {/if}
          
          {#if selectedImageIndex < images.length - 1}
            <button
              type="button"
              class="absolute right-4 top-1/2 -translate-y-1/2 flex size-10 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors"
              aria-label="Next image"
              onclick={nextImage}
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <polyline points="9 18 15 12 9 6" />
              </svg>
            </button>
          {/if}
          
          <!-- Counter -->
          <div class="absolute bottom-4 left-1/2 -translate-x-1/2 rounded-full bg-black/50 px-4 py-2 text-sm text-white">
            {selectedImageIndex + 1} / {images.length}
          </div>
        </div>
      </Dialog.Content>
    </Dialog.Root>
  {/if}
</div>
