<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { ArrowLeft, Mail } from 'lucide-svelte';

  let accountId = $derived($page.params.accountId ?? '');
  let isCreating = $derived(inboxesStore.uiFlags.isCreating);

  let inboxName = $state('');
  let emailAddress = $state('');
  let errorMessage = $state<string | null>(null);

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new`);
  }

  function isValidEmail(email: string) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  async function handleCreate() {
    errorMessage = null;

    if (!inboxName.trim()) {
      errorMessage = 'Inbox name is required.';
      return;
    }

    if (!emailAddress.trim() || !isValidEmail(emailAddress.trim())) {
      errorMessage = 'A valid forwarding email address is required.';
      return;
    }

    const inbox = await inboxesStore.createInboxThroughChannel('email', {
      name: inboxName.trim(),
      email: emailAddress.trim(),
      imapEnabled: false,
      smtpEnabled: false,
    });

    if (inbox) {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/${inbox.id}/agents`);
      return;
    }

    errorMessage = inboxesStore.error || 'Failed to create email inbox.';
  }
</script>

<div class="mx-auto max-w-3xl space-y-6">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        Email Inbox
      </h1>
      <p class="text-sm text-muted-foreground mt-1">
        Create an email inbox and continue to the agent assignment and setup steps.
      </p>
    </div>
  </div>

  {#if errorMessage}
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
      {errorMessage}
    </div>
  {/if}

  <Card.Root>
    <Card.Header>
      <Card.Title class="flex items-center gap-2">
        <Mail class="h-5 w-5" />
        Email Channel Setup
      </Card.Title>
      <Card.Description>
        Start with a name and the email address customers should use to reach this inbox.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-5">
      <div class="space-y-2">
        <Label for="inbox-name">Inbox Name</Label>
        <Input
          id="inbox-name"
          bind:value={inboxName}
          placeholder="Support Email"
        />
      </div>

      <div class="space-y-2">
        <Label for="email-address">Email Address</Label>
        <Input
          id="email-address"
          type="email"
          bind:value={emailAddress}
          placeholder="support@example.com"
        />
        <p class="text-sm text-muted-foreground">
          This is the address customers will use before SMTP and IMAP are configured.
        </p>
      </div>
    </Card.Content>
    <Card.Footer class="flex justify-end">
      <Button onclick={handleCreate} disabled={isCreating}>
        {isCreating ? 'Creating...' : 'Create Email Inbox'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
