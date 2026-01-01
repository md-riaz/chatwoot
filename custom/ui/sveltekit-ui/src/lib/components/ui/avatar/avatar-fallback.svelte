<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let {
    class: className,
    children,
    ...restProps
  }: Props = $props();
</script>

<span
  class={cn(
    'flex h-full w-full items-center justify-center rounded-full bg-muted text-sm font-medium',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</span>
