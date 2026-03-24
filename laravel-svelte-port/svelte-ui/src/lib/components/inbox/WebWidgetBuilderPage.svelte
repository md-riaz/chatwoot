<script lang="ts">
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { getWebWidgetScript } from '$lib/api/inboxes';
  import InboxSettingsHeader from './InboxSettingsHeader.svelte';
  import InboxSettingsTabs from './InboxSettingsTabs.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { ColorPicker } from '$lib/components/ui/color-picker';
  import WidgetPreview from '$lib/components/widget-preview/Widget.svelte';

  let accountId = $derived($page.params.accountId ?? '');
  let inboxId = $derived(Number($page.params.id));

  let inbox = $derived(
    inboxesStore.allInboxes.find(item => item.id === inboxId) ?? null
  );

  let channel = $derived((inbox?.channel ?? {}) as Record<string, any>);
  let isLoading = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdating);

  let inboxName = $state('');
  let websiteUrl = $state('');
  let widgetColor = $state('#1f93ff');
  let welcomeTitle = $state('');
  let welcomeTagline = $state('');
  let widgetBubblePosition = $state<'left' | 'right'>('right');
  let widgetBubbleLauncherTitle = $state('Chat with us');
  let embedScript = $state('');
  let successMessage = $state('');
  let errorMessage = $state('');

  $effect(() => {
    if (inboxId) {
      inboxesStore.fetchInbox(inboxId);
    }
  });

  $effect(() => {
    if (!inbox) return;

    inboxName = inbox.name ?? '';
    websiteUrl = (channel.websiteUrl as string) ?? '';
    widgetColor = (channel.widgetColor as string) ?? '#1f93ff';
    welcomeTitle = (channel.welcomeTitle as string) ?? '';
    welcomeTagline = (channel.welcomeTagline as string) ?? '';
  });

  $effect(() => {
    let isCancelled = false;

    async function loadScript() {
      if (!inboxId) return;

      try {
        embedScript = await getWebWidgetScript(Number(accountId), inboxId);
      } catch (error) {
        if (!isCancelled) {
          embedScript = '';
        }
      }
    }

    loadScript();

    return () => {
      isCancelled = true;
    };
  });

  async function handleSave() {
    successMessage = '';
    errorMessage = '';

    const saved = await inboxesStore.updateWebWidget(inboxId, {
      name: inboxName,
      websiteUrl,
      widgetColor,
      welcomeTitle,
      welcomeTagline,
    });

    if (saved) {
      successMessage = 'Widget settings updated successfully';
      embedScript = await getWebWidgetScript(Number(accountId), inboxId);
      return;
    }

    errorMessage =
      inboxesStore.error || 'Failed to update widget settings';
  }
</script>

<div class="space-y-6">
  <InboxSettingsHeader {accountId} {inbox} />

  {#if inbox}
    <InboxSettingsTabs
      {accountId}
      inboxId={inbox.id}
      channelType={inbox.channelType}
      active="widget-builder"
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
    <div class="py-20 text-center text-muted-foreground">Loading widget settings...</div>
  {:else if !inbox}
    <div class="py-20 text-center text-muted-foreground">Inbox not found</div>
  {:else}
    <div class="grid gap-6 xl:grid-cols-[minmax(0,26rem)_minmax(0,1fr)]">
      <Card.Root>
        <Card.Header>
          <Card.Title>Widget Builder</Card.Title>
          <Card.Description>
            Configure the public-facing website widget experience.
          </Card.Description>
        </Card.Header>
        <Card.Content class="space-y-4">
          <div class="space-y-2">
            <Label for="inbox-name">Website Name</Label>
            <Input id="inbox-name" bind:value={inboxName} />
          </div>

          <div class="space-y-2">
            <Label for="website-url">Website URL</Label>
            <Input id="website-url" bind:value={websiteUrl} />
          </div>

          <div class="space-y-2">
            <Label for="welcome-title">Welcome Title</Label>
            <Input id="welcome-title" bind:value={welcomeTitle} />
          </div>

          <div class="space-y-2">
            <Label for="welcome-tagline">Welcome Tagline</Label>
            <Textarea id="welcome-tagline" bind:value={welcomeTagline} rows={4} />
          </div>

          <div class="space-y-2">
            <Label for="widget-color">Widget Color</Label>
            <ColorPicker id="widget-color" bind:value={widgetColor} />
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
              <Label for="bubble-position">Bubble Position</Label>
              <select
                id="bubble-position"
                bind:value={widgetBubblePosition}
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
              >
                <option value="right">Right</option>
                <option value="left">Left</option>
              </select>
            </div>

            <div class="space-y-2">
              <Label for="bubble-title">Bubble Launcher Title</Label>
              <Input id="bubble-title" bind:value={widgetBubbleLauncherTitle} />
            </div>
          </div>
        </Card.Content>
        <Card.Footer class="justify-end">
          <Button onclick={handleSave} disabled={isUpdating}>
            {isUpdating ? 'Saving...' : 'Save Widget Settings'}
          </Button>
        </Card.Footer>
      </Card.Root>

      <div class="space-y-6">
        <Card.Root>
          <Card.Header>
            <Card.Title>Preview</Card.Title>
          </Card.Header>
          <Card.Content>
            <WidgetPreview
              websiteName={inboxName || 'Website'}
              welcomeHeading={welcomeTitle || 'Welcome'}
              welcomeTagline={welcomeTagline || 'We are here to help.'}
              color={widgetColor}
              widgetBubblePosition={widgetBubblePosition}
              widgetBubbleLauncherTitle={widgetBubbleLauncherTitle}
            />
          </Card.Content>
        </Card.Root>

        <Card.Root>
          <Card.Header>
            <Card.Title>Embed Script</Card.Title>
            <Card.Description>
              Add this snippet to your website to load the widget.
            </Card.Description>
          </Card.Header>
          <Card.Content>
            <Textarea readonly rows={8} value={embedScript} />
          </Card.Content>
        </Card.Root>
      </div>
    </div>
  {/if}
</div>
