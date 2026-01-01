<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    name?: string;
    email?: string;
    avatar?: string;
    avatarFallback?: string;
    status?: 'online' | 'offline' | 'away' | 'busy';
    children?: Snippet;
  };

  let {
    class: className,
    name = '',
    email = '',
    avatar = '',
    avatarFallback = '',
    status,
    children,
    ...restProps
  }: Props = $props();

  const statusColors = {
    online: 'bg-green-500',
    offline: 'bg-gray-400',
    away: 'bg-yellow-500',
    busy: 'bg-red-500'
  };
</script>

<div class={cn('flex items-center gap-4', className)} {...restProps}>
  <div class="relative">
    {#if avatar}
      <img src={avatar} alt={name} class="h-14 w-14 rounded-full object-cover" />
    {:else}
      <div class="h-14 w-14 rounded-full bg-primary/20 flex items-center justify-center text-lg font-medium">
        {avatarFallback}
      </div>
    {/if}
    {#if status}
      <span class={cn('absolute bottom-0 right-0 h-4 w-4 rounded-full ring-2 ring-background', statusColors[status])}></span>
    {/if}
  </div>
  <div class="flex-1 min-w-0">
    {#if children}
      {@render children()}
    {:else}
      <p class="font-semibold text-lg truncate">{name}</p>
      {#if email}
        <p class="text-sm text-muted-foreground truncate">{email}</p>
      {/if}
    {/if}
  </div>
</div>
