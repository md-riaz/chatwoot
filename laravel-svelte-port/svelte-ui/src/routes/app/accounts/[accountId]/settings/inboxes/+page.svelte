<script lang="ts">
  /**
   * Inboxes Management Page
   * List and manage inboxes/channels
   * Vue parity: app/javascript/dashboard/routes/dashboard/settings/inbox/Index.vue
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Table from '$lib/components/ui/table';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import {
    Plus,
    Settings,
    Trash2,
    Globe,
    Mail,
    Phone,
    MessageCircle,
    MessageSquare,
    Send,
    Hash,
    Instagram,
    Video,
    Plug,
    Inbox,
  } from 'lucide-svelte';

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

  // Channel type to icon mapping (matching Vue's ChannelIcon)
  const channelIconMap: Record<string, typeof Globe> = {
    'Channel::WebWidget': Globe,
    'Channel::Api': Plug,
    'Channel::Email': Mail,
    'Channel::Whatsapp': Phone,
    'Channel::Sms': MessageSquare,
    'Channel::TwilioSms': MessageSquare,
    'Channel::FacebookPage': MessageCircle,
    'Channel::TwitterProfile': Hash,
    'Channel::Line': MessageCircle,
    'Channel::Telegram': Send,
    'Channel::Instagram': Instagram,
    'Channel::Tiktok': Video,
    'Channel::Voice': Phone,
  };

  // Channel type to human-readable name (matching Vue's ChannelName.vue i18nMap)
  const channelNameMap: Record<string, string> = {
    'Channel::WebWidget': 'Website',
    'Channel::Api': 'API',
    'Channel::Email': 'Email',
    'Channel::Whatsapp': 'WhatsApp',
    'Channel::Sms': 'SMS',
    'Channel::TwilioSms': 'Twilio SMS',
    'Channel::FacebookPage': 'Messenger',
    'Channel::TwitterProfile': 'Twitter',
    'Channel::Line': 'Line',
    'Channel::Telegram': 'Telegram',
    'Channel::Instagram': 'Instagram',
    'Channel::Tiktok': 'TikTok',
    'Channel::Voice': 'Voice',
  };

  function getChannelIcon(channelType: string) {
    return channelIconMap[channelType] || Inbox;
  }

  function getChannelName(channelType: string, medium?: string) {
    // Handle Twilio whatsapp variant
    if (channelType === 'Channel::TwilioSms' && medium === 'whatsapp') {
      return 'WhatsApp';
    }
    return channelNameMap[channelType] || channelType.replace('Channel::', '');
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

<div class="flex flex-col w-full h-full gap-8">
  <BaseSettingsHeader
    title="Inboxes"
    description="A channel is the mode of communication your customer chooses to interact with you. An inbox is where you manage interactions for a specific channel. It can include communications from various sources such as email, live chat, and social media."
    linkText="Learn more about inboxes"
    linkUrl="https://www.chatwoot.com/hc/user-guide/articles/1677579816-how-to-create-a-website-channel"
  >
    {#snippet actions()}
      {#if isAdmin}
        <Button onclick={handleCreateInbox}>
          <Plus class="mr-2 h-4 w-4" />
          Add Inbox
        </Button>
      {/if}
    {/snippet}
  </BaseSettingsHeader>

  <main>
    {#if isLoading}
      <div class="flex justify-center items-center py-20">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
        ></div>
      </div>
    {:else if inboxes.length === 0}
      <p
        class="flex-1 py-20 text-foreground flex items-center justify-center text-base"
      >
        No inboxes available. Create one to get started.
      </p>
    {:else}
      <table class="min-w-full overflow-x-auto">
        <tbody class="divide-y divide-border flex-1 text-foreground">
          {#each inboxes as inbox}
            <tr>
              <td class="py-4 pr-4">
                <div class="flex items-center flex-row gap-4">
                  {#if inbox.avatarUrl}
                    <div
                      class="bg-muted/30 rounded-full size-12 p-2 ring-1 ring-border shadow-sm flex items-center justify-center overflow-hidden"
                    >
                      <img
                        src={inbox.avatarUrl}
                        alt={inbox.name}
                        class="h-full w-full object-cover rounded-full"
                      />
                    </div>
                  {:else}
                    <div
                      class="size-12 flex justify-center items-center bg-muted/30 rounded-full p-2 ring-1 ring-border shadow-sm"
                    >
                      {#each [getChannelIcon(inbox.channelType)] as IconComponent}
                        <IconComponent class="size-5 text-muted-foreground" />
                      {/each}
                    </div>
                  {/if}
                  <div>
                    <span class="block font-medium capitalize">
                      {inbox.name}
                    </span>
                    <span class="text-sm text-muted-foreground">
                      {getChannelName(inbox.channelType, inbox.medium)}
                    </span>
                  </div>
                </div>
              </td>

              <td class="py-4">
                <div class="flex gap-1 justify-end">
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
                      disabled={isDeleting && deletingInbox?.id === inbox.id}
                    >
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  {/if}
                </div>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    {/if}
  </main>
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
