<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    name?: string;
    avatar?: string;
    avatarFallback?: string;
    inboxIcon?: Snippet;
    children?: Snippet;
  };

  let {
    class: className,
    name = '',
    avatar = '',
    avatarFallback = '',
    inboxIcon,
    children,
    ...restProps
  }: Props = $props();
</script>

<div class={cn('flex items-center gap-3', className)} {...restProps}>
  <div class="relative">
    {#if avatar}
      <img src={avatar} alt={name} class="h-10 w-10 rounded-full object-cover" />
    {:else}
      <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium">
        {avatarFallback}
      </div>
    {/if}
    {#if inboxIcon}
      <div class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-background border flex items-center justify-center">
        {@render inboxIcon()}
      </div>
    {/if}
  </div>
  <div class="flex-1 min-w-0">
    {#if children}
      {@render children()}
    {:else}
      <p class="font-medium truncate">{name}</p>
    {/if}
  </div>
</div>
