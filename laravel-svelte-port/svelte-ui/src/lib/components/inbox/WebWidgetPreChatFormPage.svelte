<script lang="ts">
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import InboxSettingsHeader from './InboxSettingsHeader.svelte';
  import InboxSettingsTabs from './InboxSettingsTabs.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';

  let accountId = $derived($page.params.accountId ?? '');
  let inboxId = $derived(Number($page.params.id));

  let inbox = $derived(
    inboxesStore.allInboxes.find(item => item.id === inboxId) ?? null
  );

  let channel = $derived((inbox?.channel ?? {}) as Record<string, any>);
  let isLoading = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdating);

  let preChatFormEnabled = $state(false);
  let preChatMessage = $state('');
  let requireName = $state(false);
  let requireEmail = $state(false);
  let requirePhoneNumber = $state(false);
  let successMessage = $state('');
  let errorMessage = $state('');

  $effect(() => {
    if (inboxId) {
      inboxesStore.fetchInbox(inboxId);
    }
  });

  $effect(() => {
    if (!inbox) return;

    const options = (channel.preChatFormOptions ?? {}) as Record<string, any>;
    preChatFormEnabled = (channel.preChatFormEnabled as boolean) ?? false;
    preChatMessage = (options.preChatMessage as string) ?? '';
    requireName = (options.requireName as boolean) ?? false;
    requireEmail = (options.requireEmail as boolean) ?? false;
    requirePhoneNumber = (options.requirePhoneNumber as boolean) ?? false;
  });

  async function handleSave() {
    successMessage = '';
    errorMessage = '';

    const saved = await inboxesStore.updateWebWidget(inboxId, {
      preChatFormEnabled,
      preChatFormOptions: {
        preChatMessage,
        requireName,
        requireEmail,
        requirePhoneNumber,
      },
    });

    if (saved) {
      successMessage = 'Pre chat form settings updated successfully';
      return;
    }

    errorMessage =
      inboxesStore.error || 'Failed to update pre chat form settings';
  }
</script>

<div class="space-y-6">
  <InboxSettingsHeader {accountId} {inbox} />

  {#if inbox}
    <InboxSettingsTabs
      {accountId}
      inboxId={inbox.id}
      channelType={inbox.channelType}
      active="pre-chat-form"
    />
  {/if}

  {#if successMessage}
    <Card.Root class="border-green-200 bg-green-50">
      <Card.Content class="p-4 text-green-800">{successMessage}</Card.Content>
    </Card.Root>
  {/if}

  {#if errorMessage}
    <Card.Root class="border-red-200 bg-red-50">
      <Card.Content class="p-4 text-red-800">{errorMessage}</Card.Content>
    </Card.Root>
  {/if}

  {#if isLoading}
    <div class="py-20 text-center text-muted-foreground">Loading pre chat form settings...</div>
  {:else if !inbox}
    <div class="py-20 text-center text-muted-foreground">Inbox not found</div>
  {:else}
    <Card.Root>
      <Card.Header>
        <Card.Title>Pre Chat Form</Card.Title>
        <Card.Description>
          Control what information visitors must provide before starting a conversation.
        </Card.Description>
      </Card.Header>
      <Card.Content class="space-y-6">
        <div class="flex items-center justify-between rounded-lg border p-4">
          <div class="space-y-0.5">
            <Label>Enable Pre Chat Form</Label>
            <p class="text-sm text-muted-foreground">
              Ask visitors for contact details before opening the widget conversation.
            </p>
          </div>
          <Switch bind:checked={preChatFormEnabled} />
        </div>

        {#if preChatFormEnabled}
          <div class="space-y-2">
            <Label for="pre-chat-message">Pre Chat Message</Label>
            <Textarea id="pre-chat-message" bind:value={preChatMessage} rows={4} />
          </div>

          <div class="grid gap-4 sm:grid-cols-3">
            <div class="flex items-center justify-between rounded-lg border p-4">
              <Label for="require-name">Require Name</Label>
              <Switch id="require-name" bind:checked={requireName} />
            </div>

            <div class="flex items-center justify-between rounded-lg border p-4">
              <Label for="require-email">Require Email</Label>
              <Switch id="require-email" bind:checked={requireEmail} />
            </div>

            <div class="flex items-center justify-between rounded-lg border p-4">
              <Label for="require-phone">Require Phone</Label>
              <Switch id="require-phone" bind:checked={requirePhoneNumber} />
            </div>
          </div>
        {/if}
      </Card.Content>
      <Card.Footer class="justify-end">
        <Button onclick={handleSave} disabled={isUpdating}>
          {isUpdating ? 'Saving...' : 'Save Pre Chat Settings'}
        </Button>
      </Card.Footer>
    </Card.Root>
  {/if}
</div>
