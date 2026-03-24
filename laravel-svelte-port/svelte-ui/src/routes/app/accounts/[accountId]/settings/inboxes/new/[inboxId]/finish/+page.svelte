<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { CheckCircle2, Users, Settings } from 'lucide-svelte';

  let accountId = $derived(Number($page.params.accountId));
  let inboxId = $derived(Number($page.params.inboxId));

  const inbox = $derived(
    inboxesStore.allInboxes.find(item => item.id === inboxId) ?? null
  );
  const isEmailInbox = $derived(inbox?.channelType === 'Channel::Email');
  const isApiInbox = $derived(inbox?.channelType === 'Channel::Api');
  const isFacebookInbox = $derived(
    inbox?.channelType === 'Channel::FacebookPage'
  );
  const apiWebhookUrl = $derived(
    typeof inbox?.webhookUrl === 'string' && inbox.webhookUrl.trim()
      ? inbox.webhookUrl.trim()
      : typeof inbox?.additionalAttributes?.webhook_url === 'string' &&
          inbox.additionalAttributes.webhook_url.trim()
        ? inbox.additionalAttributes.webhook_url.trim()
        : null
  );

  $effect(() => {
    if (inboxId && !inbox) {
      inboxesStore.fetchInbox(inboxId);
    }
  });
</script>

<div class="mx-auto max-w-3xl space-y-6">
  <Card.Root>
    <Card.Content class="flex flex-col items-center text-center gap-4 p-10">
      <CheckCircle2 class="h-14 w-14 text-green-600" />
      <div class="space-y-2">
        <h1 class="text-2xl font-semibold">Inbox created</h1>
        <p class="text-muted-foreground">
          {#if inbox}
            <span class="font-medium text-foreground">{inbox.name}</span>
            is ready. You can continue configuring it now or return to the inbox list.
          {:else}
            Your inbox is ready. You can continue configuring it now or return to the inbox list.
          {/if}
        </p>
      </div>
      {#if isEmailInbox}
        <div class="w-full max-w-xl rounded-lg border bg-muted/30 p-4 text-left">
          <div class="font-medium mb-2">Email setup</div>
          <p class="text-sm text-muted-foreground">
            Finish the email channel by reviewing SMTP and IMAP settings from inbox configuration.
          </p>
          {#if inbox?.forwardingEnabled && inbox?.forwardToEmail}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Forwarding Address
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 font-mono text-sm break-all">
                {inbox.forwardToEmail}
              </div>
            </div>
          {/if}
        </div>
      {/if}
      {#if isApiInbox}
        <div class="w-full max-w-xl rounded-lg border bg-muted/30 p-4 text-left">
          <div class="mb-2 font-medium">API setup</div>
          <p class="text-sm text-muted-foreground">
            Use the inbox settings page to review webhook handling and connect your application to the API channel.
          </p>
          {#if apiWebhookUrl}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Webhook URL
              </div>
              <div class="mt-1 break-all rounded-md border bg-background px-3 py-2 font-mono text-sm">
                {apiWebhookUrl}
              </div>
            </div>
          {/if}
        </div>
      {/if}
      {#if isFacebookInbox}
        <div class="w-full max-w-xl rounded-lg border bg-muted/30 p-4 text-left">
          <div class="mb-2 font-medium">Facebook setup</div>
          <p class="text-sm text-muted-foreground">
            Review the inbox configuration and confirm the Facebook page connection before routing production conversations through Messenger.
          </p>
        </div>
      {/if}
      <div class="grid w-full gap-4 pt-2 md:grid-cols-2">
        <div class="rounded-lg border p-4 text-left">
          <div class="mb-2 flex items-center gap-2 font-medium">
            <Users class="h-4 w-4" />
            Collaborators
          </div>
          <p class="text-sm text-muted-foreground">
            Add or update inbox agents later from the collaborators tab.
          </p>
        </div>
        <div class="rounded-lg border p-4 text-left">
          <div class="mb-2 flex items-center gap-2 font-medium">
            <Settings class="h-4 w-4" />
            Configuration
          </div>
          <p class="text-sm text-muted-foreground">
            Adjust greeting, assignment, and inbox behavior from settings.
          </p>
        </div>
      </div>
      <div class="flex flex-wrap justify-center gap-3 pt-2">
        <Button
          variant="outline"
          onclick={() => goto(`/app/accounts/${accountId}/settings/inboxes`)}
        >
          Back to Inboxes
        </Button>
        <Button
          onclick={() =>
            goto(`/app/accounts/${accountId}/settings/inboxes/${inboxId}/configuration`)}
        >
          Open Inbox Settings
        </Button>
      </div>
    </Card.Content>
  </Card.Root>
</div>
