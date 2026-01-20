<script lang="ts">
  import { page } from '$app/stores';
  import * as Separator from '$lib/components/ui/separator';
  import NotificationList from '$lib/components/notifications/NotificationList.svelte';
  import { notificationsStore } from '$lib/stores/notifications.svelte';
  import type { Notification } from '$lib/api/notifications';
  import { goto } from '$app/navigation';
  import InboxListHeader from '$lib/components/inbox/InboxListHeader.svelte';

  type SortOrder = 'newest' | 'oldest';

  const accountId = $derived(Number($page.params.accountId));
  const unreadCount = $derived(notificationsStore.unreadCount);
  const totalCount = $derived(notificationsStore.all.length);
  const isLoading = $derived(notificationsStore.isLoading);

  let sortOrder = $state<SortOrder>('newest');
  let showSnoozed = $state(true);
  let showRead = $state(true);

  $effect(() => {
    if (accountId && notificationsStore.accountId !== accountId) {
      notificationsStore.fetchNotifications(accountId);
      notificationsStore.fetchUnreadCount(accountId);
    }
  });

  function handleNotificationOpen(notification: Notification) {
    const conversationId = notification.primaryActorId;
    if (!conversationId) return;

    goto(`/app/accounts/${accountId}/inbox-view/conversation/${conversationId}`);
  }

  function handleDisplayChange(change: { sortOrder: SortOrder; showSnoozed: boolean; showRead: boolean }) {
    sortOrder = change.sortOrder;
    showSnoozed = change.showSnoozed;
    showRead = change.showRead;
  }

  async function handleMarkAllRead() {
    if (accountId) {
      await notificationsStore.markAllAsRead(accountId);
    }
  }

  async function handleDeleteAll() {
    if (accountId) {
      await notificationsStore.deleteAll(accountId, 'all');
    }
  }

  async function handleDeleteAllRead() {
    if (accountId) {
      await notificationsStore.deleteAll(accountId, 'read');
    }
  }
</script>

<section class="flex w-full h-full bg-background">
  <div
    class="flex flex-col h-full w-full lg:min-w-[340px] lg:max-w-[340px] border-r border-border"
  >
    <InboxListHeader
      sortOrder={sortOrder}
      showSnoozed={showSnoozed}
      showRead={showRead}
      onDisplayChange={handleDisplayChange}
      onMarkAllRead={handleMarkAllRead}
      onDeleteAll={handleDeleteAll}
      onDeleteAllRead={handleDeleteAllRead}
    />

    <Separator.Root />

    <div class="flex-1 overflow-y-auto">
      <NotificationList
        sortOrder={sortOrder}
        showSnoozed={showSnoozed}
        showRead={showRead}
        onNotificationOpen={handleNotificationOpen}
      />
    </div>
  </div>

  <div class="flex-1 min-w-0">
    <slot />
  </div>
</section>
