<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<nav class={cn('flex-1 overflow-y-auto p-2 space-y-1', className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</nav>
