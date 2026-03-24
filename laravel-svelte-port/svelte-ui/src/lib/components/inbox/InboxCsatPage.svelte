<script lang="ts">
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import InboxSettingsHeader from './InboxSettingsHeader.svelte';
  import InboxSettingsTabs from './InboxSettingsTabs.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Label } from '$lib/components/ui/label';
  import { Switch } from '$lib/components/ui/switch';
  import { Textarea } from '$lib/components/ui/textarea';

  let accountId = $derived($page.params.accountId ?? '');
  let inboxId = $derived(Number($page.params.id));

  let inbox = $derived(
    inboxesStore.allInboxes.find(item => item.id === inboxId) ?? null
  );

  let isLoading = $derived(inboxesStore.uiFlags.isFetchingItem);
  let isUpdating = $derived(inboxesStore.uiFlags.isUpdating);

  let csatSurveyEnabled = $state(false);
  let surveyMessage = $state(
    'How would you rate your experience with this conversation?'
  );
  let successMessage = $state('');
  let errorMessage = $state('');

  $effect(() => {
    if (inboxId) {
      inboxesStore.fetchInbox(inboxId);
    }
  });

  $effect(() => {
    if (!inbox) return;
    csatSurveyEnabled = inbox.csatSurveyEnabled ?? false;
  });

  async function handleSave() {
    successMessage = '';
    errorMessage = '';

    const updated = await inboxesStore.updateInbox(inboxId, {
      csat_survey_enabled: csatSurveyEnabled,
    });

    if (updated) {
      successMessage = 'CSAT settings updated successfully';
      return;
    }

    errorMessage = inboxesStore.error || 'Failed to update CSAT settings';
  }
</script>

<div class="space-y-6">
  <InboxSettingsHeader {accountId} {inbox} />

  {#if inbox}
    <InboxSettingsTabs
      {accountId}
      inboxId={inbox.id}
      channelType={inbox.channelType}
      active="csat"
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
    <div class="py-20 text-center text-muted-foreground">Loading CSAT settings...</div>
  {:else if !inbox}
    <div class="py-20 text-center text-muted-foreground">Inbox not found</div>
  {:else}
    <Card.Root>
      <Card.Header>
        <Card.Title>Customer Satisfaction</Card.Title>
        <Card.Description>
          Enable post-conversation CSAT collection for this inbox.
        </Card.Description>
      </Card.Header>
      <Card.Content class="space-y-6">
        <div class="flex items-center justify-between rounded-lg border p-4">
          <div class="space-y-0.5">
            <Label>Enable CSAT Survey</Label>
            <p class="text-sm text-muted-foreground">
              Ask customers for feedback after a conversation is resolved.
            </p>
          </div>
          <Switch bind:checked={csatSurveyEnabled} />
        </div>

        <div class="space-y-2">
          <Label for="survey-preview">Survey Message Preview</Label>
          <Textarea id="survey-preview" bind:value={surveyMessage} rows={3} disabled />
        </div>
      </Card.Content>
      <Card.Footer class="justify-end">
        <Button onclick={handleSave} disabled={isUpdating}>
          {isUpdating ? 'Saving...' : 'Save CSAT Settings'}
        </Button>
      </Card.Footer>
    </Card.Root>
  {/if}
</div>
