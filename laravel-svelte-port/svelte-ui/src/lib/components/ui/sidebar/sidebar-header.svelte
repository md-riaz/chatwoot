<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    collapsed?: boolean;
    children?: Snippet;
  };

  let { class: className, collapsed = false, children, ...restProps }: Props = $props();
</script>

<div
  class={cn('flex items-center h-14 px-4 border-b border-sidebar-border shrink-0', className)}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
