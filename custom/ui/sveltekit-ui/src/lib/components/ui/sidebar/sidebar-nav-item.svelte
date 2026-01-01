<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    active?: boolean;
    collapsed?: boolean;
    href?: string;
    icon?: Snippet;
    badge?: number | string;
    children?: Snippet;
    onclick?: () => void;
  };

  let {
    class: className,
    active = false,
    collapsed = false,
    href,
    icon,
    badge,
    children,
    onclick,
    ...restProps
  }: Props = $props();
</script>

<a
  {href}
  class={cn(
    'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors',
    'text-sidebar-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground',
    active && 'bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 hover:text-sidebar-primary-foreground',
    collapsed && 'justify-center px-0',
    className
  )}
  {onclick}
  {...restProps}
>
  {#if icon}
    <span class="shrink-0">
      {@render icon()}
    </span>
  {/if}
  {#if !collapsed && children}
    <span class="flex-1 truncate">
      {@render children()}
    </span>
  {/if}
  {#if !collapsed && badge !== undefined}
    <span class={cn(
      'shrink-0 h-5 min-w-5 px-1 rounded-full text-xs flex items-center justify-center',
      active ? 'bg-sidebar-primary-foreground/20 text-sidebar-primary-foreground' : 'bg-sidebar-accent text-sidebar-accent-foreground'
    )}>
      {badge}
    </span>
  {/if}
</a>
