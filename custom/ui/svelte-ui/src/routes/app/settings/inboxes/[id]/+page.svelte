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
  import type { UpdateInboxParams } from '$lib/api/inboxes';

  let accountId = $derived($page.params.accountId);
  let inboxId = $derived(Number($page.params.id));
  
  let inbox = $derived(
    inboxesStore.allInboxes.find((i) => i.id === inboxId)
  );
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
  
  onMount(() => {
    inboxesStore.fetchInbox(inboxId);
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
      setTimeout(() => {
        successMessage = '';
      }, 3000);
    } else {
      errorMessage = inboxesStore.error || 'Failed to update inbox settings';
    }
  }
  
  async function handleDelete() {
    if (!inbox) return;
    
    const confirmed = confirm(`Are you sure you want to delete "${inbox.name}"? This action cannot be undone.`);
    if (!confirmed) return;
    
    const success = await inboxesStore.deleteInbox(inboxId);
    if (success) {
      goto(`/app/${accountId}/settings/inboxes`);
    } else {
      errorMessage = inboxesStore.error || 'Failed to delete inbox';
    }
  }
  
  function getChannelTypeName(channelType: string): string {
    return channelType.replace('Channel::', '');
  }
  
  function getChannelIcon(channelType: string): string {
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
</script>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-4">
      <Button variant="ghost" onclick={() => goto(`/app/${accountId}/settings/inboxes`)}>
        ← Back to Inboxes
      </Button>
      {#if inbox}
        <div class="flex items-center gap-3">
          <div class="text-3xl">{getChannelIcon(inbox.channelType)}</div>
          <div>
            <h1 class="text-3xl font-bold">{inbox.name}</h1>
            <Badge variant="secondary" class="mt-1">
              {getChannelTypeName(inbox.channelType)}
            </Badge>
          </div>
        </div>
      {/if}
    </div>
    {#if inbox}
      <Button variant="destructive" onclick={handleDelete} disabled={isDeleting}>
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
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if !inbox}
    <Card.Root>
      <Card.Content class="p-12 text-center">
        <p class="text-gray-600">Inbox not found</p>
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
            <Card.Description>Configure greeting message for new conversations</Card.Description>
          </Card.Header>
          <Card.Content class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Enable Greeting Message</Label>
                <p class="text-sm text-gray-600">Show a greeting when conversation starts</p>
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
            <Card.Description>Configure how conversations are handled</Card.Description>
          </Card.Header>
          <Card.Content class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Auto Assignment</Label>
                <p class="text-sm text-gray-600">Automatically assign conversations to agents</p>
              </div>
              <Switch bind:checked={enableAutoAssignment} />
            </div>
            
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Working Hours</Label>
                <p class="text-sm text-gray-600">Enable working hours for this inbox</p>
              </div>
              <Switch bind:checked={workingHoursEnabled} />
            </div>
            
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Allow Messages After Resolved</Label>
                <p class="text-sm text-gray-600">Allow new messages in resolved conversations</p>
              </div>
              <Switch bind:checked={allowMessagesAfterResolved} />
            </div>
          </Card.Content>
        </Card.Root>

        <div class="flex justify-end gap-3">
          <Button variant="outline" onclick={() => goto(`/app/${accountId}/settings/inboxes`)}>
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
            <Card.Description>Basic information about this inbox</Card.Description>
          </Card.Header>
          <Card.Content class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <Label class="text-gray-600">Channel Type</Label>
                <p class="font-medium">{getChannelTypeName(inbox.channelType)}</p>
              </div>
              
              <div>
                <Label class="text-gray-600">Inbox ID</Label>
                <p class="font-medium">{inbox.id}</p>
              </div>
              
              {#if inbox.websiteUrl}
                <div>
                  <Label class="text-gray-600">Website URL</Label>
                  <p class="font-medium">{inbox.websiteUrl}</p>
                </div>
              {/if}
              
              {#if inbox.emailAddress}
                <div>
                  <Label class="text-gray-600">Email Address</Label>
                  <p class="font-medium">{inbox.emailAddress}</p>
                </div>
              {/if}
              
              {#if inbox.inboxIdentifier}
                <div>
                  <Label class="text-gray-600">Identifier</Label>
                  <p class="font-medium font-mono text-sm">{inbox.inboxIdentifier}</p>
                </div>
              {/if}
              
              <div>
                <Label class="text-gray-600">Auto Assignment</Label>
                <p class="font-medium">{inbox.enableAutoAssignment ? 'Enabled' : 'Disabled'}</p>
              </div>
              
              <div>
                <Label class="text-gray-600">Working Hours</Label>
                <p class="font-medium">{inbox.workingHoursEnabled ? 'Enabled' : 'Disabled'}</p>
              </div>
              
              {#if inbox.timezone}
                <div>
                  <Label class="text-gray-600">Timezone</Label>
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
