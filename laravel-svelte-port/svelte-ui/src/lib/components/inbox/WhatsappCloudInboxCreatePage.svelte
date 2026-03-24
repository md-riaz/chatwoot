<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { ArrowLeft, Phone } from 'lucide-svelte';

  let accountId = $derived($page.params.accountId ?? '');
  let isCreating = $derived(inboxesStore.uiFlags.isCreating);

  let inboxName = $state('');
  let phoneNumber = $state('');
  let phoneNumberId = $state('');
  let businessAccountId = $state('');
  let apiKey = $state('');
  let errorMessage = $state<string | null>(null);

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new`);
  }

  async function handleCreate() {
    errorMessage = null;

    if (
      !inboxName.trim() ||
      !phoneNumber.trim() ||
      !phoneNumberId.trim() ||
      !businessAccountId.trim() ||
      !apiKey.trim()
    ) {
      errorMessage = 'All WhatsApp Cloud fields are required.';
      return;
    }

    const inbox = await inboxesStore.createInboxThroughChannel('whatsapp', {
      name: inboxName.trim(),
      phoneNumber: phoneNumber.trim(),
      provider: 'whatsapp_cloud',
      providerConfig: {
        phoneNumberId: phoneNumberId.trim(),
        businessAccountId: businessAccountId.trim(),
        apiKey: apiKey.trim(),
      },
    });

    if (inbox) {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/${inbox.id}/agents`);
      return;
    }

    errorMessage = inboxesStore.error || 'Failed to create WhatsApp inbox.';
  }
</script>

<div class="mx-auto max-w-3xl space-y-6">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        WhatsApp Cloud Inbox
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        Connect a WhatsApp Cloud number and continue to agent assignment.
      </p>
    </div>
  </div>

  {#if errorMessage}
    <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      {errorMessage}
    </div>
  {/if}

  <Card.Root>
    <Card.Header>
      <Card.Title class="flex items-center gap-2">
        <Phone class="h-5 w-5" />
        WhatsApp Cloud Setup
      </Card.Title>
      <Card.Description>
        Use the Meta Cloud API credentials for the phone number you want to connect.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-5">
      <div class="space-y-2">
        <Label for="wa-name">Inbox Name</Label>
        <Input id="wa-name" bind:value={inboxName} placeholder="WhatsApp Support" />
      </div>

      <div class="space-y-2">
        <Label for="wa-phone">Phone Number</Label>
        <Input id="wa-phone" bind:value={phoneNumber} placeholder="+15551234567" />
      </div>

      <div class="space-y-2">
        <Label for="wa-phone-number-id">Phone Number ID</Label>
        <Input id="wa-phone-number-id" bind:value={phoneNumberId} placeholder="106540352242922" />
      </div>

      <div class="space-y-2">
        <Label for="wa-business-account-id">Business Account ID</Label>
        <Input id="wa-business-account-id" bind:value={businessAccountId} placeholder="192837465564738" />
      </div>

      <div class="space-y-2">
        <Label for="wa-api-key">Access Token</Label>
        <Input id="wa-api-key" bind:value={apiKey} placeholder="EAAG..." />
      </div>
    </Card.Content>
    <Card.Footer class="flex justify-end">
      <Button onclick={handleCreate} disabled={isCreating}>
        {isCreating ? 'Creating...' : 'Create WhatsApp Inbox'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
