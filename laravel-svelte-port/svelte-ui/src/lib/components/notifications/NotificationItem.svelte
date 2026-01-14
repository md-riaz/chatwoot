<script lang="ts">
  import { notificationsStore } from '$lib/stores/notifications.svelte';
  import type { Notification } from '$lib/api/notifications';
  import { Button } from '$lib/components/ui/button';
  import { Check, X } from '@lucide/svelte';
  import { toast } from 'svelte-sonner';
  
  interface Props {
    notification: Notification;
  }
  
  let { notification }: Props = $props();
  
  const isUnread = $derived(!notification.readAt);
  
  async function handleMarkRead() {
    await notificationsStore.markAsRead(notification.id);
  }
  
  async function handleDelete() {
    await notificationsStore.deleteNotification(notification.id);
    toast.success('Notification deleted');
  }
  
  function formatTimestamp(timestamp: string) {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
  }
</script>

<div 
  class="notification-item px-4 py-3 hover:bg-gray-50 transition-colors cursor-pointer"
  class:unread={isUnread}
  onclick={handleMarkRead}
>
  <div class="flex items-start gap-3">
    {#if notification.primaryActor?.thumbnail}
      <img 
        src={notification.primaryActor.thumbnail} 
        alt={notification.primaryActor.name}
        class="w-8 h-8 rounded-full"
      />
    {:else}
      <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold">
        {notification.primaryActor?.name?.charAt(0) || '?'}
      </div>
    {/if}
    
    <div class="flex-1 min-w-0">
      <div class="flex items-start justify-between gap-2">
        <div class="flex-1">
          <p class="text-sm font-medium text-gray-900">
            {notification.primaryActor?.name || 'Unknown'}
          </p>
          <p class="text-sm text-gray-600 mt-1">
            {notification.pushMessageTitle || notification.notificationType}
          </p>
          <p class="text-xs text-gray-400 mt-1">
            {formatTimestamp(notification.createdAt)}
          </p>
        </div>
        
        <div class="flex items-center gap-1">
          {#if isUnread}
            <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
          {/if}
          
          <Button
            variant="ghost"
            size="icon"
            class="h-6 w-6"
            onclick={(e: MouseEvent) => {
              e.stopPropagation();
              handleDelete();
            }}
          >
            <X class="h-3 w-3" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .notification-item.unread {
    background-color: #f0f9ff;
  }
</style>
