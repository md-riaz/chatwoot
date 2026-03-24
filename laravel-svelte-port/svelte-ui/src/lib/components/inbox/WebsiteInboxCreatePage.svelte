<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { ColorPicker } from '$lib/components/ui/color-picker';
  import { Switch } from '$lib/components/ui/switch';
  import { ArrowLeft, Globe } from 'lucide-svelte';
  let accountId = $derived($page.params.accountId ?? '');
  let isCreating = $derived(inboxesStore.uiFlags.isCreating);

  let inboxName = $state('');
  let websiteUrl = $state('');
  let widgetColor = $state('#009CE0');
  let welcomeTitle = $state('');
  let welcomeTagline = $state('');
  let greetingEnabled = $state(false);
  let greetingMessage = $state('');
  let errorMessage = $state<string | null>(null);

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/inboxes/new`);
  }

  async function handleCreate() {
    errorMessage = null;

    if (!inboxName.trim() || !websiteUrl.trim()) {
      errorMessage = 'Inbox name and website URL are required.';
      return;
    }

    const inbox = await inboxesStore.createInboxThroughChannel('webWidget', {
      name: inboxName.trim(),
      websiteUrl: websiteUrl.trim(),
      widgetColor,
      welcomeTitle: welcomeTitle.trim(),
      welcomeTagline: welcomeTagline.trim(),
      greetingEnabled,
      greetingMessage: greetingEnabled ? greetingMessage : undefined,
    });

    if (inbox) {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/${inbox.id}/agents`);
      return;
    }

    errorMessage = inboxesStore.error || 'Failed to create website live chat inbox.';
  }
</script>

<div class="mx-auto max-w-3xl space-y-6">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        Website Live Chat
      </h1>
      <p class="text-sm text-muted-foreground mt-1">
        Create a website inbox and generate the widget configuration for your site.
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
        <Globe class="h-5 w-5" />
        Website Channel Setup
      </Card.Title>
      <Card.Description>
        Configure the basic live chat details shown to visitors on your site.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-5">
      <div class="space-y-2">
        <Label for="inbox-name">Inbox Name</Label>
        <Input
          id="inbox-name"
          bind:value={inboxName}
          placeholder="Acme Website Support"
        />
      </div>

      <div class="space-y-2">
        <Label for="website-url">Website URL</Label>
        <Input
          id="website-url"
          bind:value={websiteUrl}
          placeholder="https://www.example.com"
        />
      </div>

      <div class="space-y-2">
        <Label for="widget-color">Widget Color</Label>
        <ColorPicker id="widget-color" bind:value={widgetColor} />
      </div>

      <div class="space-y-2">
        <Label for="welcome-title">Welcome Title</Label>
        <Input
          id="welcome-title"
          bind:value={welcomeTitle}
          placeholder="Chat with us"
        />
      </div>

      <div class="space-y-2">
        <Label for="welcome-tagline">Welcome Tagline</Label>
        <Textarea
          id="welcome-tagline"
          bind:value={welcomeTagline}
          rows={3}
          placeholder="We typically reply in a few minutes."
        />
      </div>

      <div class="flex items-center justify-between rounded-lg border p-4">
        <div class="space-y-0.5">
          <Label>Enable Greeting Message</Label>
          <p class="text-sm text-muted-foreground">
            Show an automatic greeting when the widget opens.
          </p>
        </div>
        <Switch bind:checked={greetingEnabled} />
      </div>

      {#if greetingEnabled}
        <div class="space-y-2">
          <Label for="greeting-message">Greeting Message</Label>
          <Textarea
            id="greeting-message"
            bind:value={greetingMessage}
            rows={4}
            placeholder="Welcome! How can we help you today?"
          />
        </div>
      {/if}
    </Card.Content>
    <Card.Footer class="flex justify-end">
      <Button onclick={handleCreate} disabled={isCreating}>
        {isCreating ? 'Creating...' : 'Create Website Inbox'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
