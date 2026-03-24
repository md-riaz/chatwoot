<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { ArrowLeft, Plug } from 'lucide-svelte';
  import type { CreateInboxParams } from '$lib/api/inboxes';

  let accountId = $derived($page.params.accountId ?? '');
  let isCreating = $derived(inboxesStore.uiFlags.isCreating);

  let inboxName = $state('');
  let webhookUrl = $state('');
  let errorMessage = $state<string | null>(null);

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new`);
  }

  function isValidWebhookUrl(value: string) {
    if (!value.trim()) {
      return true;
    }

    return /^https?:\/\//i.test(value.trim());
  }

  async function handleCreate() {
    errorMessage = null;

    if (!inboxName.trim()) {
      errorMessage = 'Inbox name is required.';
      return;
    }

    if (!isValidWebhookUrl(webhookUrl)) {
      errorMessage = 'Webhook URL must start with http:// or https://.';
      return;
    }

    const params: CreateInboxParams = {
      name: inboxName.trim(),
      channelType: 'Channel::Api',
      channelData: {
        webhook_url: webhookUrl.trim(),
      },
    };

    const inbox = await inboxesStore.createInbox(params);

    if (inbox) {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/${inbox.id}/agents`);
      return;
    }

    errorMessage = inboxesStore.error || 'Failed to create API inbox.';
  }
</script>

<div class="mx-auto max-w-3xl space-y-6">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        API Inbox
      </h1>
      <p class="mt-1 text-sm text-muted-foreground">
        Create a programmatic inbox and continue to agent assignment and follow-up configuration.
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
        <Plug class="h-5 w-5" />
        API Channel Setup
      </Card.Title>
      <Card.Description>
        Start with an inbox name and optionally configure a webhook endpoint for outbound events.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-5">
      <div class="space-y-2">
        <Label for="inbox-name">Inbox Name</Label>
        <Input
          id="inbox-name"
          bind:value={inboxName}
          placeholder="API Support"
        />
      </div>

      <div class="space-y-2">
        <Label for="webhook-url">Webhook URL</Label>
        <Input
          id="webhook-url"
          bind:value={webhookUrl}
          placeholder="https://example.com/chatwoot/webhooks"
        />
        <p class="text-sm text-muted-foreground">
          Optional. If set, Chatwoot will post conversation events to this endpoint.
        </p>
      </div>
    </Card.Content>
    <Card.Footer class="flex justify-end">
      <Button onclick={handleCreate} disabled={isCreating}>
        {isCreating ? 'Creating...' : 'Create API Inbox'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
