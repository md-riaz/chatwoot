<!--
  Presence Indicator Component
  Shows user online/offline/away status
-->
<script lang="ts">
  import { presenceStore } from '$lib/websocket/presence-store.svelte.js';
  import { Badge } from '$lib/components/ui/badge/index.js';
  import { Circle } from 'lucide-svelte';
  
  interface Props {
    userId: number;
    showLabel?: boolean;
    size?: 'sm' | 'md' | 'lg';
    class?: string;
  }
  
  let { userId, showLabel = false, size = 'md', class: className = '' }: Props = $props();
  
  // Reactive values
  let userPresence = $derived(presenceStore.getUserPresence(userId));
  let isOnline = $derived(presenceStore.isUserOnline(userId));
  let isAway = $derived(presenceStore.isUserAway(userId));
  
  // Status configuration
  let status = $derived(() => {
    if (!userPresence) return 'offline';
    return userPresence.status;
  });
  
  let statusConfig = $derived(() => {
    switch (status) {
      case 'online':
        return {
          color: 'bg-green-500',
          label: 'Online',
          variant: 'default' as const
        };
      case 'away':
        return {
          color: 'bg-yellow-500',
          label: 'Away',
          variant: 'secondary' as const
        };
      case 'offline':
      default:
        return {
          color: 'bg-gray-400',
          label: 'Offline',
          variant: 'outline' as const
        };
    }
  });
  
  let sizeClasses = $derived(() => {
    switch (size) {
      case 'sm':
        return 'w-2 h-2';
      case 'lg':
        return 'w-4 h-4';
      case 'md':
      default:
        return 'w-3 h-3';
    }
  });
</script>

<div class="presence-indicator {className}" title="{userPresence?.name || 'User'} is {status}">
  {#if showLabel}
    <Badge variant={statusConfig.variant} class="flex items-center gap-1.5">
      <div class="presence-indicator__dot {sizeClasses} {statusConfig.color} rounded-full"></div>
      <span class="text-xs">{statusConfig.label}</span>
    </Badge>
  {:else}
    <div class="presence-indicator__dot {sizeClasses} {statusConfig.color} rounded-full border-2 border-background"></div>
  {/if}
</div>

<style>
  .presence-indicator {
    @apply inline-flex items-center;
  }
  
  .presence-indicator__dot {
    @apply flex-shrink-0;
  }
</style>