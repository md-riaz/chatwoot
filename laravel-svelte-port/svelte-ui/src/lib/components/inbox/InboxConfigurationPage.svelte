<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import type { UpdateInboxParams } from '$lib/api/inboxes';
  import InboxSettingsHeader from './InboxSettingsHeader.svelte';
  import InboxSettingsTabs from './InboxSettingsTabs.svelte';

  let accountId = $derived($page.params.accountId ?? '');
  let inboxId = $derived(Number($page.params.id));

  let inbox = $derived(
    inboxesStore.allInboxes.find(i => i.id === inboxId) ?? null
  );
  let isLoading = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdating);
  let isDeleting = $derived(inboxesStore.uiFlags.isDeleting);

  let inboxName = $state('');
  let greetingMessage = $state('');
  let greetingEnabled = $state(false);
  let enableAutoAssignment = $state(false);
  let workingHoursEnabled = $state(false);
  let allowMessagesAfterResolved = $state(true);

  let successMessage = $state('');
  let errorMessage = $state('');
  let successTimeout: ReturnType<typeof setTimeout> | null = null;
  let showDeleteDialog = $state(false);
  let isDeleteConfirming = $state(false);

  $effect(() => {
    if (inboxId) {
      inboxesStore.fetchInbox(inboxId);
    }
  });

  $effect(() => {
    if (!inbox) return;

    inboxName = inbox.name || '';
    greetingMessage = inbox.greeting_message || '';
    greetingEnabled = inbox.greeting_enabled || false;
    enableAutoAssignment = inbox.enableAutoAssignment || false;
    workingHoursEnabled = inbox.workingHoursEnabled || false;
    allowMessagesAfterResolved = inbox.allowMessagesAfterResolved !== false;
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
      if (successTimeout) {
        clearTimeout(successTimeout);
      }
      successTimeout = setTimeout(() => {
        successMessage = '';
        successTimeout = null;
      }, 3000);
    } else {
      errorMessage = inboxesStore.error || 'Failed to update inbox settings';
    }
  }

  function handleDelete() {
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
</script>

<div class="space-y-6">
  <InboxSettingsHeader
    {accountId}
    {inbox}
    {isDeleting}
    onDelete={handleDelete}
  />

  {#if inbox}
    <InboxSettingsTabs
      {accountId}
      inboxId={inbox.id}
      channelType={inbox.channelType}
      active="configuration"
    />
  {/if}

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
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if !inbox}
    <Card.Root>
      <Card.Content class="p-12 text-center">
        <p class="text-muted-foreground">Inbox not found</p>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="space-y-6">
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
          <Card.Description>
            Configure greeting message for new conversations
          </Card.Description>
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
          <Card.Description>
            Configure how conversations are handled
          </Card.Description>
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
        <Button onclick={handleUpdate} disabled={isUpdating}>
          {isUpdating ? 'Saving...' : 'Save Changes'}
        </Button>
      </div>
    </div>
  {/if}
</div>

<AlertDialog.Root bind:open={showDeleteDialog}>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Inbox</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete this inbox? This action cannot be undone.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel>Cancel</AlertDialog.Cancel>
      <AlertDialog.Action
        class="bg-destructive text-destructive-foreground"
        onclick={confirmDelete}
        disabled={isDeleteConfirming}
      >
        {isDeleteConfirming ? 'Deleting...' : 'Delete Inbox'}
      </AlertDialog.Action>
    </AlertDialog.Footer>
  </AlertDialog.Content>
</AlertDialog.Root>
