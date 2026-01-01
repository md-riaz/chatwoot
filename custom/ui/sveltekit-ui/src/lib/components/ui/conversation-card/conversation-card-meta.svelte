<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    time?: string;
    unreadCount?: number;
    children?: Snippet;
  };

  let {
    class: className,
    time = '',
    unreadCount = 0,
    children,
    ...restProps
  }: Props = $props();
</script>

<div class={cn('flex items-center justify-between text-xs', className)} {...restProps}>
  <span class="text-muted-foreground">{time}</span>
  <div class="flex items-center gap-2">
    {#if children}
      {@render children()}
    {/if}
    {#if unreadCount > 0}
      <span class="h-5 min-w-5 px-1 rounded-full bg-primary text-primary-foreground text-xs flex items-center justify-center">
        {unreadCount > 99 ? '99+' : unreadCount}
      </span>
    {/if}
  </div>
</div>
