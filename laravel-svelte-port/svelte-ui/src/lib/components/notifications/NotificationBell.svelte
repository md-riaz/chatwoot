<script lang="ts">
  import { notificationsStore } from '$lib/stores/notifications.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { Bell, Check, CheckCheck, Trash2 } from '@lucide/svelte';
  import { toast } from 'svelte-sonner';
  
  const unreadCount = $derived(notificationsStore.unreadCount);
  const hasUnread = $derived(notificationsStore.hasUnread);
  
  let isOpen = $state(false);
  
  function handleOpenChange(open: boolean) {
    isOpen = open;
    if (open && notificationsStore.all.length === 0) {
      notificationsStore.fetchNotifications();
    }
  }
  
  async function handleMarkAllRead() {
    await notificationsStore.markAllAsRead();
    toast.success('All notifications marked as read');
  }
</script>

<DropdownMenu.Root open={isOpen} onOpenChange={handleOpenChange}>
  <DropdownMenu.Trigger>
    {#snippet child({ props })}
      <Button {...props} variant="ghost" size="icon" class="relative">
        <Bell class="h-5 w-5" />
        {#if hasUnread}
          <Badge 
            class="absolute -top-1 -right-1 h-5 w-5 flex items-center justify-center p-0 text-xs"
            variant="destructive"
          >
            {unreadCount > 99 ? '99+' : unreadCount}
          </Badge>
        {/if}
        <span class="sr-only">Notifications</span>
      </Button>
    {/snippet}
  </DropdownMenu.Trigger>
  
  <DropdownMenu.Content class="w-80">
    <div class="flex items-center justify-between px-4 py-3 border-b">
      <h3 class="font-semibold">Notifications</h3>
      {#if hasUnread}
        <Button
          variant="ghost"
          size="sm"
          onclick={handleMarkAllRead}
          class="text-xs"
        >
          <CheckCheck class="h-3 w-3 mr-1" />
          Mark all read
        </Button>
      {/if}
    </div>
    
    <div class="max-h-96 overflow-y-auto">
      {@render children?.()}
    </div>
  </DropdownMenu.Content>
</DropdownMenu.Root>
