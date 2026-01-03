<script lang="ts">
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Button } from '$lib/components/ui/button';
  import { Select } from '$lib/components/ui/select';
  import { Badge } from '$lib/components/ui/badge';
  import { campaignsStore } from '$lib/stores/campaigns.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  
  interface Props {
    mode?: 'create' | 'edit';
    campaign?: any;
    onsubmit?: (data: any) => void;
    oncancel?: () => void;
  }
  
  let {
    mode = 'create',
    campaign = null,
    onsubmit,
    oncancel
  }: Props = $props();
  
  // Form state
  let title = $state(campaign?.title || '');
  let inboxId = $state<number | null>(campaign?.inboxId || null);
  let templateId = $state<number | null>(campaign?.templateId || null);
  let scheduledAt = $state(campaign?.scheduledAt || '');
  let selectedAudience = $state<number[]>(campaign?.audience?.map((a: any) => a.id) || []);
  let templateParams = $state<Record<string, string>>({});
  
  // UI state
  let isSubmitting = $state(false);
  let errors = $state<Record<string, string>>({});
  
  // Computed
  let whatsappInboxes = $derived(
    inboxesStore.allInboxes.filter(
      inbox => inbox.channelType === 'Channel::Whatsapp'
    )
  );
  
  let inboxOptions = $derived(
    whatsappInboxes.map(inbox => ({
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
  
  // Mock template options - in real implementation, these would come from the inbox
  let templateOptions = $derived(() => {
    if (!inboxId) return [];
    
    // Mock templates for demo - replace with actual API call
    return [
      { value: '1', label: 'Welcome Message (en)', params: ['{{1}}'] },
      { value: '2', label: 'Order Confirmation (en)', params: ['{{1}}', '{{2}}'] },
      { value: '3', label: 'Appointment Reminder (en)', params: ['{{1}}', '{{2}}'] },
    ];
  });
  
  let selectedTemplate = $derived(
    templateOptions().find(t => t.value === templateId?.toString())
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
    
    if (!inboxId) {
      errors.inboxId = 'Please select a WhatsApp inbox';
    }
    
    if (!templateId) {
      errors.templateId = 'Please select a template';
    }
    
    if (!scheduledAt) {
      errors.scheduledAt = 'Please select a date and time';
    }
    
    if (selectedAudience.length === 0) {
      errors.selectedAudience = 'Please select at least one audience label';
    }
    
    // Validate template parameters
    if (selectedTemplate?.params) {
      for (const param of selectedTemplate.params) {
        const paramKey = param.replace(/[{}]/g, '');
        if (!templateParams[paramKey]?.trim()) {
          errors[`param_${paramKey}`] = `Parameter ${paramKey} is required`;
        }
      }
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
        inbox_id: inboxId,
        template_id: templateId,
        template_params: templateParams,
        scheduled_at: new Date(scheduledAt).toISOString(),
        audience: selectedAudience.map(id => ({
          id,
          type: 'Label'
        })),
        campaign_type: 'one_off',
        campaign_status: 'active'
      };
      
      if (mode === 'create') {
        await campaignsStore.createCampaign(campaignData);
      } else if (campaign?.id) {
        await campaignsStore.updateCampaign(campaign.id, campaignData);
      }
      
      if (onsubmit) onsubmit(campaignData);
    } catch (error) {
      console.error('Failed to save WhatsApp campaign:', error);
      errors.submit = 'Failed to save campaign. Please try again.';
    } finally {
      isSubmitting = false;
    }
  }
  
  function handleCancel() {
    if (oncancel) oncancel();
  }
  
  function handleAudienceChange(labelId: string) {
    const id = parseInt(labelId);
    if (selectedAudience.includes(id)) {
      selectedAudience = selectedAudience.filter(a => a !== id);
    } else {
      selectedAudience = [...selectedAudience, id];
    }
  }
  
  // Reset template when inbox changes
  $effect(() => {
    if (inboxId) {
      templateId = null;
      templateParams = {};
    }
  });
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
  
  <!-- Inbox Selection -->
  <div class="space-y-2">
    <Label for="inbox">WhatsApp Inbox *</Label>
    <Select.Root
      onValueChange={(value) => { inboxId = value ? parseInt(value) : null; }}
      value={inboxId?.toString()}
    >
      <Select.Trigger id="inbox" class={errors.inboxId ? 'border-red-500' : ''}>
        <Select.Value placeholder="Select WhatsApp inbox" />
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
  
  <!-- Template Selection -->
  <div class="space-y-2">
    <Label for="template">WhatsApp Template *</Label>
    <Select.Root
      onValueChange={(value) => { templateId = value ? parseInt(value) : null; }}
      value={templateId?.toString()}
      disabled={!inboxId}
    >
      <Select.Trigger id="template" class={errors.templateId ? 'border-red-500' : ''}>
        <Select.Value placeholder={inboxId ? "Select a template" : "Select inbox first"} />
      </Select.Trigger>
      <Select.Content>
        {#each templateOptions() as option}
          <Select.Item value={option.value}>{option.label}</Select.Item>
        {/each}
      </Select.Content>
    </Select.Root>
    {#if errors.templateId}
      <p class="text-sm text-red-500">{errors.templateId}</p>
    {/if}
    <p class="text-xs text-gray-500">
      Only approved WhatsApp templates can be used for campaigns
    </p>
  </div>
  
  <!-- Template Parameters -->
  {#if selectedTemplate?.params && selectedTemplate.params.length > 0}
    <div class="space-y-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border">
      <div class="flex items-center gap-2">
        <Badge variant="secondary">Template Parameters</Badge>
      </div>
      
      {#each selectedTemplate.params as param}
        {@const paramKey = param.replace(/[{}]/g, '')}
        <div class="space-y-2">
          <Label for={`param-${paramKey}`}>
            Parameter {paramKey} *
          </Label>
          <Input
            id={`param-${paramKey}`}
            bind:value={templateParams[paramKey]}
            placeholder={`Enter value for ${param}`}
            class={errors[`param_${paramKey}`] ? 'border-red-500' : ''}
          />
          {#if errors[`param_${paramKey}`]}
            <p class="text-sm text-red-500">{errors[`param_${paramKey}`]}</p>
          {/if}
        </div>
      {/each}
      
      <p class="text-xs text-gray-500 mt-2">
        These values will be used in the template placeholders
      </p>
    </div>
  {/if}
  
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
