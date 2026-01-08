<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    src?: string;
    alt?: string;
    fallback?: string;
    children?: Snippet;
  };

  let { class: className, src, alt = '', fallback = '', children, ...restProps }: Props = $props();
</script>

<div class={cn('shrink-0 self-end', className)} {...restProps}>
  {#if src}
    <img
      {src}
      {alt}
      class="h-8 w-8 rounded-full object-cover"
    />
  {:else}
    <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center text-xs font-medium">
      {fallback}
    </div>
  {/if}
  {#if children}
    {@render children()}
  {/if}
</div>
