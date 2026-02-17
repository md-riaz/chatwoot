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
    'flex flex-col gap-2 p-4 cursor-pointer transition-all border-l-[3px] border-l-transparent select-none relative',
    'hover:bg-slate-50 dark:hover:bg-slate-800/50',
    selected &&
      'bg-primary/5 dark:bg-primary/10 border-l-primary shadow-sm z-10',
    unread && !selected && 'bg-white dark:bg-slate-900',
    className
  )}
  role="button"
  tabindex="0"
  {onclick}
  onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && onclick?.()}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
