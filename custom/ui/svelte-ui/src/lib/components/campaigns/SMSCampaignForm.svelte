<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Button } from '$lib/components/ui/button';
  import * as Select from '$lib/components/ui/select';
  import { campaignsStore } from '$lib/stores/campaigns.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import type { Campaign, CreateCampaignParams } from '$lib/api/campaigns';
  
  interface Props {
    mode?: 'create' | 'edit';
    campaign?: Campaign | null;
  }
  
  let {
    mode = 'create',
    campaign = null
  }: Props = $props();
  
  const dispatch = createEventDispatcher<{
    submit: CreateCampaignParams;
    cancel: void;
  }>();
  
  // Form state
  let title = $state(campaign?.title || '');
  let message = $state(campaign?.message || '');
  let inboxId = $state<number | null>(campaign?.inboxId || null);
  let scheduledAt = $state(campaign?.scheduledAt || '');
  let selectedAudience = $state<number[]>(campaign?.audience?.map((a: any) => a.id) || []);
  
  // UI state
  let isSubmitting = $state(false);
  let errors = $state<Record<string, string>>({});
  
  // Computed
  let smsInboxes = $derived(
    inboxesStore.allInboxes.filter(
      inbox => inbox.channelType === 'Channel::Sms' || 
               inbox.channelType === 'Channel::TwilioSms'
    )
  );
  
  let inboxOptions = $derived(
    smsInboxes.map(inbox => ({
      value: inbox.id.toString(),
      label: inbox.name
    }))
  );
  
  let labelOptions = $derived(
    labelsStore.allLabels.map(label => ({
      value: label.id.toString(),
      label: label.title
    }))
  );
  
  // Get current datetime for min attribute
  let currentDateTime = $derived(() => {
    const now = new Date();
    const localTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
    return localTime.toISOString().slice(0, 16);
  });
  
  function validateForm(): boolean {
    errors = {};
    
    if (!title.trim()) {
      errors.title = 'Title is required';
    }
    
    if (!message.trim()) {
      errors.message = 'Message is required';
    }
    
    if (!inboxId) {
      errors.inboxId = 'Please select an SMS inbox';
    }
    
    if (!scheduledAt) {
      errors.scheduledAt = 'Please select a date and time';
    }
    
    if (selectedAudience.length === 0) {
      errors.selectedAudience = 'Please select at least one audience label';
    }
    
    return Object.keys(errors).length === 0;
  }
  
  async function handleSubmit(e: Event) {
    e.preventDefault();
    
    if (!validateForm()) return;
    
    isSubmitting = true;
    
    try {
      const campaignData = {
        title: title.trim(),
        message: message.trim(),
        inbox_id: inboxId,
        scheduled_at: new Date(scheduledAt).toISOString(),
        audience: selectedAudience.map(id => ({
          id,
          type: 'Label'
        })),
        campaign_type: 'one_off',
        campaign_status: 'active'
      };
      
      dispatch('submit', campaignData);
    } catch (error) {
      console.error('Failed to save SMS campaign:', error);
      errors.submit = 'Failed to save campaign. Please try again.';
    } finally {
      isSubmitting = false;
    }
  }
  
  function handleCancel() {
    dispatch('cancel');
  }
  
  function handleAudienceChange(labelId: string) {
    const id = parseInt(labelId);
    if (selectedAudience.includes(id)) {
      selectedAudience = selectedAudience.filter(a => a !== id);
    } else {
      selectedAudience = [...selectedAudience, id];
    }
  }
</script>

<form class="space-y-4" onsubmit={handleSubmit}>
  <!-- Title -->
  <div class="space-y-2">
    <Label for="title">Campaign Title *</Label>
    <Input
      id="title"
      bind:value={title}
      placeholder="Enter campaign title"
      class={errors.title ? 'border-red-500' : ''}
    />
    {#if errors.title}
      <p class="text-sm text-red-500">{errors.title}</p>
    {/if}
  </div>
  
  <!-- Message -->
  <div class="space-y-2">
    <Label for="message">Message *</Label>
    <Textarea
      id="message"
      bind:value={message}
      placeholder="Enter your SMS message"
      rows={4}
      class={errors.message ? 'border-red-500' : ''}
    />
    <p class="text-xs text-gray-500">{message.length} characters</p>
    {#if errors.message}
      <p class="text-sm text-red-500">{errors.message}</p>
    {/if}
  </div>
  
  <!-- Inbox Selection -->
  <div class="space-y-2">
    <Label for="inbox">SMS Inbox *</Label>
    <Select.Root
      onValueChange={(value) => { inboxId = value ? parseInt(value) : null; }}
      value={inboxId?.toString()}
    >
      <Select.Trigger id="inbox" class={errors.inboxId ? 'border-red-500' : ''}>
        <Select.Value placeholder="Select SMS inbox" />
      </Select.Trigger>
      <Select.Content>
        {#each inboxOptions as option}
          <Select.Item value={option.value}>{option.label}</Select.Item>
        {/each}
      </Select.Content>
    </Select.Root>
    {#if errors.inboxId}
      <p class="text-sm text-red-500">{errors.inboxId}</p>
    {/if}
  </div>
  
  <!-- Audience Selection -->
  <div class="space-y-2">
    <Label>Target Audience (Labels) *</Label>
    <div class="border rounded-md p-3 space-y-2 max-h-40 overflow-y-auto">
      {#each labelOptions as option}
        <label class="flex items-center space-x-2 cursor-pointer">
          <input
            type="checkbox"
            checked={selectedAudience.includes(parseInt(option.value))}
            onchange={() => handleAudienceChange(option.value)}
            class="rounded border-gray-300"
          />
          <span class="text-sm">{option.label}</span>
        </label>
      {/each}
    </div>
    {#if errors.selectedAudience}
      <p class="text-sm text-red-500">{errors.selectedAudience}</p>
    {/if}
    <p class="text-xs text-gray-500">
      Select labels to target specific contact segments
    </p>
  </div>
  
  <!-- Scheduled At -->
  <div class="space-y-2">
    <Label for="scheduledAt">Schedule Date & Time *</Label>
    <Input
      id="scheduledAt"
      type="datetime-local"
      bind:value={scheduledAt}
      min={currentDateTime()}
      class={errors.scheduledAt ? 'border-red-500' : ''}
    />
    {#if errors.scheduledAt}
      <p class="text-sm text-red-500">{errors.scheduledAt}</p>
    {/if}
  </div>
  
  <!-- Error Message -->
  {#if errors.submit}
    <div class="bg-red-50 border border-red-200 rounded-md p-3">
      <p class="text-sm text-red-600">{errors.submit}</p>
    </div>
  {/if}
  
  <!-- Actions -->
  <div class="flex gap-3 pt-4">
    <Button
      type="button"
      variant="outline"
      class="flex-1"
      onclick={handleCancel}
      disabled={isSubmitting}
    >
      Cancel
    </Button>
    <Button
      type="submit"
      class="flex-1"
      disabled={isSubmitting}
    >
      {isSubmitting ? 'Saving...' : mode === 'create' ? 'Create Campaign' : 'Update Campaign'}
    </Button>
  </div>
</form>
