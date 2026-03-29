<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { getWebWidgetScript } from '$lib/api/inboxes';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Textarea } from '$lib/components/ui/textarea';
  import { CheckCircle2, Users, Settings, MessageSquareText, Globe, MessageCircle, Phone } from 'lucide-svelte';

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
  const isWebWidgetInbox = $derived(
    inbox?.channelType === 'Channel::WebWidget'
  );
  const isWhatsappInbox = $derived(
    inbox?.channelType === 'Channel::Whatsapp'
  );
  const channel = $derived((inbox?.channel ?? {}) as Record<string, any>);
  const whatsappPhoneNumber = $derived(
    typeof channel.phoneNumber === 'string' ? channel.phoneNumber : null
  );
  const whatsappPhoneNumberId = $derived(
    typeof channel.providerConfig?.phoneNumberId === 'string'
      ? channel.providerConfig.phoneNumberId
      : null
  );
  const whatsappBusinessAccountId = $derived(
    typeof channel.providerConfig?.businessAccountId === 'string'
      ? channel.providerConfig.businessAccountId
      : null
  );
  const whatsappVerifyToken = $derived(
    typeof channel.providerConfig?.webhookVerifyToken === 'string'
      ? channel.providerConfig.webhookVerifyToken
      : null
  );
  const whatsappWebhookUrl = $derived(
    whatsappPhoneNumber
      ? `${$page.url.origin}/webhooks/whatsapp/${encodeURIComponent(whatsappPhoneNumber)}`
      : null
  );
  const facebookPageId = $derived(
    typeof channel.pageId === 'string' ? channel.pageId : null
  );
  const facebookInstagramId = $derived(
    typeof channel.instagramId === 'string' ? channel.instagramId : null
  );
  const websiteUrl = $derived(
    typeof channel.websiteUrl === 'string' ? channel.websiteUrl : null
  );
  let widgetScript = $state('');
  let isLoadingWidgetScript = $state(false);
  let isSyncingTemplates = $state(false);
  let finishError = $state<string | null>(null);
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

  $effect(() => {
    let cancelled = false;

    async function loadWidgetScript() {
      if (!isWebWidgetInbox) {
        widgetScript = '';
        return;
      }

      isLoadingWidgetScript = true;

      try {
        widgetScript = await getWebWidgetScript(accountId, inboxId);
      } catch (error: any) {
        if (!cancelled) {
          finishError = error?.message || 'Failed to load website widget script.';
        }
      } finally {
        if (!cancelled) {
          isLoadingWidgetScript = false;
        }
      }
    }

    loadWidgetScript();

    return () => {
      cancelled = true;
    };
  });

  async function handleSyncTemplates() {
    isSyncingTemplates = true;
    finishError = null;

    try {
      await inboxesStore.syncTemplates(inboxId);
    } catch (error: any) {
      finishError = error?.message || 'Failed to sync WhatsApp templates.';
    } finally {
      isSyncingTemplates = false;
    }
  }
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
      {#if isWebWidgetInbox}
        <div class="w-full max-w-xl rounded-lg border bg-muted/30 p-4 text-left">
          <div class="mb-2 flex items-center gap-2 font-medium">
            <Globe class="h-4 w-4" />
            Website live chat
          </div>
          <p class="text-sm text-muted-foreground">
            Add the widget script to your website, then continue with widget builder and pre-chat form settings.
          </p>
          {#if websiteUrl}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Website URL
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 text-sm break-all">
                {websiteUrl}
              </div>
            </div>
          {/if}
          <div class="mt-3">
            <div class="text-xs uppercase tracking-wide text-muted-foreground">
              Widget Script
            </div>
            {#if isLoadingWidgetScript}
              <div class="mt-1 text-sm text-muted-foreground">Loading widget script...</div>
            {:else}
              <Textarea readonly rows={6} value={widgetScript} class="mt-1 font-mono text-xs" />
            {/if}
          </div>
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
          <div class="mb-2 flex items-center gap-2 font-medium">
            <MessageCircle class="h-4 w-4" />
            Facebook setup
          </div>
          <p class="text-sm text-muted-foreground">
            Review the inbox configuration and confirm the Facebook page connection before routing production conversations through Messenger.
          </p>
          {#if facebookPageId}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Connected Page ID
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 font-mono text-sm break-all">
                {facebookPageId}
              </div>
            </div>
          {/if}
          {#if facebookInstagramId}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Linked Instagram Business ID
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 font-mono text-sm break-all">
                {facebookInstagramId}
              </div>
            </div>
          {/if}
        </div>
      {/if}
      {#if isWhatsappInbox}
        <div class="w-full max-w-xl rounded-lg border bg-muted/30 p-4 text-left">
          <div class="mb-2 flex items-center gap-2 font-medium">
            <Phone class="h-4 w-4" />
            WhatsApp Cloud setup
          </div>
          <p class="text-sm text-muted-foreground">
            Keep these values handy when reviewing your Meta webhook configuration and syncing templates.
          </p>
          {#if whatsappPhoneNumber}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Phone Number
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 text-sm break-all">
                {whatsappPhoneNumber}
              </div>
            </div>
          {/if}
          {#if whatsappPhoneNumberId}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Phone Number ID
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 font-mono text-sm break-all">
                {whatsappPhoneNumberId}
              </div>
            </div>
          {/if}
          {#if whatsappBusinessAccountId}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Business Account ID
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 font-mono text-sm break-all">
                {whatsappBusinessAccountId}
              </div>
            </div>
          {/if}
          {#if whatsappWebhookUrl}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Webhook Callback URL
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 font-mono text-sm break-all">
                {whatsappWebhookUrl}
              </div>
            </div>
          {/if}
          {#if whatsappVerifyToken}
            <div class="mt-3">
              <div class="text-xs uppercase tracking-wide text-muted-foreground">
                Verify Token
              </div>
              <div class="mt-1 rounded-md border bg-background px-3 py-2 font-mono text-sm break-all">
                {whatsappVerifyToken}
              </div>
            </div>
          {/if}
          <div class="mt-4">
            <Button variant="outline" onclick={handleSyncTemplates} disabled={isSyncingTemplates}>
              {isSyncingTemplates ? 'Syncing templates...' : 'Sync WhatsApp Templates'}
            </Button>
          </div>
        </div>
      {/if}
      {#if finishError}
        <div class="w-full max-w-xl rounded-lg border border-red-200 bg-red-50 p-4 text-left text-red-800">
          {finishError}
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
        {#if isWebWidgetInbox}
          <Button
            variant="outline"
            onclick={() =>
              goto(`/app/accounts/${accountId}/settings/inboxes/${inboxId}/widget-builder`)}
          >
            <MessageSquareText class="mr-2 h-4 w-4" />
            Open Widget Builder
          </Button>
        {/if}
      </div>
    </Card.Content>
  </Card.Root>
</div>
