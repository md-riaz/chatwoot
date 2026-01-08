<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    title?: string;
    collapsed?: boolean;
    children?: Snippet;
  };

  let { class: className, title = '', collapsed = false, children, ...restProps }: Props = $props();
</script>

<div class={cn('space-y-1', className)} {...restProps}>
  {#if !collapsed && title}
    <p class="px-3 py-2 text-xs font-semibold text-sidebar-foreground/60 uppercase tracking-wider">
      {title}
    </p>
  {/if}
  {#if children}
    {@render children()}
  {/if}
</div>
