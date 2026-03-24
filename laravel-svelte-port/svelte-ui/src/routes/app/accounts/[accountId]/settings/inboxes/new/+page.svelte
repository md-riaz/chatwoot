<script lang="ts">
  /**
   * Inbox Creation Wizard
   * Multi-step wizard for creating different types of inboxes
   */

  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { ColorPicker } from '$lib/components/ui/color-picker';
  import { Textarea } from '$lib/components/ui/textarea';
  import * as Select from '$lib/components/ui/select';
  import { Switch } from '$lib/components/ui/switch';
  import {
    Globe,
    Plug,
    Mail,
    Phone,
    MessageCircle,
    MessageSquare,
    Send,
    Hash,
    Instagram,
    Video,
    ArrowLeft,
    Check,
  } from 'lucide-svelte';
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

  // Timezone display label
  const timezoneOptions = [
    { value: 'UTC', label: 'UTC' },
    { value: 'America/New_York', label: 'Eastern Time (ET)' },
    { value: 'America/Chicago', label: 'Central Time (CT)' },
    { value: 'America/Denver', label: 'Mountain Time (MT)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (PT)' },
    { value: 'Europe/London', label: 'London (GMT)' },
    { value: 'Europe/Paris', label: 'Central European Time (CET)' },
    { value: 'Asia/Tokyo', label: 'Tokyo (JST)' },
    { value: 'Asia/Shanghai', label: 'China (CST)' },
    { value: 'Australia/Sydney', label: 'Sydney (AEST)' },
  ];

  const timezoneLabel = $derived(
    timezoneOptions.find(tz => tz.value === timezone)?.label ||
      'Select timezone'
  );

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

  // Errors
  let errors = $state<Record<string, string>>({});

  const channelTypes = [
    {
      type: 'Channel::WebWidget',
      name: 'Website',
      icon: Globe,
      description: 'Live chat widget for your website',
    },
    {
      type: 'Channel::Instagram',
      name: 'Instagram',
      icon: Instagram,
      description: 'Direct messages from an Instagram professional account',
    },
    {
      type: 'Channel::FacebookPage',
      name: 'Facebook Page',
      icon: MessageCircle,
      description: 'Messenger conversations from a Facebook page',
    },
    {
      type: 'Channel::Api',
      name: 'API',
      icon: Plug,
      description: 'Custom channel using API',
    },
    {
      type: 'Channel::Email',
      name: 'Email',
      icon: Mail,
      description: 'Support via email',
    },
    {
      type: 'Channel::Whatsapp',
      name: 'WhatsApp',
      icon: Phone,
      description: 'WhatsApp Business API',
    },
    {
      type: 'Channel::Sms',
      name: 'SMS',
      icon: MessageSquare,
      description: 'SMS channel',
    },
    {
      type: 'Channel::Voice',
      name: 'Voice',
      icon: Phone,
      description: 'Voice calls via Twilio',
    },
  ];

  // Provider options
  const smsProviders = [
    { value: 'twilio', label: 'Twilio' },
    { value: 'bandwidth', label: 'Bandwidth' },
  ];

  function needsProviderSelection(channelType: string): boolean {
    return channelType === 'Channel::Sms';
  }

  function handleChannelSelect(channelType: string) {
    if (channelType === 'Channel::WebWidget') {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/website`);
      return;
    }

    if (channelType === 'Channel::Api') {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/api`);
      return;
    }

    if (channelType === 'Channel::FacebookPage') {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/facebook`);
      return;
    }

    if (channelType === 'Channel::Instagram') {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/instagram`);
      return;
    }

    if (channelType === 'Channel::Email') {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/email`);
      return;
    }

    if (channelType === 'Channel::Whatsapp') {
      goto(`/app/accounts/${accountId}/settings/inboxes/new/whatsapp`);
      return;
    }

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
    } else if (
      selectedChannelType === 'Channel::Email' &&
      emailAddress.trim()
    ) {
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
          if (!apiKeySecret.trim())
            errors.apiKeySecret = 'API Key Secret is required';
        } else {
          if (!authToken.trim()) errors.authToken = 'Auth Token is required';
        }
        if (useMessagingService) {
          if (!messagingServiceSid.trim())
            errors.messagingServiceSid = 'Messaging Service SID is required';
        } else {
          if (!phoneNumber.trim())
            errors.phoneNumber = 'Phone number is required';
        }
      } else if (selectedProvider === 'bandwidth') {
        if (!bandwidthAccountId.trim())
          errors.bandwidthAccountId = 'Account ID is required';
        if (!bandwidthUsername.trim())
          errors.bandwidthUsername = 'Username is required';
        if (!bandwidthPassword.trim())
          errors.bandwidthPassword = 'Password is required';
        if (!bandwidthApplicationId.trim())
          errors.bandwidthApplicationId = 'Application ID is required';
        if (!phoneNumber.trim())
          errors.phoneNumber = 'Phone number is required';
      }
    }

    // Voice validation
    if (selectedChannelType === 'Channel::Voice') {
      if (!phoneNumber.trim()) errors.phoneNumber = 'Phone number is required';
      if (!accountSid.trim()) errors.accountSid = 'Account SID is required';
      if (useApiKey) {
        if (!apiKeySid.trim()) errors.apiKeySid = 'API Key SID is required';
        if (!apiKeySecret.trim())
          errors.apiKeySecret = 'API Key Secret is required';
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
          ...(useApiKey
            ? {
                api_key_sid: apiKeySid,
                api_key_secret: apiKeySecret,
              }
            : {
                auth_token: authToken,
              }),
          ...(useMessagingService
            ? {
                messaging_service_sid: messagingServiceSid,
              }
            : {
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
    } else if (selectedChannelType === 'Channel::Voice') {
      channelData.phone_number = phoneNumber;
      channelData.provider = 'twilio';
      channelData.provider_config = {
        account_sid: accountSid,
        ...(useApiKey
          ? {
              api_key_sid: apiKeySid,
              api_key_secret: apiKeySecret,
            }
          : {
              auth_token: authToken,
            }),
      };
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
      goto(`/app/accounts/${accountId}/settings/inboxes/new/${inbox.id}/agents`);
    }
  }
</script>

<div class="space-y-6">
  <div class="flex items-center gap-4">
    <Button variant="ghost" onclick={handleBack}>
      <ArrowLeft class="mr-1 h-4 w-4" /> Back
    </Button>
    <div>
      <h1 class="text-xl font-medium tracking-tight text-foreground">
        Create Inbox
      </h1>
      <p class="text-sm text-muted-foreground mt-1">
        {#if currentStep === 1}
          Choose a supported channel type to get started
        {:else if currentStep === 2}
          Select a provider for {channelTypes.find(
            c => c.type === selectedChannelType
          )?.name}
        {:else if currentStep === 3}
          Configure your {channelTypes.find(c => c.type === selectedChannelType)
            ?.name} inbox
        {/if}
      </p>
    </div>
  </div>

  <!-- Progress Steps -->
  <div class="flex items-center gap-2">
    <div class="flex items-center gap-2">
      <div
        class="flex h-8 w-8 items-center justify-center rounded-full {currentStep ===
        1
          ? 'bg-primary text-primary-foreground'
          : currentStep > 1
            ? 'bg-primary/80 text-primary-foreground'
            : 'bg-muted'}"
      >
        {#if currentStep > 1}<Check class="h-4 w-4" />{:else}1{/if}
      </div>
      <span
        class="text-sm {currentStep === 1
          ? 'font-semibold text-foreground'
          : 'text-muted-foreground'}">Select Channel</span
      >
    </div>
    {#if needsProviderSelection(selectedChannelType)}
      <div class="h-px w-12 bg-border"></div>
      <div class="flex items-center gap-2">
        <div
          class="flex h-8 w-8 items-center justify-center rounded-full {currentStep ===
          2
            ? 'bg-primary text-primary-foreground'
            : currentStep > 2
              ? 'bg-primary/80 text-primary-foreground'
              : 'bg-muted'}"
        >
          {#if currentStep > 2}<Check class="h-4 w-4" />{:else}2{/if}
        </div>
        <span
          class="text-sm {currentStep === 2
            ? 'font-semibold text-foreground'
            : 'text-muted-foreground'}">Select Provider</span
        >
      </div>
    {/if}
    <div class="h-px w-12 bg-border"></div>
    <div class="flex items-center gap-2">
      <div
        class="flex h-8 w-8 items-center justify-center rounded-full {currentStep ===
          3 ||
        (currentStep === 2 && !needsProviderSelection(selectedChannelType))
          ? 'bg-primary text-primary-foreground'
          : 'bg-muted'}"
      >
        {needsProviderSelection(selectedChannelType) ? '3' : '2'}
      </div>
      <span
        class="text-sm {currentStep === 3 ||
        (currentStep === 2 && !needsProviderSelection(selectedChannelType))
          ? 'font-semibold text-foreground'
          : 'text-muted-foreground'}">Configure</span
      >
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
              <div
                class="flex h-12 w-12 items-center justify-center rounded-lg bg-muted"
              >
                {#each [channel.icon] as IconComponent}
                  <IconComponent class="h-6 w-6 text-muted-foreground" />
                {/each}
              </div>
              <h3 class="font-semibold text-lg">{channel.name}</h3>
              <p class="text-sm text-muted-foreground">{channel.description}</p>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {:else if currentStep === 2 && needsProviderSelection(selectedChannelType)}
    <!-- Step 2: Provider Selection (for SMS) -->
    <div>
      <h2 class="text-2xl font-bold mb-4">Select Provider</h2>
      <p class="text-muted-foreground mb-6">
        Choose a provider for your {channelTypes.find(
          c => c.type === selectedChannelType
        )?.name} channel
      </p>

      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        {#if selectedChannelType === 'Channel::Sms'}
          {#each smsProviders as provider}
            <Card.Root
              class="hover:shadow-md transition-shadow cursor-pointer hover:border-blue-500"
              onclick={() => handleProviderSelect(provider.value)}
            >
              <Card.Content class="p-6 text-center">
                <div
                  class="flex h-10 w-10 mx-auto mb-3 items-center justify-center rounded-lg bg-muted"
                >
                  <MessageSquare class="h-5 w-5 text-muted-foreground" />
                </div>
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
              {/if}
            </p>
          </div>
        {/if}

        <!-- Basic Configuration -->
        <div>
          <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

          <div class="space-y-4">
            <div>
              <Label for="inboxName"
                >Inbox Name <span class="text-red-500">*</span></Label
              >
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
                <Label for="websiteUrl"
                  >Website URL <span class="text-red-500">*</span></Label
                >
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
                <ColorPicker
                  id="widgetColor"
                  bind:value={widgetColor}
                  colorInputClass="w-20"
                />
              </div>
            </div>
          {:else if selectedChannelType === 'Channel::Email'}
            <div class="space-y-4">
              <div>
                <Label for="emailAddress"
                  >Email Address <span class="text-red-500">*</span></Label
                >
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
                <p class="text-sm text-muted-foreground mt-1">
                  Email forwarding will be configured after creation
                </p>
              </div>
            </div>
          {:else if selectedChannelType === 'Channel::Sms'}
            {#if selectedProvider === 'twilio'}
              <!-- Twilio SMS Configuration -->
              <div class="space-y-4">
                <div>
                  <Label for="accountSid"
                    >Account SID <span class="text-red-500">*</span></Label
                  >
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
                  <label
                    for="useApiKey"
                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                    >Use API Key instead of Auth Token</label
                  >
                  <Switch bind:checked={useApiKey} id="useApiKey" />
                </div>

                {#if useApiKey}
                  <div>
                    <Label for="apiKeySid"
                      >API Key SID <span class="text-red-500">*</span></Label
                    >
                    <Input
                      id="apiKeySid"
                      bind:value={apiKeySid}
                      placeholder="SKxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                      class={errors.apiKeySid ? 'border-red-500' : ''}
                    />
                    {#if errors.apiKeySid}
                      <p class="text-sm text-red-500 mt-1">
                        {errors.apiKeySid}
                      </p>
                    {/if}
                  </div>

                  <div>
                    <Label for="apiKeySecret"
                      >API Key Secret <span class="text-red-500">*</span></Label
                    >
                    <Input
                      id="apiKeySecret"
                      type="password"
                      bind:value={apiKeySecret}
                      placeholder="Your API Key Secret"
                      class={errors.apiKeySecret ? 'border-red-500' : ''}
                    />
                    {#if errors.apiKeySecret}
                      <p class="text-sm text-red-500 mt-1">
                        {errors.apiKeySecret}
                      </p>
                    {/if}
                  </div>
                {:else}
                  <div>
                    <Label for="authToken"
                      >Auth Token <span class="text-red-500">*</span></Label
                    >
                    <Input
                      id="authToken"
                      type="password"
                      bind:value={authToken}
                      placeholder="Your Auth Token"
                      class={errors.authToken ? 'border-red-500' : ''}
                    />
                    {#if errors.authToken}
                      <p class="text-sm text-red-500 mt-1">
                        {errors.authToken}
                      </p>
                    {/if}
                  </div>
                {/if}

                <div class="flex items-center space-x-2">
                  <Switch
                    bind:checked={useMessagingService}
                    id="useMessagingService"
                  />
                  <Label for="useMessagingService"
                    >Use Messaging Service SID</Label
                  >
                </div>

                {#if useMessagingService}
                  <div>
                    <Label for="messagingServiceSid"
                      >Messaging Service SID <span class="text-red-500">*</span
                      ></Label
                    >
                    <Input
                      id="messagingServiceSid"
                      bind:value={messagingServiceSid}
                      placeholder="MGxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                      class={errors.messagingServiceSid ? 'border-red-500' : ''}
                    />
                    {#if errors.messagingServiceSid}
                      <p class="text-sm text-red-500 mt-1">
                        {errors.messagingServiceSid}
                      </p>
                    {/if}
                  </div>
                {:else}
                  <div>
                    <Label for="phoneNumber"
                      >Phone Number <span class="text-red-500">*</span></Label
                    >
                    <Input
                      id="phoneNumber"
                      type="tel"
                      bind:value={phoneNumber}
                      placeholder="+1234567890"
                      class={errors.phoneNumber ? 'border-red-500' : ''}
                    />
                    {#if errors.phoneNumber}
                      <p class="text-sm text-red-500 mt-1">
                        {errors.phoneNumber}
                      </p>
                    {/if}
                    <p class="text-sm text-muted-foreground mt-1">
                      E.164 format
                    </p>
                  </div>
                {/if}
              </div>
            {:else if selectedProvider === 'bandwidth'}
              <!-- Bandwidth SMS Configuration -->
              <div class="space-y-4">
                <div>
                  <Label for="bandwidthAccountId"
                    >Account ID <span class="text-red-500">*</span></Label
                  >
                  <Input
                    id="bandwidthAccountId"
                    bind:value={bandwidthAccountId}
                    placeholder="Your Bandwidth Account ID"
                    class={errors.bandwidthAccountId ? 'border-red-500' : ''}
                  />
                  {#if errors.bandwidthAccountId}
                    <p class="text-sm text-red-500 mt-1">
                      {errors.bandwidthAccountId}
                    </p>
                  {/if}
                </div>

                <div>
                  <Label for="bandwidthUsername"
                    >Username <span class="text-red-500">*</span></Label
                  >
                  <Input
                    id="bandwidthUsername"
                    bind:value={bandwidthUsername}
                    placeholder="Your Bandwidth Username"
                    class={errors.bandwidthUsername ? 'border-red-500' : ''}
                  />
                  {#if errors.bandwidthUsername}
                    <p class="text-sm text-red-500 mt-1">
                      {errors.bandwidthUsername}
                    </p>
                  {/if}
                </div>

                <div>
                  <Label for="bandwidthPassword"
                    >Password <span class="text-red-500">*</span></Label
                  >
                  <Input
                    id="bandwidthPassword"
                    type="password"
                    bind:value={bandwidthPassword}
                    placeholder="Your Bandwidth Password"
                    class={errors.bandwidthPassword ? 'border-red-500' : ''}
                  />
                  {#if errors.bandwidthPassword}
                    <p class="text-sm text-red-500 mt-1">
                      {errors.bandwidthPassword}
                    </p>
                  {/if}
                </div>

                <div>
                  <Label for="bandwidthApplicationId"
                    >Application ID <span class="text-red-500">*</span></Label
                  >
                  <Input
                    id="bandwidthApplicationId"
                    bind:value={bandwidthApplicationId}
                    placeholder="Your Bandwidth Application ID"
                    class={errors.bandwidthApplicationId
                      ? 'border-red-500'
                      : ''}
                  />
                  {#if errors.bandwidthApplicationId}
                    <p class="text-sm text-red-500 mt-1">
                      {errors.bandwidthApplicationId}
                    </p>
                  {/if}
                </div>

                <div>
                  <Label for="phoneNumber"
                    >Phone Number <span class="text-red-500">*</span></Label
                  >
                  <Input
                    id="phoneNumber"
                    type="tel"
                    bind:value={phoneNumber}
                    placeholder="+1234567890"
                    class={errors.phoneNumber ? 'border-red-500' : ''}
                  />
                  {#if errors.phoneNumber}
                    <p class="text-sm text-red-500 mt-1">
                      {errors.phoneNumber}
                    </p>
                  {/if}
                  <p class="text-sm text-muted-foreground mt-1">E.164 format</p>
                </div>
              </div>
            {/if}
          {:else if selectedChannelType === 'Channel::Voice'}
            <!-- Twilio Voice Configuration -->
            <div class="space-y-4">
              <div>
                <Label for="phoneNumber"
                  >Phone Number <span class="text-red-500">*</span></Label
                >
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
                <p class="text-sm text-muted-foreground mt-1">
                  Your Twilio phone number in E.164 format
                </p>
              </div>

              <div>
                <Label for="accountSid"
                  >Twilio Account SID <span class="text-red-500">*</span></Label
                >
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
                <label
                  for="useApiKeyVoice"
                  class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                  >Use API Key instead of Auth Token</label
                >
                <Switch bind:checked={useApiKey} id="useApiKeyVoice" />
              </div>

              {#if useApiKey}
                <div>
                  <Label for="apiKeySid"
                    >Twilio API Key SID <span class="text-red-500">*</span
                    ></Label
                  >
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
                  <Label for="apiKeySecret"
                    >Twilio API Key Secret <span class="text-red-500">*</span
                    ></Label
                  >
                  <Input
                    id="apiKeySecret"
                    type="password"
                    bind:value={apiKeySecret}
                    placeholder="Your Twilio API Key Secret"
                    class={errors.apiKeySecret ? 'border-red-500' : ''}
                  />
                  {#if errors.apiKeySecret}
                    <p class="text-sm text-red-500 mt-1">
                      {errors.apiKeySecret}
                    </p>
                  {/if}
                </div>
              {:else}
                <div>
                  <Label for="authToken"
                    >Twilio Auth Token <span class="text-red-500">*</span
                    ></Label
                  >
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
                <p class="text-sm text-muted-foreground">
                  API channel allows you to integrate custom messaging
                  platforms. API credentials will be generated after creation.
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
                <p class="text-sm text-muted-foreground">
                  Show a greeting when conversation starts
                </p>
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
                <p class="text-sm text-muted-foreground">
                  Automatically assign conversations to agents
                </p>
              </div>
              <Switch bind:checked={enableAutoAssignment} />
            </div>

            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label>Working Hours</Label>
                <p class="text-sm text-muted-foreground">
                  Enable working hours for this inbox
                </p>
              </div>
              <Switch bind:checked={workingHoursEnabled} />
            </div>

            {#if workingHoursEnabled}
              <div>
                <Label for="timezone">Timezone</Label>
                <Select.Root type="single" bind:value={timezone}>
                  <Select.Trigger class="w-full">
                    {timezoneLabel}
                  </Select.Trigger>
                  <Select.Content>
                    <Select.Item value="UTC" label="UTC">UTC</Select.Item>
                    <Select.Item
                      value="America/New_York"
                      label="Eastern Time (ET)">Eastern Time (ET)</Select.Item
                    >
                    <Select.Item
                      value="America/Chicago"
                      label="Central Time (CT)">Central Time (CT)</Select.Item
                    >
                    <Select.Item
                      value="America/Denver"
                      label="Mountain Time (MT)">Mountain Time (MT)</Select.Item
                    >
                    <Select.Item
                      value="America/Los_Angeles"
                      label="Pacific Time (PT)">Pacific Time (PT)</Select.Item
                    >
                    <Select.Item value="Europe/London" label="London (GMT)"
                      >London (GMT)</Select.Item
                    >
                    <Select.Item
                      value="Europe/Paris"
                      label="Central European Time (CET)"
                      >Central European Time (CET)</Select.Item
                    >
                    <Select.Item value="Asia/Tokyo" label="Tokyo (JST)"
                      >Tokyo (JST)</Select.Item
                    >
                    <Select.Item value="Asia/Shanghai" label="China (CST)"
                      >China (CST)</Select.Item
                    >
                    <Select.Item value="Australia/Sydney" label="Sydney (AEST)"
                      >Sydney (AEST)</Select.Item
                    >
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
