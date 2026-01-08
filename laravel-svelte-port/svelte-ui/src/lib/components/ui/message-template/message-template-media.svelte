<script lang="ts">
  import { cn } from '$lib/utils';

  interface Props {
    type: 'image' | 'video' | 'document' | 'audio';
    url: string;
    caption?: string;
    filename?: string;
    class?: string;
  }

  let { type, url, caption, filename, class: className }: Props = $props();
</script>

<div class={cn('max-w-sm rounded-lg overflow-hidden bg-woot-50 dark:bg-woot-900', className)}>
  {#if type === 'image'}
    <img src={url} alt={caption || 'Image'} class="w-full h-auto" />
  {:else if type === 'video'}
    <video src={url} controls class="w-full h-auto">
      <track kind="captions" />
    </video>
  {:else if type === 'audio'}
    <div class="p-3">
      <audio src={url} controls class="w-full">
        <track kind="captions" />
      </audio>
    </div>
  {:else}
    <div class="p-3 flex items-center gap-2">
      <svg class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <span class="text-sm text-slate-700 dark:text-slate-300">{filename || 'Document'}</span>
    </div>
  {/if}
  {#if caption}
    <div class="p-3 text-sm text-slate-700 dark:text-slate-300">{caption}</div>
  {/if}
</div>
