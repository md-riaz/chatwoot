<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Attachment = {
    name: string;
    size: string;
    type: 'file' | 'image';
  };

  type Props = {
    class?: string;
    attachments?: Attachment[];
    onRemove?: (index: number) => void;
    children?: Snippet;
  };

  let { class: className, attachments = [], onRemove, children, ...restProps }: Props = $props();
</script>

{#if attachments.length > 0 || children}
  <div class={cn('flex flex-wrap gap-2 pb-2 border-b mb-2', className)} {...restProps}>
    {#if children}
      {@render children()}
    {:else}
      {#each attachments as attachment, i}
        <div class="flex items-center gap-2 px-2 py-1 rounded bg-muted text-sm">
          {#if attachment.type === 'image'}
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted-foreground"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          {:else}
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-muted-foreground"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          {/if}
          <span class="truncate max-w-[120px]">{attachment.name}</span>
          <span class="text-xs text-muted-foreground">{attachment.size}</span>
          <button
            type="button"
            class="text-muted-foreground hover:text-foreground"
            onclick={() => onRemove?.(i)}
            aria-label="Remove attachment"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
      {/each}
    {/if}
  </div>
{/if}
