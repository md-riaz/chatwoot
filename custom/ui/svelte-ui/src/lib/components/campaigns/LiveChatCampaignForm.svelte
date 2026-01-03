<script lang="ts">
  /**
   * LiveChatCampaignForm
   * Form for creating/editing live chat campaigns
   */
  import { onMount, createEventDispatcher } from 'svelte';
  import { page } from '$app/stores';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import * as Select from '$lib/components/ui/select';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Switch } from '$lib/components/ui/switch';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { CAMPAIGN_TYPES } from '$lib/api/campaigns';
  import type { Campaign, CreateCampaignParams } from '$lib/api/campaigns';

  interface Props {
    mode: 'create' | 'edit';
    campaign?: Campaign | null;
  }

  let { mode, campaign = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateCampaignParams;
    cancel: void;
  }>();

  let title = $state('');
  let message = $state('');
  let inboxId = $state<number | null>(null);
  let senderId = $state(0);
  let enabled = $state(true);
  let triggerOnlyDuringBusinessHours = $state(false);
  let endPoint = $state('');
  let timeOnPage = $state(10);

  let errors = $state<Record<string, string>>({});
  let isSubmitting = $state(false);

  let webInboxes = $derived(
    inboxesStore.allInboxes.filter(
      (inbox) => inbox.channelType === 'Channel::WebWidget'
    )
  );

  onMount(() => {
    // Load inboxes
    inboxesStore.fetchInboxes();

    // If editing, populate form with campaign data
    if (mode === 'edit' && campaign) {
      title = campaign.title;
      message = campaign.message;
      inboxId = campaign.inboxId;
      senderId = campaign.senderId || 0;
      enabled = campaign.enabled;
      endPoint = campaign.triggerRules?.url || '';
      timeOnPage = campaign.triggerRules?.timeOnPage || 10;
    }
  });

  function validateForm(): boolean {
    errors = {};
    let isValid = true;

    if (!title || title.length < 1) {
      errors.title = 'Title is required';
      isValid = false;
    }

    if (!message || message.length < 1) {
      errors.message = 'Message is required';
      isValid = false;
    }

    if (!inboxId) {
      errors.inboxId = 'Inbox is required';
      isValid = false;
    }

    if (!endPoint) {
      errors.endPoint = 'URL is required';
      isValid = false;
    } else {
      // Validate URL format
      try {
        const url = new URL(endPoint);
        if (!url.protocol.startsWith('http')) {
          errors.endPoint = 'URL must start with http:// or https://';
          isValid = false;
        }
      } catch {
        errors.endPoint = 'Please enter a valid URL';
        isValid = false;
      }
    }

    if (!timeOnPage || timeOnPage < 0) {
      errors.timeOnPage = 'Time on page must be a positive number';
      isValid = false;
    }

    return isValid;
  }

  function handleSubmit() {
    if (!validateForm()) {
      return;
    }

    isSubmitting = true;

    const campaignData: CreateCampaignParams = {
      title,
      message,
      campaignType: CAMPAIGN_TYPES.ONGOING,
      inboxId: inboxId!,
      senderId: senderId || undefined,
      enabled,
      triggerRules: {
        url: endPoint,
        timeOnPage,
      },
    };

    dispatch('submit', campaignData);
    isSubmitting = false;
  }

  function handleCancel() {
    dispatch('cancel');
  }
</script>

<div class="space-y-6">
  <div class="space-y-4">
    <div class="space-y-2">
      <Label for="title">Campaign Title *</Label>
      <Input
        id="title"
        bind:value={title}
        placeholder="e.g., Welcome Campaign"
        class={errors.title ? 'border-red-500' : ''}
      />
      {#if errors.title}
        <p class="text-sm text-red-500">{errors.title}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="inbox">Inbox *</Label>
      <Select.Root
        selected={webInboxes.find((i) => i.id === inboxId)
          ? { value: inboxId!, label: webInboxes.find((i) => i.id === inboxId)!.name }
          : undefined}
        onSelectedChange={(v) => {
          if (v) inboxId = v.value as number;
        }}
      >
        <Select.Trigger class={errors.inboxId ? 'border-red-500' : ''}>
          <Select.Value placeholder="Select an inbox" />
        </Select.Trigger>
        <Select.Content>
          {#each webInboxes as inbox}
            <Select.Item value={inbox.id} label={inbox.name}>
              {inbox.name}
            </Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
      {#if errors.inboxId}
        <p class="text-sm text-red-500">{errors.inboxId}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="message">Campaign Message *</Label>
      <Textarea
        id="message"
        bind:value={message}
        placeholder="Enter your campaign message..."
        rows={4}
        class={errors.message ? 'border-red-500' : ''}
      />
      {#if errors.message}
        <p class="text-sm text-red-500">{errors.message}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="endPoint">Target URL *</Label>
      <Input
        id="endPoint"
        bind:value={endPoint}
        placeholder="https://example.com/page"
        class={errors.endPoint ? 'border-red-500' : ''}
      />
      <p class="text-sm text-muted-foreground">
        Campaign will trigger when visitors are on this URL
      </p>
      {#if errors.endPoint}
        <p class="text-sm text-red-500">{errors.endPoint}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="timeOnPage">Time on Page (seconds) *</Label>
      <Input
        id="timeOnPage"
        type="number"
        bind:value={timeOnPage}
        min="0"
        placeholder="10"
        class={errors.timeOnPage ? 'border-red-500' : ''}
      />
      <p class="text-sm text-muted-foreground">
        Campaign will trigger after visitor spends this many seconds on the page
      </p>
      {#if errors.timeOnPage}
        <p class="text-sm text-red-500">{errors.timeOnPage}</p>
      {/if}
    </div>

    <div class="flex items-center space-x-2">
      <Switch id="enabled" bind:checked={enabled} />
      <Label for="enabled">Enable campaign</Label>
    </div>

    <div class="flex items-center space-x-2">
      <Switch
        id="businessHours"
        bind:checked={triggerOnlyDuringBusinessHours}
      />
      <Label for="businessHours">Trigger only during business hours</Label>
    </div>
  </div>

  <div class="flex justify-end gap-2">
    <Button variant="outline" onclick={handleCancel} disabled={isSubmitting}>
      Cancel
    </Button>
    <Button onclick={handleSubmit} disabled={isSubmitting}>
      {isSubmitting ? 'Saving...' : mode === 'create' ? 'Create Campaign' : 'Update Campaign'}
    </Button>
  </div>
</div>
