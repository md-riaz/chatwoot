<script lang="ts">
  import { Bell, MoreVertical } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import InboxDisplayMenu from './InboxDisplayMenu.svelte';
  import { notificationsStore } from '$lib/stores/notifications.svelte';

  type SortOrder = 'newest' | 'oldest';

  interface Props {
    sortOrder?: SortOrder;
    showSnoozed?: boolean;
    showRead?: boolean;
    onDisplayChange?: (change: { sortOrder: SortOrder; showSnoozed: boolean; showRead: boolean }) => void;
    onMarkAllRead?: () => void;
    onDeleteAll?: () => void;
    onDeleteAllRead?: () => void;
  }

  let {
    sortOrder = 'newest',
    showSnoozed = true,
    showRead = true,
    onDisplayChange = undefined,
    onMarkAllRead = undefined,
    onDeleteAll = undefined,
    onDeleteAllRead = undefined
  }: Props = $props();

  const unreadCount = $derived(notificationsStore.unreadCount);
  const isMarkingRead = $derived(notificationsStore.isMarkingRead);
  const isDeleting = $derived(notificationsStore.isDeleting);
  const hasReadNotifications = $derived(notificationsStore.readNotifications.length > 0);
</script>

<div class="flex items-center justify-between w-full gap-2 h-12 px-3">
  <div class="flex items-center gap-2 min-w-0 flex-1">
    <Bell class="h-4 w-4 text-muted-foreground" />
    <h1 class="min-w-0 text-sm font-medium truncate">
      My Inbox
    </h1>
    <InboxDisplayMenu
      sortOrder={sortOrder}
      showSnoozed={showSnoozed}
      showRead={showRead}
      onChange={onDisplayChange}
    />
  </div>

  <DropdownMenu.Root>
    <DropdownMenu.Trigger>
      {#snippet child({ props })}
        <Button
          {...props}
          variant="ghost"
          size="icon"
          class="h-8 w-8"
          aria-label="Notification actions"
        >
          <MoreVertical class="h-4 w-4" />
        </Button>
      {/snippet}
    </DropdownMenu.Trigger>
    <DropdownMenu.Content align="end" sideOffset={4} class="min-w-40">
      <DropdownMenu.Item
        disabled={isMarkingRead}
        onclick={() => {
          if (onMarkAllRead) onMarkAllRead();
        }}
      >
        {$_('inbox.list.mark_all_read')}
      </DropdownMenu.Item>
      <DropdownMenu.Item
        disabled={isDeleting || unreadCount === 0}
        onclick={() => {
          if (onDeleteAll) onDeleteAll();
        }}
      >
        {$_('inbox.list.delete_all')}
      </DropdownMenu.Item>
      <DropdownMenu.Item
        disabled={isDeleting || !hasReadNotifications}
        onclick={() => {
          if (onDeleteAllRead) onDeleteAllRead();
        }}
      >
        {$_('inbox.list.delete_all_read')}
      </DropdownMenu.Item>
    </DropdownMenu.Content>
  </DropdownMenu.Root>
</div>
