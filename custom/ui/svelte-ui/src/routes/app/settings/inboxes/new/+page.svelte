<script lang="ts">
  /**
   * Inbox Creation Wizard
   * Multi-step wizard for creating different types of inboxes
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import * as Select from '$lib/components/ui/select';
  import { Switch } from '$lib/components/ui/switch';
  import type { CreateInboxParams } from '$lib/api/inboxes';

  let accountId = $derived($page.params.accountId);
  
  // Wizard state
  let currentStep = $state(1);
  let selectedChannelType = $state<string>('');
  let isCreating = $derived(inboxesStore.uiFlags.isCreating);
  
  // Form data
  let inboxName = $state('');
  let greetingMessage = $state('Welcome! How can we help you today?');
  let greetingEnabled = $state(true);
  let enableAutoAssignment = $state(true);
  let workingHoursEnabled = $state(false);
  let timezone = $state('UTC');
  
  // Channel-specific data
  let websiteUrl = $state('');
  let widgetColor = $state('#1f93ff');
  let emailAddress = $state('');
  let phoneNumber = $state('');
  let apiKey = $state('');
  
  // Errors
  let errors = $state<Record<string, string>>({});
  
  const channelTypes = [
    {
      type: 'Channel::WebWidget',
      name: 'Website',
      icon: '💬',
      description: 'Live chat widget for your website',
    },
    {
      type: 'Channel::Api',
      name: 'API',
      icon: '🔌',
      description: 'Custom channel using API',
    },
    {
      type: 'Channel::Email',
      name: 'Email',
      icon: '📧',
      description: 'Support via email',
    },
    {
      type: 'Channel::Whatsapp',
      name: 'WhatsApp',
      icon: '📱',
      description: 'WhatsApp Business API',
    },
    {
      type: 'Channel::Sms',
      name: 'SMS',
      icon: '💌',
      description: 'SMS channel',
    },
  ];
  
  function handleChannelSelect(channelType: string) {
    selectedChannelType = channelType;
    currentStep = 2;
  }
  
  function handleBack() {
    if (currentStep > 1) {
      currentStep--;
    } else {
      goto(`/app/${accountId}/settings/inboxes`);
    }
  }
  
  function validateStep2(): boolean {
    errors = {};
    
    if (!inboxName.trim()) {
      errors.inboxName = 'Inbox name is required';
    }
    
    // Channel-specific validation
    if (selectedChannelType === 'Channel::WebWidget' && !websiteUrl.trim()) {
      errors.websiteUrl = 'Website URL is required';
    }
    
    if (selectedChannelType === 'Channel::Email' && !emailAddress.trim()) {
      errors.emailAddress = 'Email address is required';
    } else if (selectedChannelType === 'Channel::Email' && emailAddress.trim()) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(emailAddress)) {
        errors.emailAddress = 'Invalid email address';
      }
    }
    
    if (selectedChannelType === 'Channel::Sms' && !phoneNumber.trim()) {
      errors.phoneNumber = 'Phone number is required';
    }
    
    if (selectedChannelType === 'Channel::Whatsapp' && !phoneNumber.trim()) {
      errors.phoneNumber = 'Phone number is required';
    }
    
    return Object.keys(errors).length === 0;
  }
  
  async function handleCreate() {
    if (!validateStep2()) {
      return;
    }
    
    // Build channel data based on selected type
    const channelData: Record<string, any> = {};
    
    if (selectedChannelType === 'Channel::WebWidget') {
      channelData.website_url = websiteUrl;
      channelData.widget_color = widgetColor;
      channelData.website_name = inboxName;
    } else if (selectedChannelType === 'Channel::Email') {
      channelData.email = emailAddress;
      channelData.imap_enabled = false;
      channelData.smtp_enabled = false;
    } else if (selectedChannelType === 'Channel::Sms') {
      channelData.phone_number = phoneNumber;
      channelData.provider = 'default';
    } else if (selectedChannelType === 'Channel::Whatsapp') {
      channelData.phone_number = phoneNumber;
      channelData.provider = 'whatsapp_cloud';
    } else if (selectedChannelType === 'Channel::Api') {
      channelData.webhook_url = '';
    }
    
    const params: CreateInboxParams = {
      name: inboxName,
      channelType: selectedChannelType,
      channelData,
      greetingEnabled,
      greetingMessage: greetingEnabled ? greetingMessage : undefined,
      enableAutoAssignment,
      workingHoursEnabled,
      timezone,
    };
    
    const inbox = await inboxesStore.createInbox(params);
    
    if (inbox) {
      goto(`/app/${accountId}/settings/inboxes`);
    }
  }
</script>

<div class="space-y-6 max-w-4xl mx-auto">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      ← Back
    </Button>
    <div>
      <h1 class="text-3xl font-bold">Create Inbox</h1>
      <p class="text-muted-foreground mt-1">
        {#if currentStep === 1}
          Choose a channel type to get started
        {:else if currentStep === 2}
          Configure your {channelTypes.find(c => c.type === selectedChannelType)?.name} inbox
        {/if}
      </p>
    </div>
  </div>

  <!-- Progress Steps -->
  <div class="flex items-center gap-2">
    <div class="flex items-center gap-2">
      <div class="flex h-8 w-8 items-center justify-center rounded-full {currentStep === 1 ? 'bg-blue-600 text-white' : 'bg-gray-200'}">
        1
      </div>
      <span class="text-sm {currentStep === 1 ? 'font-semibold' : 'text-gray-600'}">Select Channel</span>
    </div>
    <div class="h-px w-12 bg-gray-300"></div>
    <div class="flex items-center gap-2">
      <div class="flex h-8 w-8 items-center justify-center rounded-full {currentStep === 2 ? 'bg-blue-600 text-white' : 'bg-gray-200'}">
        2
      </div>
      <span class="text-sm {currentStep === 2 ? 'font-semibold' : 'text-gray-600'}">Configure</span>
    </div>
  </div>

  {#if currentStep === 1}
    <!-- Step 1: Channel Selection -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      {#each channelTypes as channel}
        <Card.Root
          class="hover:shadow-md transition-shadow cursor-pointer hover:border-blue-500"
          onclick={() => handleChannelSelect(channel.type)}
        >
          <Card.Content class="p-6">
            <div class="flex flex-col items-center text-center gap-3">
              <div class="text-5xl">{channel.icon}</div>
              <h3 class="font-semibold text-lg">{channel.name}</h3>
              <p class="text-sm text-gray-600">{channel.description}</p>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {:else if currentStep === 2}
    <!-- Step 2: Configuration -->
    <Card.Root>
      <Card.Content class="p-6 space-y-6">
        <!-- Basic Configuration -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Basic Information</h3>
          
          <div class="space-y-4">
            <div>
              <Label for="inboxName">Inbox Name <span class="text-red-500">*</span></Label>
              <Input
                id="inboxName"
                bind:value={inboxName}
                placeholder="e.g., Website Support, Sales Inquiries"
                class={errors.inboxName ? 'border-red-500' : ''}
              />
              {#if errors.inboxName}
                <p class="text-sm text-red-500 mt-1">{errors.inboxName}</p>
              {/if}
            </div>
          </div>
        </div>

        <!-- Channel-Specific Configuration -->
        <div>
          <h3 class="text-lg font-semibold mb-4">
            {channelTypes.find(c => c.type === selectedChannelType)?.name} Settings
          </h3>
          
          {#if selectedChannelType === 'Channel::WebWidget'}
            <div class="space-y-4">
              <div>
                <Label for="websiteUrl">Website URL <span class="text-red-500">*</span></Label>
                <Input
                  id="websiteUrl"
                  bind:value={websiteUrl}
                  placeholder="https://www.example.com"
                  class={errors.websiteUrl ? 'border-red-500' : ''}
                />
                {#if errors.websiteUrl}
                  <p class="text-sm text-red-500 mt-1">{errors.websiteUrl}</p>
                {/if}
              </div>
              
              <div>
                <Label for="widgetColor">Widget Color</Label>
                <div class="flex gap-2">
                  <Input
                    id="widgetColor"
                    type="color"
                    bind:value={widgetColor}
                    class="w-20 h-10"
                  />
                  <Input
                    type="text"
                    bind:value={widgetColor}
                    placeholder="#1f93ff"
                    class="flex-1"
                  />
                </div>
              </div>
            </div>
          {:else if selectedChannelType === 'Channel::Email'}
            <div class="space-y-4">
              <div>
                <Label for="emailAddress">Email Address <span class="text-red-500">*</span></Label>
                <Input
                  id="emailAddress"
                  type="email"
                  bind:value={emailAddress}
                  placeholder="support@example.com"
                  class={errors.emailAddress ? 'border-red-500' : ''}
                />
                {#if errors.emailAddress}
                  <p class="text-sm text-red-500 mt-1">{errors.emailAddress}</p>
                {/if}
                <p class="text-sm text-gray-600 mt-1">
                  Email forwarding will be configured after creation
                </p>
              </div>
            </div>
          {:else if selectedChannelType === 'Channel::Sms' || selectedChannelType === 'Channel::Whatsapp'}
            <div class="space-y-4">
              <div>
                <Label for="phoneNumber">Phone Number <span class="text-red-500">*</span></Label>
                <Input
                  id="phoneNumber"
                  type="tel"
                  bind:value={phoneNumber}
                  placeholder="+1234567890"
                  class={errors.phoneNumber ? 'border-red-500' : ''}
                />
                {#if errors.phoneNumber}
                  <p class="text-sm text-red-500 mt-1">{errors.phoneNumber}</p>
                {/if}
                <p class="text-sm text-gray-600 mt-1">
                  {selectedChannelType === 'Channel::Whatsapp' 
                    ? 'WhatsApp Business API credentials will be configured after creation'
                    : 'SMS provider credentials will be configured after creation'}
                </p>
              </div>
            </div>
          {:else if selectedChannelType === 'Channel::Api'}
            <div class="space-y-4">
              <div>
                <p class="text-sm text-gray-600">
                  API channel allows you to integrate custom messaging platforms. 
                  API credentials will be generated after creation.
                </p>
              </div>
            </div>
          {/if}
        </div>

        <!-- Greeting Configuration -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Greeting Settings</h3>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Enable Greeting Message</Label>
                <p class="text-sm text-gray-600">Show a greeting when conversation starts</p>
              </div>
              <Switch bind:checked={greetingEnabled} />
            </div>
            
            {#if greetingEnabled}
              <div>
                <Label for="greetingMessage">Greeting Message</Label>
                <Textarea
                  id="greetingMessage"
                  bind:value={greetingMessage}
                  placeholder="Welcome! How can we help you today?"
                  rows={3}
                />
              </div>
            {/if}
          </div>
        </div>

        <!-- Additional Settings -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Additional Settings</h3>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Auto Assignment</Label>
                <p class="text-sm text-gray-600">Automatically assign conversations to agents</p>
              </div>
              <Switch bind:checked={enableAutoAssignment} />
            </div>
            
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Working Hours</Label>
                <p class="text-sm text-gray-600">Enable working hours for this inbox</p>
              </div>
              <Switch bind:checked={workingHoursEnabled} />
            </div>
            
            {#if workingHoursEnabled}
              <div>
                <Label for="timezone">Timezone</Label>
                <Select.Root>
                  <Select.Trigger class="w-full">
                    <Select.Value placeholder={timezone} />
                  </Select.Trigger>
                  <Select.Content>
                    <Select.Item value="UTC">UTC</Select.Item>
                    <Select.Item value="America/New_York">Eastern Time (ET)</Select.Item>
                    <Select.Item value="America/Chicago">Central Time (CT)</Select.Item>
                    <Select.Item value="America/Denver">Mountain Time (MT)</Select.Item>
                    <Select.Item value="America/Los_Angeles">Pacific Time (PT)</Select.Item>
                    <Select.Item value="Europe/London">London (GMT)</Select.Item>
                    <Select.Item value="Europe/Paris">Central European Time (CET)</Select.Item>
                    <Select.Item value="Asia/Tokyo">Tokyo (JST)</Select.Item>
                    <Select.Item value="Asia/Shanghai">China (CST)</Select.Item>
                    <Select.Item value="Australia/Sydney">Sydney (AEST)</Select.Item>
                  </Select.Content>
                </Select.Root>
              </div>
            {/if}
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t">
          <Button variant="outline" onclick={handleBack} disabled={isCreating}>
            Back
          </Button>
          <Button onclick={handleCreate} disabled={isCreating}>
            {isCreating ? 'Creating...' : 'Create Inbox'}
          </Button>
        </div>
      </Card.Content>
    </Card.Root>
  {/if}
</div>
