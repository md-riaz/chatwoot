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
  let selectedProvider = $state<string>('');
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
  
  // Twilio common fields
  let accountSid = $state('');
  let authToken = $state('');
  let apiKeySid = $state('');
  let apiKeySecret = $state('');
  let messagingServiceSid = $state('');
  let useApiKey = $state(false);
  let useMessagingService = $state(false);
  
  // Bandwidth SMS fields
  let bandwidthAccountId = $state('');
  let bandwidthUsername = $state('');
  let bandwidthPassword = $state('');
  let bandwidthApplicationId = $state('');
  
  // WhatsApp Cloud fields
  let phoneNumberId = $state('');
  let businessAccountId = $state('');
  let accessToken = $state('');
  
  // 360Dialog fields
  let dialog360ApiKey = $state('');
  let dialog360PartnerId = $state('');
  
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
    {
      type: 'Channel::Voice',
      name: 'Voice',
      icon: '📞',
      description: 'Voice calls via Twilio',
    },
  ];
  
  // Provider options
  const smsProviders = [
    { value: 'twilio', label: 'Twilio' },
    { value: 'bandwidth', label: 'Bandwidth' },
  ];
  
  const whatsappProviders = [
    { value: 'whatsapp_cloud', label: 'WhatsApp Cloud' },
    { value: 'twilio', label: 'Twilio' },
    { value: '360dialog', label: '360Dialog' },
  ];
  
  function needsProviderSelection(channelType: string): boolean {
    return channelType === 'Channel::Sms' || channelType === 'Channel::Whatsapp';
  }
  
  function handleChannelSelect(channelType: string) {
    selectedChannelType = channelType;
    if (needsProviderSelection(channelType)) {
      currentStep = 2; // Provider selection step
    } else {
      selectedProvider = '';
      currentStep = 3; // Configuration step
    }
  }
  
  function handleProviderSelect(provider: string) {
    selectedProvider = provider;
    currentStep = 3; // Configuration step
  }
  
  function handleBack() {
    if (currentStep === 3 && needsProviderSelection(selectedChannelType)) {
      // From config back to provider selection
      currentStep = 2;
    } else if (currentStep > 1) {
      currentStep--;
      if (currentStep === 1) {
        selectedChannelType = '';
        selectedProvider = '';
      }
    } else {
      goto(`/app/accounts/${accountId}/settings/inboxes`);
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
    
    // SMS validation based on provider
    if (selectedChannelType === 'Channel::Sms') {
      if (selectedProvider === 'twilio') {
        if (!accountSid.trim()) errors.accountSid = 'Account SID is required';
        if (useApiKey) {
          if (!apiKeySid.trim()) errors.apiKeySid = 'API Key SID is required';
          if (!apiKeySecret.trim()) errors.apiKeySecret = 'API Key Secret is required';
        } else {
          if (!authToken.trim()) errors.authToken = 'Auth Token is required';
        }
        if (useMessagingService) {
          if (!messagingServiceSid.trim()) errors.messagingServiceSid = 'Messaging Service SID is required';
        } else {
          if (!phoneNumber.trim()) errors.phoneNumber = 'Phone number is required';
        }
      } else if (selectedProvider === 'bandwidth') {
        if (!bandwidthAccountId.trim()) errors.bandwidthAccountId = 'Account ID is required';
        if (!bandwidthUsername.trim()) errors.bandwidthUsername = 'Username is required';
        if (!bandwidthPassword.trim()) errors.bandwidthPassword = 'Password is required';
        if (!bandwidthApplicationId.trim()) errors.bandwidthApplicationId = 'Application ID is required';
        if (!phoneNumber.trim()) errors.phoneNumber = 'Phone number is required';
      }
    }
    
    // WhatsApp validation based on provider
    if (selectedChannelType === 'Channel::Whatsapp') {
      if (selectedProvider === 'whatsapp_cloud') {
        if (!phoneNumberId.trim()) errors.phoneNumberId = 'Phone Number ID is required';
        if (!businessAccountId.trim()) errors.businessAccountId = 'Business Account ID is required';
        if (!accessToken.trim()) errors.accessToken = 'Access Token is required';
      } else if (selectedProvider === 'twilio') {
        if (!accountSid.trim()) errors.accountSid = 'Account SID is required';
        if (useApiKey) {
          if (!apiKeySid.trim()) errors.apiKeySid = 'API Key SID is required';
          if (!apiKeySecret.trim()) errors.apiKeySecret = 'API Key Secret is required';
        } else {
          if (!authToken.trim()) errors.authToken = 'Auth Token is required';
        }
        if (!phoneNumber.trim()) errors.phoneNumber = 'Phone number is required';
      } else if (selectedProvider === '360dialog') {
        if (!dialog360ApiKey.trim()) errors.dialog360ApiKey = 'API Key is required';
      }
    }
    
    // Voice validation
    if (selectedChannelType === 'Channel::Voice') {
      if (!phoneNumber.trim()) errors.phoneNumber = 'Phone number is required';
      if (!accountSid.trim()) errors.accountSid = 'Account SID is required';
      if (useApiKey) {
        if (!apiKeySid.trim()) errors.apiKeySid = 'API Key SID is required';
        if (!apiKeySecret.trim()) errors.apiKeySecret = 'API Key Secret is required';
      } else {
        if (!authToken.trim()) errors.authToken = 'Auth Token is required';
      }
    }
    
    return Object.keys(errors).length === 0;
  }
  
  async function handleCreate() {
    if (!validateStep2()) {
      return;
    }
    
    // Build channel data based on selected type and provider
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
      if (selectedProvider === 'twilio') {
        channelData.provider = 'twilio';
        channelData.provider_config = {
          account_sid: accountSid,
          ...(useApiKey ? {
            api_key_sid: apiKeySid,
            api_key_secret: apiKeySecret,
          } : {
            auth_token: authToken,
          }),
          ...(useMessagingService ? {
            messaging_service_sid: messagingServiceSid,
          } : {
            phone_number: phoneNumber,
          }),
        };
      } else if (selectedProvider === 'bandwidth') {
        channelData.provider = 'bandwidth';
        channelData.provider_config = {
          account_id: bandwidthAccountId,
          username: bandwidthUsername,
          password: bandwidthPassword,
          application_id: bandwidthApplicationId,
        };
        channelData.phone_number = phoneNumber;
      }
    } else if (selectedChannelType === 'Channel::Whatsapp') {
      if (selectedProvider === 'whatsapp_cloud') {
        channelData.provider = 'whatsapp_cloud';
        channelData.provider_config = {
          phone_number_id: phoneNumberId,
          business_account_id: businessAccountId,
          api_key: accessToken,
        };
      } else if (selectedProvider === 'twilio') {
        channelData.provider = 'twilio';
        channelData.provider_config = {
          account_sid: accountSid,
          ...(useApiKey ? {
            api_key_sid: apiKeySid,
            api_key_secret: apiKeySecret,
          } : {
            auth_token: authToken,
          }),
        };
        channelData.phone_number = phoneNumber;
      } else if (selectedProvider === '360dialog') {
        channelData.provider = '360dialog';
        channelData.provider_config = {
          api_key: dialog360ApiKey,
          ...(dialog360PartnerId ? { partner_id: dialog360PartnerId } : {}),
        };
      }
    } else if (selectedChannelType === 'Channel::Voice') {
      channelData.phone_number = phoneNumber;
      channelData.provider = 'twilio';
      channelData.provider_config = {
        account_sid: accountSid,
        ...(useApiKey ? {
          api_key_sid: apiKeySid,
          api_key_secret: apiKeySecret,
        } : {
          auth_token: authToken,
        }),
      };
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
      goto(`/app/accounts/${accountId}/settings/inboxes`);
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
          Select a provider for {channelTypes.find(c => c.type === selectedChannelType)?.name}
        {:else if currentStep === 3}
          Configure your {channelTypes.find(c => c.type === selectedChannelType)?.name} inbox
        {/if}
      </p>
    </div>
  </div>

  <!-- Progress Steps -->
  <div class="flex items-center gap-2">
    <div class="flex items-center gap-2">
      <div class="flex h-8 w-8 items-center justify-center rounded-full {currentStep === 1 ? 'bg-blue-600 text-white' : currentStep > 1 ? 'bg-green-600 text-white' : 'bg-gray-200'}">
        {#if currentStep > 1}✓{:else}1{/if}
      </div>
      <span class="text-sm {currentStep === 1 ? 'font-semibold' : 'text-gray-600'}">Select Channel</span>
    </div>
    {#if needsProviderSelection(selectedChannelType)}
      <div class="h-px w-12 bg-gray-300"></div>
      <div class="flex items-center gap-2">
        <div class="flex h-8 w-8 items-center justify-center rounded-full {currentStep === 2 ? 'bg-blue-600 text-white' : currentStep > 2 ? 'bg-green-600 text-white' : 'bg-gray-200'}">
          {#if currentStep > 2}✓{:else}2{/if}
        </div>
        <span class="text-sm {currentStep === 2 ? 'font-semibold' : 'text-gray-600'}">Select Provider</span>
      </div>
    {/if}
    <div class="h-px w-12 bg-gray-300"></div>
    <div class="flex items-center gap-2">
      <div class="flex h-8 w-8 items-center justify-center rounded-full {currentStep === 3 || (currentStep === 2 && !needsProviderSelection(selectedChannelType)) ? 'bg-blue-600 text-white' : 'bg-gray-200'}">
        {needsProviderSelection(selectedChannelType) ? '3' : '2'}
      </div>
      <span class="text-sm {currentStep === 3 || (currentStep === 2 && !needsProviderSelection(selectedChannelType)) ? 'font-semibold' : 'text-gray-600'}">Configure</span>
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
  {:else if currentStep === 2 && needsProviderSelection(selectedChannelType)}
    <!-- Step 2: Provider Selection (for SMS and WhatsApp) -->
    <div>
      <h2 class="text-2xl font-bold mb-4">Select Provider</h2>
      <p class="text-muted-foreground mb-6">
        Choose a provider for your {channelTypes.find(c => c.type === selectedChannelType)?.name} channel
      </p>
      
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {#if selectedChannelType === 'Channel::Sms'}
          {#each smsProviders as provider}
            <Card.Root
              class="hover:shadow-md transition-shadow cursor-pointer hover:border-blue-500"
              onclick={() => handleProviderSelect(provider.value)}
            >
              <Card.Content class="p-6 text-center">
                <div class="text-4xl mb-3">📞</div>
                <h3 class="font-semibold text-lg mb-2">{provider.label}</h3>
              </Card.Content>
            </Card.Root>
          {/each}
        {:else if selectedChannelType === 'Channel::Whatsapp'}
          {#each whatsappProviders as provider}
            <Card.Root
              class="hover:shadow-md transition-shadow cursor-pointer hover:border-blue-500"
              onclick={() => handleProviderSelect(provider.value)}
            >
              <Card.Content class="p-6 text-center">
                <div class="text-4xl mb-3">💬</div>
                <h3 class="font-semibold text-lg mb-2">{provider.label}</h3>
              </Card.Content>
            </Card.Root>
          {/each}
        {/if}
      </div>
    </div>
  {:else if currentStep === 3 || (currentStep === 2 && !needsProviderSelection(selectedChannelType))}
    <!-- Step 3 (or 2 for non-provider channels): Configuration -->
    <Card.Root>
      <Card.Content class="p-6 space-y-6">
        <!-- Provider indication -->
        {#if selectedProvider}
          <div class="bg-blue-50 border border-blue-200 rounded-md p-3 mb-4">
            <p class="text-sm text-blue-900">
              <span class="font-semibold">Provider:</span> 
              {#if selectedChannelType === 'Channel::Sms'}
                {smsProviders.find(p => p.value === selectedProvider)?.label}
              {:else if selectedChannelType === 'Channel::Whatsapp'}
                {whatsappProviders.find(p => p.value === selectedProvider)?.label}
              {/if}
            </p>
          </div>
        {/if}
        
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
                    type={"color" as any}
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
          {:else if selectedChannelType === 'Channel::Sms'}
            {#if selectedProvider === 'twilio'}
              <!-- Twilio SMS Configuration -->
              <div class="space-y-4">
                <div>
                  <Label for="accountSid">Account SID <span class="text-red-500">*</span></Label>
                  <Input
                    id="accountSid"
                    bind:value={accountSid}
                    placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                    class={errors.accountSid ? 'border-red-500' : ''}
                  />
                  {#if errors.accountSid}
                    <p class="text-sm text-red-500 mt-1">{errors.accountSid}</p>
                  {/if}
                </div>
                
                <div class="flex items-center space-x-2">
                  <label for="useApiKey" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Use API Key instead of Auth Token</label>
                  <Switch bind:checked={useApiKey} id="useApiKey" />
                </div>
                
                {#if useApiKey}
                  <div>
                    <Label for="apiKeySid">API Key SID <span class="text-red-500">*</span></Label>
                    <Input
                      id="apiKeySid"
                      bind:value={apiKeySid}
                      placeholder="SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                      class={errors.apiKeySid ? 'border-red-500' : ''}
                    />
                    {#if errors.apiKeySid}
                      <p class="text-sm text-red-500 mt-1">{errors.apiKeySid}</p>
                    {/if}
                  </div>
                  
                  <div>
                    <Label for="apiKeySecret">API Key Secret <span class="text-red-500">*</span></Label>
                    <Input
                      id="apiKeySecret"
                      type="password"
                      bind:value={apiKeySecret}
                      placeholder="Your API Key Secret"
                      class={errors.apiKeySecret ? 'border-red-500' : ''}
                    />
                    {#if errors.apiKeySecret}
                      <p class="text-sm text-red-500 mt-1">{errors.apiKeySecret}</p>
                    {/if}
                  </div>
                {:else}
                  <div>
                    <Label for="authToken">Auth Token <span class="text-red-500">*</span></Label>
                    <Input
                      id="authToken"
                      type="password"
                      bind:value={authToken}
                      placeholder="Your Auth Token"
                      class={errors.authToken ? 'border-red-500' : ''}
                    />
                    {#if errors.authToken}
                      <p class="text-sm text-red-500 mt-1">{errors.authToken}</p>
                    {/if}
                  </div>
                {/if}
                
                <div class="flex items-center space-x-2">
                  <Switch bind:checked={useMessagingService} id="useMessagingService" />
                  <Label for="useMessagingService">Use Messaging Service SID</Label>
                </div>
                
                {#if useMessagingService}
                  <div>
                    <Label for="messagingServiceSid">Messaging Service SID <span class="text-red-500">*</span></Label>
                    <Input
                      id="messagingServiceSid"
                      bind:value={messagingServiceSid}
                      placeholder="MGxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                      class={errors.messagingServiceSid ? 'border-red-500' : ''}
                    />
                    {#if errors.messagingServiceSid}
                      <p class="text-sm text-red-500 mt-1">{errors.messagingServiceSid}</p>
                    {/if}
                  </div>
                {:else}
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
                    <p class="text-sm text-gray-600 mt-1">E.164 format</p>
                  </div>
                {/if}
              </div>
            {:else if selectedProvider === 'bandwidth'}
              <!-- Bandwidth SMS Configuration -->
              <div class="space-y-4">
                <div>
                  <Label for="bandwidthAccountId">Account ID <span class="text-red-500">*</span></Label>
                  <Input
                    id="bandwidthAccountId"
                    bind:value={bandwidthAccountId}
                    placeholder="Your Bandwidth Account ID"
                    class={errors.bandwidthAccountId ? 'border-red-500' : ''}
                  />
                  {#if errors.bandwidthAccountId}
                    <p class="text-sm text-red-500 mt-1">{errors.bandwidthAccountId}</p>
                  {/if}
                </div>
                
                <div>
                  <Label for="bandwidthUsername">Username <span class="text-red-500">*</span></Label>
                  <Input
                    id="bandwidthUsername"
                    bind:value={bandwidthUsername}
                    placeholder="Your Bandwidth Username"
                    class={errors.bandwidthUsername ? 'border-red-500' : ''}
                  />
                  {#if errors.bandwidthUsername}
                    <p class="text-sm text-red-500 mt-1">{errors.bandwidthUsername}</p>
                  {/if}
                </div>
                
                <div>
                  <Label for="bandwidthPassword">Password <span class="text-red-500">*</span></Label>
                  <Input
                    id="bandwidthPassword"
                    type="password"
                    bind:value={bandwidthPassword}
                    placeholder="Your Bandwidth Password"
                    class={errors.bandwidthPassword ? 'border-red-500' : ''}
                  />
                  {#if errors.bandwidthPassword}
                    <p class="text-sm text-red-500 mt-1">{errors.bandwidthPassword}</p>
                  {/if}
                </div>
                
                <div>
                  <Label for="bandwidthApplicationId">Application ID <span class="text-red-500">*</span></Label>
                  <Input
                    id="bandwidthApplicationId"
                    bind:value={bandwidthApplicationId}
                    placeholder="Your Bandwidth Application ID"
                    class={errors.bandwidthApplicationId ? 'border-red-500' : ''}
                  />
                  {#if errors.bandwidthApplicationId}
                    <p class="text-sm text-red-500 mt-1">{errors.bandwidthApplicationId}</p>
                  {/if}
                </div>
                
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
                  <p class="text-sm text-gray-600 mt-1">E.164 format</p>
                </div>
              </div>
            {/if}
          {:else if selectedChannelType === 'Channel::Whatsapp'}
            {#if selectedProvider === 'whatsapp_cloud'}
              <!-- WhatsApp Cloud Configuration -->
              <div class="space-y-4">
                <div>
                  <Label for="phoneNumberId">Phone Number ID <span class="text-red-500">*</span></Label>
                  <Input
                    id="phoneNumberId"
                    bind:value={phoneNumberId}
                    placeholder="123456789012345"
                    class={errors.phoneNumberId ? 'border-red-500' : ''}
                  />
                  {#if errors.phoneNumberId}
                    <p class="text-sm text-red-500 mt-1">{errors.phoneNumberId}</p>
                  {/if}
                </div>
                
                <div>
                  <Label for="businessAccountId">Business Account ID <span class="text-red-500">*</span></Label>
                  <Input
                    id="businessAccountId"
                    bind:value={businessAccountId}
                    placeholder="123456789012345"
                    class={errors.businessAccountId ? 'border-red-500' : ''}
                  />
                  {#if errors.businessAccountId}
                    <p class="text-sm text-red-500 mt-1">{errors.businessAccountId}</p>
                  {/if}
                </div>
                
                <div>
                  <Label for="accessToken">Access Token <span class="text-red-500">*</span></Label>
                  <Input
                    id="accessToken"
                    type="password"
                    bind:value={accessToken}
                    placeholder="Your WhatsApp Access Token"
                    class={errors.accessToken ? 'border-red-500' : ''}
                  />
                  {#if errors.accessToken}
                    <p class="text-sm text-red-500 mt-1">{errors.accessToken}</p>
                  {/if}
                </div>
              </div>
            {:else if selectedProvider === 'twilio'}
              <!-- Twilio WhatsApp Configuration -->
              <div class="space-y-4">
                <div>
                  <Label for="accountSid">Account SID <span class="text-red-500">*</span></Label>
                  <Input
                    id="accountSid"
                    bind:value={accountSid}
                    placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                    class={errors.accountSid ? 'border-red-500' : ''}
                  />
                  {#if errors.accountSid}
                    <p class="text-sm text-red-500 mt-1">{errors.accountSid}</p>
                  {/if}
                </div>
                
                <div class="flex items-center space-x-2">
                  <label for="useApiKey" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Use API Key instead of Auth Token</label>
                  <Switch bind:checked={useApiKey} id="useApiKey" />
                </div>
                
                {#if useApiKey}
                  <div>
                    <Label for="apiKeySid">API Key SID <span class="text-red-500">*</span></Label>
                    <Input
                      id="apiKeySid"
                      bind:value={apiKeySid}
                      placeholder="SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                      class={errors.apiKeySid ? 'border-red-500' : ''}
                    />
                    {#if errors.apiKeySid}
                      <p class="text-sm text-red-500 mt-1">{errors.apiKeySid}</p>
                    {/if}
                  </div>
                  
                  <div>
                    <Label for="apiKeySecret">API Key Secret <span class="text-red-500">*</span></Label>
                    <Input
                      id="apiKeySecret"
                      type="password"
                      bind:value={apiKeySecret}
                      placeholder="Your API Key Secret"
                      class={errors.apiKeySecret ? 'border-red-500' : ''}
                    />
                    {#if errors.apiKeySecret}
                      <p class="text-sm text-red-500 mt-1">{errors.apiKeySecret}</p>
                    {/if}
                  </div>
                {:else}
                  <div>
                    <Label for="authToken">Auth Token <span class="text-red-500">*</span></Label>
                    <Input
                      id="authToken"
                      type="password"
                      bind:value={authToken}
                      placeholder="Your Auth Token"
                      class={errors.authToken ? 'border-red-500' : ''}
                    />
                    {#if errors.authToken}
                      <p class="text-sm text-red-500 mt-1">{errors.authToken}</p>
                    {/if}
                  </div>
                {/if}
                
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
                  <p class="text-sm text-gray-600 mt-1">E.164 format</p>
                </div>
              </div>
            {:else if selectedProvider === '360dialog'}
              <!-- 360Dialog WhatsApp Configuration -->
              <div class="space-y-4">
                <div>
                  <Label for="dialog360ApiKey">API Key <span class="text-red-500">*</span></Label>
                  <Input
                    id="dialog360ApiKey"
                    type="password"
                    bind:value={dialog360ApiKey}
                    placeholder="Your 360Dialog API Key"
                    class={errors.dialog360ApiKey ? 'border-red-500' : ''}
                  />
                  {#if errors.dialog360ApiKey}
                    <p class="text-sm text-red-500 mt-1">{errors.dialog360ApiKey}</p>
                  {/if}
                </div>
                
                <div>
                  <Label for="dialog360PartnerId">Partner ID (Optional)</Label>
                  <Input
                    id="dialog360PartnerId"
                    bind:value={dialog360PartnerId}
                    placeholder="Your Partner ID"
                  />
                  <p class="text-sm text-gray-600 mt-1">Leave empty if not applicable</p>
                </div>
              </div>
            {/if}
          {:else if selectedChannelType === 'Channel::Voice'}
            <!-- Twilio Voice Configuration -->
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
                  Your Twilio phone number in E.164 format
                </p>
              </div>
              
              <div>
                <Label for="accountSid">Twilio Account SID <span class="text-red-500">*</span></Label>
                <Input
                  id="accountSid"
                  bind:value={accountSid}
                  placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                  class={errors.accountSid ? 'border-red-500' : ''}
                />
                {#if errors.accountSid}
                  <p class="text-sm text-red-500 mt-1">{errors.accountSid}</p>
                {/if}
              </div>
              
              <div class="flex items-center space-x-2">
                <label for="useApiKeyVoice" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">Use API Key instead of Auth Token</label>
                <Switch bind:checked={useApiKey} id="useApiKeyVoice" />
              </div>
              
              {#if useApiKey}
                <div>
                  <Label for="apiKeySid">Twilio API Key SID <span class="text-red-500">*</span></Label>
                  <Input
                    id="apiKeySid"
                    bind:value={apiKeySid}
                    placeholder="SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                    class={errors.apiKeySid ? 'border-red-500' : ''}
                  />
                  {#if errors.apiKeySid}
                    <p class="text-sm text-red-500 mt-1">{errors.apiKeySid}</p>
                  {/if}
                </div>
                
                <div>
                  <Label for="apiKeySecret">Twilio API Key Secret <span class="text-red-500">*</span></Label>
                  <Input
                    id="apiKeySecret"
                    type="password"
                    bind:value={apiKeySecret}
                    placeholder="Your Twilio API Key Secret"
                    class={errors.apiKeySecret ? 'border-red-500' : ''}
                  />
                  {#if errors.apiKeySecret}
                    <p class="text-sm text-red-500 mt-1">{errors.apiKeySecret}</p>
                  {/if}
                </div>
              {:else}
                <div>
                  <Label for="authToken">Twilio Auth Token <span class="text-red-500">*</span></Label>
                  <Input
                    id="authToken"
                    type="password"
                    bind:value={authToken}
                    placeholder="Your Twilio Auth Token"
                    class={errors.authToken ? 'border-red-500' : ''}
                  />
                  {#if errors.authToken}
                    <p class="text-sm text-red-500 mt-1">{errors.authToken}</p>
                  {/if}
                </div>
              {/if}
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
                <Select.Root bind:value={timezone}>
                  <Select.Trigger class="w-full">
                    <Select.Value placeholder="Select timezone" />
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
