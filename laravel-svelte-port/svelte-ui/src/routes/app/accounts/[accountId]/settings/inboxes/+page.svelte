<script lang="ts">
  /**
   * Inboxes Management Page
   * List and manage inboxes/channels
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import SectionLayout from '../account/components/SectionLayout.svelte';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Table from '$lib/components/ui/table';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import { Plus, Settings, Trash2 } from 'lucide-svelte';

  const accountId = $derived(Number($page.params.accountId));
  const inboxes = $derived(inboxesStore.sortedInboxes);
  const isLoading = $derived(inboxesStore.isLoading);

  const currentUserRole = $derived(authStore.currentRole);
  const isAdmin = $derived(currentUserRole === 'administrator');

  onMount(() => {
    inboxesStore.fetchInboxes();
  });

  function handleCreateInbox() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new`);
  }

  function handleViewInbox(inboxId: number) {
    goto(`/app/accounts/${accountId}/settings/inboxes/${inboxId}`);
  }

  function getChannelIcon(channelType: string) {
    const icons: Record<string, string> = {
      'Channel::WebWidget': '💬',
      'Channel::Api': '🔌',
      'Channel::Email': '📧',
      'Channel::Whatsapp': '📱',
      'Channel::Sms': '💌',
      'Channel::Twilio': '💌',
      'Channel::FacebookPage': '📘',
      'Channel::TwitterProfile': '🐦',
      'Channel::Line': '💚',
      'Channel::Telegram': '✈️',
    };
    return icons[channelType] || '📮';
  }

  // Delete dialog state
  let showDeleteDialog = $state(false);
  let deletingInbox = $state<any>(null);
  let isDeleting = $state(false);

  function handleDeleteClick(inbox: any) {
    deletingInbox = inbox;
    showDeleteDialog = true;
  }

  async function confirmDelete() {
    if (!deletingInbox) return;
    isDeleting = true;
    try {
      await inboxesStore.deleteInbox(deletingInbox.id);
      showDeleteDialog = false;
      deletingInbox = null;
    } finally {
      isDeleting = false;
    }
  }

  function cancelDelete() {
    showDeleteDialog = false;
    deletingInbox = null;
  }
</script>

<div class="flex flex-col w-full space-y-6">
  <BaseSettingsHeader title="Inboxes" />

  <div class="flex-grow flex-shrink min-w-0 space-y-6">
    <SectionLayout
      title="Inboxes List"
      description="Manage your communication channels and inboxes"
    >
      {#snippet headerActions()}
        {#if isAdmin}
          <Button onclick={handleCreateInbox}>
            <Plus class="mr-2 h-4 w-4" />
            Create New Inbox
          </Button>
        {/if}
      {/snippet}

      {#if isLoading}
        <div class="flex justify-center items-center py-20">
          <div
            class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
          ></div>
        </div>
      {:else if inboxes.length === 0}
        <div
          class="text-center py-12 border rounded-lg bg-card text-card-foreground"
        >
          <div class="mb-4">
            <svg
              class="mx-auto h-16 w-16 text-muted-foreground"
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
          <p class="text-muted-foreground mb-4">
            Create your first inbox to start receiving messages.
          </p>
          {#if isAdmin}
            <Button onclick={handleCreateInbox}>Create Your First Inbox</Button>
          {/if}
        </div>
      {:else}
        <div class="rounded-md border">
          <Table.Root>
            <Table.Body>
              {#each inboxes as inbox}
                <Table.Row class="hover:bg-muted/50 transition-colors">
                  <Table.Cell class="py-4 pl-4 pr-4 align-top w-full">
                    <div class="flex items-center gap-4">
                      {#if inbox.avatarUrl}
                        <div
                          class="bg-muted/30 rounded-full h-12 w-12 p-2 ring-1 ring-border shadow-sm flex items-center justify-center overflow-hidden"
                        >
                          <img
                            src={inbox.avatarUrl}
                            alt={inbox.name}
                            class="h-full w-full object-cover rounded-full"
                          />
                        </div>
                      {:else}
                        <div
                          class="bg-muted/30 rounded-full h-12 w-12 p-2 ring-1 ring-border shadow-sm flex items-center justify-center text-xl"
                        >
                          {getChannelIcon(inbox.channelType)}
                        </div>
                      {/if}

                      <div>
                        <span class="block font-medium capitalize text-base"
                          >{inbox.name}</span
                        >
                        <p class="text-sm text-muted-foreground m-0 capitalize">
                          {inbox.channelType.replace('Channel::', '')}
                        </p>
                      </div>
                    </div>
                  </Table.Cell>

                  <Table.Cell
                    class="py-4 pr-4 align-top text-right whitespace-nowrap align-middle"
                  >
                    <div class="flex justify-end gap-1 mt-1">
                      {#if isAdmin}
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-muted-foreground hover:text-foreground"
                          title="Settings"
                          onclick={() => handleViewInbox(inbox.id)}
                        >
                          <Settings class="h-4 w-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                          title="Delete Inbox"
                          onclick={() => handleDeleteClick(inbox)}
                          disabled={isDeleting &&
                            deletingInbox?.id === inbox.id}
                        >
                          <Trash2 class="h-4 w-4" />
                        </Button>
                      {/if}
                    </div>
                  </Table.Cell>
                </Table.Row>
              {/each}
            </Table.Body>
          </Table.Root>
        </div>
      {/if}
    </SectionLayout>
  </div>
</div>

<!-- Delete Confirm Dialog -->
<AlertDialog.Root bind:open={showDeleteDialog}>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Inbox</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete <strong>{deletingInbox?.name}</strong>?
        This action cannot be undone.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel onclick={cancelDelete} disabled={isDeleting}>
        Cancel
      </AlertDialog.Cancel>
      <AlertDialog.Action
        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
        onclick={confirmDelete}
        disabled={isDeleting}
      >
        {isDeleting ? 'Deleting...' : 'Delete Inbox'}
      </AlertDialog.Action>
    </AlertDialog.Footer>
  </AlertDialog.Content>
</AlertDialog.Root>
