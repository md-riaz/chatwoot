<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<div
  class={cn('flex items-center p-2 border-t border-sidebar-border shrink-0', className)}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
