<script lang="ts">
  /**
   * Inbox Detail/Settings Page
   * View and configure individual inbox settings
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';
  import { Badge } from '$lib/components/ui/badge';
  import * as Tabs from '$lib/components/ui/tabs';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import {
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
    ArrowLeft,
  } from 'lucide-svelte';
  import type { UpdateInboxParams } from '$lib/api/inboxes';

  let accountId = $derived($page.params.accountId);
  let inboxId = $derived(Number($page.params.id));

  let inbox = $derived(inboxesStore.allInboxes.find(i => i.id === inboxId));
  let isLoading = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdating);
  let isDeleting = $derived(inboxesStore.uiFlags.isDeleting);

  // Form data
  let inboxName = $state('');
  let greetingMessage = $state('');
  let greetingEnabled = $state(false);
  let enableAutoAssignment = $state(false);
  let workingHoursEnabled = $state(false);
  let allowMessagesAfterResolved = $state(true);

  // Success/error messages
  let successMessage = $state('');
  let errorMessage = $state('');
  let successTimeout: ReturnType<typeof setTimeout> | null = null;

  $effect(() => {
    if (inboxId) {
      inboxesStore.fetchInbox(inboxId);
    }
  });

  // Update form when inbox data loads
  $effect(() => {
    if (inbox) {
      inboxName = inbox.name || '';
      greetingMessage = inbox.greeting_message || '';
      greetingEnabled = inbox.greeting_enabled || false;
      enableAutoAssignment = inbox.enableAutoAssignment || false;
      workingHoursEnabled = inbox.workingHoursEnabled || false;
      allowMessagesAfterResolved = inbox.allowMessagesAfterResolved !== false;
    }
  });

  async function handleUpdate() {
    errorMessage = '';
    successMessage = '';

    const params: UpdateInboxParams = {
      name: inboxName,
      greeting_enabled: greetingEnabled,
      greeting_message: greetingMessage,
      enable_auto_assignment: enableAutoAssignment,
      working_hours_enabled: workingHoursEnabled,
      allow_messages_after_resolved: allowMessagesAfterResolved,
    };

    const updated = await inboxesStore.updateInbox(inboxId, params);

    if (updated) {
      successMessage = 'Inbox settings updated successfully';
      // Clear any existing timeout
      if (successTimeout) {
        clearTimeout(successTimeout);
      }
      // Set new timeout
      successTimeout = setTimeout(() => {
        successMessage = '';
        successTimeout = null;
      }, 3000);
    } else {
      errorMessage = inboxesStore.error || 'Failed to update inbox settings';
    }
  }

  // Delete dialog state
  let showDeleteDialog = $state(false);
  let isDeleteConfirming = $state(false);

  async function handleDelete() {
    if (!inbox) return;
    showDeleteDialog = true;
  }

  async function confirmDelete() {
    if (!inbox) return;
    isDeleteConfirming = true;
    const success = await inboxesStore.deleteInbox(inboxId);
    isDeleteConfirming = false;
    if (success) {
      showDeleteDialog = false;
      goto(`/app/accounts/${accountId}/settings/inboxes`);
    } else {
      errorMessage = inboxesStore.error || 'Failed to delete inbox';
      showDeleteDialog = false;
    }
  }

  function getChannelTypeName(channelType: string): string {
    return channelType.replace('Channel::', '');
  }

  // Channel type to icon mapping (matching list page)
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

  function getChannelIcon(channelType: string) {
    return channelIconMap[channelType] || Inbox;
  }
</script>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-4">
      <Button
        variant="ghost"
        onclick={() => goto(`/app/accounts/${accountId}/settings/inboxes`)}
      >
        <ArrowLeft class="mr-1 h-4 w-4" /> Back to Inboxes
      </Button>
      {#if inbox}
        <div class="flex items-center gap-3">
          <div
            class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted"
          >
            {#each [getChannelIcon(inbox.channelType)] as IconComponent}
              <IconComponent class="h-5 w-5 text-muted-foreground" />
            {/each}
          </div>
          <div>
            <h1 class="text-xl font-medium tracking-tight text-foreground">
              {inbox.name}
            </h1>
            <Badge variant="secondary" class="mt-1">
              {getChannelTypeName(inbox.channelType)}
            </Badge>
          </div>
        </div>
      {/if}
    </div>
    {#if inbox}
      <Button
        variant="destructive"
        onclick={handleDelete}
        disabled={isDeleting}
      >
        {isDeleting ? 'Deleting...' : 'Delete Inbox'}
      </Button>
    {/if}
  </div>

  {#if successMessage}
    <Card.Root class="bg-green-50 border-green-200">
      <Card.Content class="p-4">
        <p class="text-green-800">{successMessage}</p>
      </Card.Content>
    </Card.Root>
  {/if}

  {#if errorMessage}
    <Card.Root class="bg-red-50 border-red-200">
      <Card.Content class="p-4">
        <p class="text-red-800">{errorMessage}</p>
      </Card.Content>
    </Card.Root>
  {/if}

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
      ></div>
    </div>
  {:else if !inbox}
    <Card.Root>
      <Card.Content class="p-12 text-center">
        <p class="text-muted-foreground">Inbox not found</p>
      </Card.Content>
    </Card.Root>
  {:else}
    <Tabs.Root value="settings" class="w-full">
      <Tabs.List>
        <Tabs.Trigger value="settings">Settings</Tabs.Trigger>
        <Tabs.Trigger value="info">Information</Tabs.Trigger>
      </Tabs.List>

      <Tabs.Content value="settings" class="space-y-6">
        <Card.Root>
          <Card.Header>
            <Card.Title>Basic Settings</Card.Title>
            <Card.Description>Configure basic inbox settings</Card.Description>
          </Card.Header>
          <Card.Content class="space-y-4">
            <div>
              <Label for="inboxName">Inbox Name</Label>
              <Input
                id="inboxName"
                bind:value={inboxName}
                placeholder="Inbox name"
              />
            </div>
          </Card.Content>
        </Card.Root>

        <Card.Root>
          <Card.Header>
            <Card.Title>Greeting</Card.Title>
            <Card.Description
              >Configure greeting message for new conversations</Card.Description
            >
          </Card.Header>
          <Card.Content class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Enable Greeting Message</Label>
                <p class="text-sm text-muted-foreground">
                  Show a greeting when conversation starts
                </p>
              </div>
              <Switch bind:checked={greetingEnabled} />
            </div>

            {#if greetingEnabled}
              <div>
                <Label for="greetingMessage">Greeting Message</Label>
                <Textarea
                  id="greetingMessage"
                  bind:value={greetingMessage}
                  placeholder="Welcome! How can we help you today?"
                  rows={3}
                />
              </div>
            {/if}
          </Card.Content>
        </Card.Root>

        <Card.Root>
          <Card.Header>
            <Card.Title>Conversation Settings</Card.Title>
            <Card.Description
              >Configure how conversations are handled</Card.Description
            >
          </Card.Header>
          <Card.Content class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Auto Assignment</Label>
                <p class="text-sm text-muted-foreground">
                  Automatically assign conversations to agents
                </p>
              </div>
              <Switch bind:checked={enableAutoAssignment} />
            </div>

            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Working Hours</Label>
                <p class="text-sm text-muted-foreground">
                  Enable working hours for this inbox
                </p>
              </div>
              <Switch bind:checked={workingHoursEnabled} />
            </div>

            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Allow Messages After Resolved</Label>
                <p class="text-sm text-muted-foreground">
                  Allow new messages in resolved conversations
                </p>
              </div>
              <Switch bind:checked={allowMessagesAfterResolved} />
            </div>
          </Card.Content>
        </Card.Root>

        <div class="flex justify-end gap-3">
          <Button
            variant="outline"
            onclick={() => goto(`/app/accounts/${accountId}/settings/inboxes`)}
          >
            Cancel
          </Button>
          <Button onclick={handleUpdate} disabled={isUpdating}>
            {isUpdating ? 'Saving...' : 'Save Changes'}
          </Button>
        </div>
      </Tabs.Content>

      <Tabs.Content value="info">
        <Card.Root>
          <Card.Header>
            <Card.Title>Inbox Information</Card.Title>
            <Card.Description
              >Basic information about this inbox</Card.Description
            >
          </Card.Header>
          <Card.Content class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label class="text-muted-foreground">Channel Type</Label>
                <p class="font-medium">
                  {getChannelTypeName(inbox.channelType)}
                </p>
              </div>

              <div>
                <Label class="text-muted-foreground">Inbox ID</Label>
                <p class="font-medium">{inbox.id}</p>
              </div>

              {#if inbox.websiteUrl}
                <div>
                  <Label class="text-muted-foreground">Website URL</Label>
                  <p class="font-medium">{inbox.websiteUrl}</p>
                </div>
              {/if}

              {#if inbox.emailAddress}
                <div>
                  <Label class="text-muted-foreground">Email Address</Label>
                  <p class="font-medium">{inbox.emailAddress}</p>
                </div>
              {/if}

              {#if inbox.inboxIdentifier}
                <div>
                  <Label class="text-muted-foreground">Identifier</Label>
                  <p class="font-medium font-mono text-sm">
                    {inbox.inboxIdentifier}
                  </p>
                </div>
              {/if}

              <div>
                <Label class="text-muted-foreground">Auto Assignment</Label>
                <p class="font-medium">
                  {inbox.enableAutoAssignment ? 'Enabled' : 'Disabled'}
                </p>
              </div>

              <div>
                <Label class="text-muted-foreground">Working Hours</Label>
                <p class="font-medium">
                  {inbox.workingHoursEnabled ? 'Enabled' : 'Disabled'}
                </p>
              </div>

              {#if inbox.timezone}
                <div>
                  <Label class="text-muted-foreground">Timezone</Label>
                  <p class="font-medium">{inbox.timezone}</p>
                </div>
              {/if}
            </div>
          </Card.Content>
        </Card.Root>
      </Tabs.Content>
    </Tabs.Root>
  {/if}
</div>

<!-- Delete Confirm Dialog -->
<AlertDialog.Root bind:open={showDeleteDialog}>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Inbox</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete <strong>{inbox?.name}</strong>? This
        action cannot be undone.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel
        onclick={() => (showDeleteDialog = false)}
        disabled={isDeleteConfirming}
      >
        Cancel
      </AlertDialog.Cancel>
      <AlertDialog.Action
        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
        onclick={confirmDelete}
        disabled={isDeleteConfirming}
      >
        {isDeleteConfirming ? 'Deleting...' : 'Delete Inbox'}
      </AlertDialog.Action>
    </AlertDialog.Footer>
  </AlertDialog.Content>
</AlertDialog.Root>
