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
  class={cn('flex items-center justify-between pt-2 border-t mt-2', className)}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
