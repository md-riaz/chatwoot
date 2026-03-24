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
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdatingSMTP);

  let isEnabled = $state(false);
  let address = $state('');
  let port = $state('');
  let login = $state('');
  let password = $state('');
  let domain = $state('');
  let sslTls = $state(false);
  let starttls = $state(true);
  let authMechanism = $state('login');
  let errorMessage = $state('');
  let successMessage = $state('');

  $effect(() => {
    if (inboxId) inboxesStore.fetchInbox(inboxId);
  });

  $effect(() => {
    if (!inbox) return;
    isEnabled = inbox.smtp_enabled ?? false;
    address = inbox.smtp_address ?? '';
    port = inbox.smtp_port ? String(inbox.smtp_port) : '';
    login = inbox.smtp_email ?? '';
    password = '';
    domain = (inbox.additionalAttributes?.smtp_domain as string) ?? '';
    sslTls = inbox.smtp_enable_ssl_tls ?? false;
    starttls = inbox.smtp_enable_starttls_auto ?? true;
    authMechanism = inbox.smtp_authentication ?? 'login';
  });

  async function handleSave() {
    errorMessage = '';
    successMessage = '';

    const ok = await inboxesStore.updateSMTPSettings(inboxId, {
      smtp_enabled: isEnabled,
      smtp_address: address,
      smtp_port: Number(port),
      smtp_email: login,
      smtp_password: password,
      smtp_domain: domain,
      smtp_enable_ssl_tls: sslTls,
      smtp_enable_starttls_auto: starttls,
      smtp_authentication: authMechanism,
    });

    if (ok) {
      successMessage = 'SMTP settings updated successfully';
      return;
    }

    errorMessage = inboxesStore.error || 'Failed to update SMTP settings';
  }
</script>

<div class="space-y-6">
  <InboxSettingsHeader accountId={accountId} {inbox} isDeleting={false} />
  {#if inbox}
    <InboxSettingsTabs accountId={accountId} inboxId={inbox.id} channelType={inbox.channelType} active="smtp" />
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
        <Card.Title>SMTP Settings</Card.Title>
        <Card.Description>Configure outgoing email delivery for this inbox.</Card.Description>
      </Card.Header>
      <Card.Content class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="space-y-0.5">
            <Label>Enable SMTP</Label>
            <p class="text-sm text-muted-foreground">Send replies through your SMTP server.</p>
          </div>
          <Switch bind:checked={isEnabled} />
        </div>

        {#if isEnabled}
          <div class="space-y-4">
            <div><Label for="smtp-address">Server</Label><Input id="smtp-address" bind:value={address} placeholder="smtp.example.com" /></div>
            <div><Label for="smtp-port">Port</Label><Input id="smtp-port" bind:value={port} type="number" placeholder="587" /></div>
            <div><Label for="smtp-login">Login</Label><Input id="smtp-login" bind:value={login} placeholder="support@example.com" /></div>
            <div><Label for="smtp-password">Password</Label><Input id="smtp-password" bind:value={password} type="password" placeholder="Mailbox password" /></div>
            <div><Label for="smtp-domain">Domain</Label><Input id="smtp-domain" bind:value={domain} placeholder="example.com" /></div>
            <div class="grid gap-4 md:grid-cols-2">
              <div class="flex items-center justify-between rounded-lg border p-4"><Label for="smtp-ssl">SSL/TLS</Label><Switch id="smtp-ssl" bind:checked={sslTls} /></div>
              <div class="flex items-center justify-between rounded-lg border p-4"><Label for="smtp-starttls">STARTTLS</Label><Switch id="smtp-starttls" bind:checked={starttls} /></div>
            </div>
            <div><Label for="smtp-auth">Authentication</Label><Input id="smtp-auth" bind:value={authMechanism} placeholder="login" /></div>
          </div>
        {/if}
      </Card.Content>
      <Card.Footer class="flex justify-end">
        <Button onclick={handleSave} disabled={isUpdating}>{isUpdating ? 'Saving...' : 'Save SMTP Settings'}</Button>
      </Card.Footer>
    </Card.Root>
  {/if}
</div>
