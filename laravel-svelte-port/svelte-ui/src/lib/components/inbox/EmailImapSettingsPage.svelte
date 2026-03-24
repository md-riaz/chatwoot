<script lang="ts">
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Switch } from '$lib/components/ui/switch';
  import InboxSettingsHeader from './InboxSettingsHeader.svelte';
  import InboxSettingsTabs from './InboxSettingsTabs.svelte';

  let accountId = $derived($page.params.accountId ?? '');
  let inboxId = $derived(Number($page.params.id));
  let inbox = $derived(inboxesStore.allInboxes.find(i => i.id === inboxId) ?? null);
  let isLoading = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdatingIMAP);

  let isEnabled = $state(false);
  let address = $state('');
  let port = $state('');
  let login = $state('');
  let password = $state('');
  let sslEnabled = $state(true);
  let errorMessage = $state('');
  let successMessage = $state('');

  $effect(() => {
    if (inboxId) inboxesStore.fetchInbox(inboxId);
  });

  $effect(() => {
    if (!inbox) return;
    isEnabled = inbox.imap_enabled ?? false;
    address = inbox.imap_address ?? '';
    port = inbox.imap_port ? String(inbox.imap_port) : '';
    login = inbox.imap_email ?? '';
    password = '';
    sslEnabled = inbox.imap_enable_ssl ?? true;
  });

  async function handleSave() {
    errorMessage = '';
    successMessage = '';

    const ok = await inboxesStore.updateIMAPSettings(inboxId, {
      imap_enabled: isEnabled,
      imap_address: address,
      imap_port: Number(port),
      imap_email: login,
      imap_password: password,
      imap_enable_ssl: sslEnabled,
    });

    if (ok) {
      successMessage = 'IMAP settings updated successfully';
      return;
    }

    errorMessage = inboxesStore.error || 'Failed to update IMAP settings';
  }
</script>

<div class="space-y-6">
  <InboxSettingsHeader accountId={accountId} {inbox} isDeleting={false} />
  {#if inbox}
    <InboxSettingsTabs accountId={accountId} inboxId={inbox.id} channelType={inbox.channelType} active="imap" />
  {/if}

  {#if successMessage}
    <Card.Root class="border-green-200 bg-green-50"><Card.Content class="p-4 text-green-800">{successMessage}</Card.Content></Card.Root>
  {/if}
  {#if errorMessage}
    <Card.Root class="border-red-200 bg-red-50"><Card.Content class="p-4 text-red-800">{errorMessage}</Card.Content></Card.Root>
  {/if}

  {#if isLoading}
    <div class="py-20 text-center text-muted-foreground">Loading inbox...</div>
  {:else if inbox}
    <Card.Root>
      <Card.Header>
        <Card.Title>IMAP Settings</Card.Title>
        <Card.Description>Configure incoming email sync for this inbox.</Card.Description>
      </Card.Header>
      <Card.Content class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="space-y-0.5">
            <Label>Enable IMAP</Label>
            <p class="text-sm text-muted-foreground">Fetch incoming emails from your mailbox.</p>
          </div>
          <Switch bind:checked={isEnabled} />
        </div>

        {#if isEnabled}
          <div class="space-y-4">
            <div><Label for="imap-address">Server</Label><Input id="imap-address" bind:value={address} placeholder="imap.example.com" /></div>
            <div><Label for="imap-port">Port</Label><Input id="imap-port" bind:value={port} type="number" placeholder="993" /></div>
            <div><Label for="imap-login">Login</Label><Input id="imap-login" bind:value={login} placeholder="support@example.com" /></div>
            <div><Label for="imap-password">Password</Label><Input id="imap-password" bind:value={password} type="password" placeholder="Mailbox password" /></div>
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Enable SSL</Label>
                <p class="text-sm text-muted-foreground">Use SSL/TLS for the IMAP connection.</p>
              </div>
              <Switch bind:checked={sslEnabled} />
            </div>
          </div>
        {/if}
      </Card.Content>
      <Card.Footer class="flex justify-end">
        <Button onclick={handleSave} disabled={isUpdating}>{isUpdating ? 'Saving...' : 'Save IMAP Settings'}</Button>
      </Card.Footer>
    </Card.Root>
  {/if}
</div>
