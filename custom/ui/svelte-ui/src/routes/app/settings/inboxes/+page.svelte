<script lang="ts">
  /**
   * Inboxes Management Page
   * List and manage inboxes/channels
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';

  let accountId = $derived($page.params.accountId);
  let inboxes = $state([
    {
      id: 1,
      name: 'Website Chat',
      channelType: 'web',
      status: 'active',
      conversationsCount: 42,
    },
    {
      id: 2,
      name: 'Support Email',
      channelType: 'email',
      status: 'active',
      conversationsCount: 158,
    },
    {
      id: 3,
      name: 'WhatsApp Business',
      channelType: 'whatsapp',
      status: 'inactive',
      conversationsCount: 0,
    },
  ]);
  let isLoading = $state(false);

  onMount(() => {
    // TODO: Fetch inboxes from API
  });

  function handleCreateInbox() {
    goto(`/app/${accountId}/settings/inboxes/new`);
  }

  function handleViewInbox(inboxId: number) {
    goto(`/app/${accountId}/settings/inboxes/${inboxId}`);
  }

  function getChannelIcon(channelType: string) {
    const icons: Record<string, string> = {
      web: '💬',
      email: '📧',
      whatsapp: '📱',
      sms: '💌',
      facebook: '📘',
      twitter: '🐦',
    };
    return icons[channelType] || '📮';
  }

  function getStatusBadge(status: string) {
    return status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
  }
</script>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold">Inboxes</h1>
      <p class="text-muted-foreground mt-2">
        Manage your communication channels and inboxes
      </p>
    </div>
    <Button onclick={handleCreateInbox}>Create Inbox</Button>
  </div>

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if inboxes.length === 0}
    <Card.Root class="text-center py-12">
      <Card.Content>
        <div class="mb-4">
          <svg
            class="mx-auto h-16 w-16 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
            />
          </svg>
        </div>
        <h2 class="text-xl font-semibold mb-2">No inboxes yet</h2>
        <p class="text-gray-600 mb-4">
          Create your first inbox to start receiving messages
        </p>
        <Button onclick={handleCreateInbox}>Create Your First Inbox</Button>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      {#each inboxes as inbox}
        <Card.Root
          class="hover:shadow-md transition-shadow cursor-pointer"
          onclick={() => handleViewInbox(inbox.id)}
        >
          <Card.Content class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="text-3xl">{getChannelIcon(inbox.channelType)}</div>
                <div>
                  <h3 class="font-semibold text-lg">{inbox.name}</h3>
                  <p class="text-sm text-gray-600 capitalize">
                    {inbox.channelType}
                  </p>
                </div>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div class="text-sm">
                <span class="font-medium">{inbox.conversationsCount}</span>
                <span class="text-gray-600"> conversations</span>
              </div>
              <span
                class="px-2 py-1 rounded text-xs font-medium {getStatusBadge(
                  inbox.status
                )}"
              >
                {inbox.status}
              </span>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {/if}
</div>
