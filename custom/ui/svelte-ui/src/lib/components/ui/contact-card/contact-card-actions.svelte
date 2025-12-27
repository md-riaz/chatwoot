<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<div class={cn('mt-4 flex items-center gap-2', className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</div>
