<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    selected?: boolean;
    unread?: boolean;
    children?: Snippet;
    onclick?: () => void;
  };

  let {
    class: className,
    selected = false,
    unread = false,
    children,
    onclick,
    ...restProps
  }: Props = $props();
</script>

<div
  class={cn(
    'flex flex-col gap-2 p-3 cursor-pointer transition-colors border-l-2 border-l-transparent',
    'hover:bg-accent',
    selected && 'bg-accent border-l-primary',
    unread && 'bg-primary/5',
    className
  )}
  role="button"
  tabindex="0"
  {onclick}
  onkeydown={(e) => e.key === 'Enter' && onclick?.()}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
