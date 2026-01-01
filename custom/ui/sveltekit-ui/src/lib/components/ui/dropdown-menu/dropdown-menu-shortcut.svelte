<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<span
  class={cn('ml-auto text-xs tracking-widest opacity-60', className)}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</span>
