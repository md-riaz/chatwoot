<script lang="ts">
  import { onMount } from 'svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { globalConfig } from '$lib/stores/globalConfig.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import * as Select from '$lib/components/ui/select';
  import { toast } from "svelte-sonner";
  import { _ } from '$lib/i18n';
  
  import SectionLayout from './components/SectionLayout.svelte';
  import AccountId from './components/AccountId.svelte';
  import BuildInfo from './components/BuildInfo.svelte';
  import AccountDelete from './components/AccountDelete.svelte';
  import AutoResolve from './components/AutoResolve.svelte';
  import AudioTranscription from './components/AudioTranscription.svelte';

  // State
  let name = $state('');
  let locale = $state('en');
  let domain = $state('');
  let supportEmail = $state('');
  let isUpdating = $state(false);

  // Derived
  let currentAccount = $derived(authStore.currentAccount);
  let uiSettings = $derived(authStore.uiSettings);
  let accountId = $derived(authStore.currentAccountId);
  
  // Feature Flags
  let showAutoResolutionConfig = $derived(authStore.isFeatureEnabled('auto_resolve_conversations'));
  let showAudioTranscriptionConfig = $derived(authStore.isFeatureEnabled('captain_integration'));
  let featureInboundEmailEnabled = $derived(authStore.isFeatureEnabled('inbound_emails'));
  let featureCustomReplyDomainEnabled = $derived(featureInboundEmailEnabled && authStore.isFeatureEnabled('custom_reply_domain'));
  let featureCustomReplyEmailEnabled = $derived(featureInboundEmailEnabled && authStore.isFeatureEnabled('custom_reply_email'));
  let isOnChatwootCloud = $derived(globalConfig.get('isOnChatwootCloud'));

  // Languages
  let enabledLanguages = $derived(globalConfig.get('enabledLanguages') || [
    { iso_639_1_code: 'en', name: 'English' }
  ]);
  let languagesSortedByCode = $derived([...enabledLanguages].sort((l1, l2) => 
    l1.iso_639_1_code.localeCompare(l2.iso_639_1_code)
  ));
  
  // Language display label
  const localeLabel = $derived(
    languagesSortedByCode.find(lang => lang.iso_639_1_code === locale)?.name || 'Select language'
  );

  // Initialize data
  $effect(() => {
    if (currentAccount) {
      name = currentAccount.name || '';
      locale = currentAccount.locale || 'en';
      domain = currentAccount.domain || '';
      supportEmail = currentAccount.supportEmail || '';
      
      // Override with UI settings locale if available (matching Vue logic)
      if (uiSettings?.locale) {
        // In Vue it sets this.$root.$i18n.locale, but here we just ensure the form reflects the account locale?
        // Vue logic: this.locale = locale (account locale).
        // Then initializeAccount sets: this.$root.$i18n.locale = this.uiSettings?.locale || locale;
        // But the form binds to `this.locale`.
      }
    }
  });

  async function updateAccount() {
    if (!name) {
      toast.error($_('GENERAL_SETTINGS.FORM.NAME.ERROR'));
      return;
    }
    if (!locale) {
      toast.error($_('GENERAL_SETTINGS.FORM.LANGUAGE.ERROR'));
      return;
    }

    isUpdating = true;
    try {
      await authStore.updateAccount({
        name,
        locale,
        domain,
        supportEmail: supportEmail
      });
      
      toast.success($_('GENERAL_SETTINGS.UPDATE.SUCCESS'));
    } catch (error) {
      toast.error($_('GENERAL_SETTINGS.UPDATE.ERROR'));
    } finally {
      isUpdating = false;
    }
  }
</script>

<div class="flex flex-col max-w-4xl mx-auto w-full p-6">
  <header class="mb-6">
    <h1 class="text-3xl font-bold tracking-tight text-foreground">
      {$_('GENERAL_SETTINGS.TITLE')}
    </h1>
  </header>

  <div class="flex-grow flex-shrink min-w-0 space-y-6">
    <!-- General Settings Section -->
    <SectionLayout
      title={$_('GENERAL_SETTINGS.FORM.GENERAL_SECTION.TITLE')}
      description={$_('GENERAL_SETTINGS.FORM.GENERAL_SECTION.NOTE')}
    >
      <form class="grid gap-6 max-w-2xl" onsubmit={(e) => { e.preventDefault(); updateAccount(); }}>
        
        <!-- Name -->
        <div class="grid gap-2">
          <Label for="account-name">{$_('GENERAL_SETTINGS.FORM.NAME.LABEL')}</Label>
          <Input 
            id="account-name" 
            bind:value={name} 
            placeholder={$_('GENERAL_SETTINGS.FORM.NAME.PLACEHOLDER')}
          />
        </div>

        <!-- Language -->
        <div class="grid gap-2">
          <Label for="account-locale">{$_('GENERAL_SETTINGS.FORM.LANGUAGE.LABEL')}</Label>
          <Select.Root type="single" bind:value={locale}>
            <Select.Trigger id="account-locale">
              {localeLabel}
            </Select.Trigger>
            <Select.Content class="max-h-[300px]">
              {#each languagesSortedByCode as lang}
                <Select.Item value={lang.iso_639_1_code} label={lang.name}>
                  {lang.name}
                </Select.Item>
              {/each}
            </Select.Content>
          </Select.Root>
        </div>

        <!-- Domain -->
        {#if featureCustomReplyDomainEnabled}
          <div class="grid gap-2">
            <Label for="account-domain">{$_('GENERAL_SETTINGS.FORM.DOMAIN.LABEL')}</Label>
            <Input 
              id="account-domain" 
              bind:value={domain} 
              placeholder={$_('GENERAL_SETTINGS.FORM.DOMAIN.PLACEHOLDER')}
            />
            <p class="text-sm text-muted-foreground">
              {#if featureInboundEmailEnabled}
                {$_('GENERAL_SETTINGS.FORM.FEATURES.INBOUND_EMAIL_ENABLED')}
              {/if}
              {#if featureCustomReplyDomainEnabled}
                {$_('GENERAL_SETTINGS.FORM.FEATURES.CUSTOM_EMAIL_DOMAIN_ENABLED')}
              {/if}
            </p>
          </div>
        {/if}

        <!-- Support Email -->
        {#if featureCustomReplyEmailEnabled}
          <div class="grid gap-2">
            <Label for="account-email">{$_('GENERAL_SETTINGS.FORM.SUPPORT_EMAIL.LABEL')}</Label>
            <Input 
              id="account-email" 
              bind:value={supportEmail} 
              placeholder={$_('GENERAL_SETTINGS.FORM.SUPPORT_EMAIL.PLACEHOLDER')}
            />
          </div>
        {/if}

        <div class="pt-2">
          <Button type="submit" disabled={isUpdating}>
            {#if isUpdating}
              Processing...
            {:else}
              {$_('GENERAL_SETTINGS.SUBMIT')}
            {/if}
          </Button>
        </div>
      </form>
    </SectionLayout>

    <!-- Auto Resolve -->
    {#if showAutoResolutionConfig}
      <AutoResolve />
    {/if}

    <!-- Audio Transcription -->
    {#if showAudioTranscriptionConfig}
      <AudioTranscription />
    {/if}

    <!-- Account ID -->
    <AccountId />

    <!-- Account Delete -->
    {#if isOnChatwootCloud}
      <AccountDelete />
    {/if}

    <!-- Build Info -->
    <BuildInfo />
  </div>
</div>
