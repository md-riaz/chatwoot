<script lang="ts">
  import { notificationsStore } from '$lib/stores/notifications.svelte';
  import type { Notification } from '$lib/api/notifications';
  import NotificationItem from './NotificationItem.svelte';
  import { Button } from '$lib/components/ui/button';
  import { AlertCircle, Loader2 } from '@lucide/svelte';
  
  type SortOrder = 'newest' | 'oldest';
  
  interface Props {
    sortOrder?: SortOrder;
    showSnoozed?: boolean;
    showRead?: boolean;
    onNotificationOpen?: (notification: Notification) => void;
  }
  
  let {
    sortOrder = 'newest',
    showSnoozed = true,
    showRead = true,
    onNotificationOpen = undefined
  }: Props = $props();
  
  const rawNotifications = $derived(notificationsStore.all);
  const isLoading = $derived(notificationsStore.isLoading);
  
  const notifications = $derived(() => {
    let items = rawNotifications;
    
    if (!showRead) {
      items = items.filter(n => !n.readAt);
    }
    
    if (!showSnoozed) {
      items = items.filter(n => !n.snoozedUntil);
    }
    
    const sorted = [...items].sort((a, b) => {
      const aTime = new Date(a.createdAt).getTime();
      const bTime = new Date(b.createdAt).getTime();
      return sortOrder === 'newest' ? bTime - aTime : aTime - bTime;
    });
    
    return sorted;
  });
  
  function handleLoadMore() {
    notificationsStore.loadMore();
  }
</script>

<div class="notification-list">
  {#if isLoading && notifications.length === 0}
    <div class="flex items-center justify-center py-12">
      <Loader2 class="h-6 w-6 animate-spin text-gray-400" />
    </div>
  {:else if notifications.length === 0}
    <div class="flex flex-col items-center justify-center py-12 text-center px-4">
      <AlertCircle class="h-12 w-12 text-gray-400 mb-3" />
      <p class="text-sm text-gray-600">No notifications yet</p>
    </div>
  {:else}
    <div class="divide-y">
      {#each notifications as notification (notification.id)}
        <NotificationItem notification={notification} onOpen={onNotificationOpen} />
      {/each}
    </div>
    
    <div class="p-4 text-center border-t">
      <Button
        variant="ghost"
        size="sm"
        onclick={handleLoadMore}
        disabled={isLoading}
      >
        {#if isLoading}
          <Loader2 class="h-4 w-4 mr-2 animate-spin" />
        {/if}
        Load more
      </Button>
    </div>
  {/if}
</div>
