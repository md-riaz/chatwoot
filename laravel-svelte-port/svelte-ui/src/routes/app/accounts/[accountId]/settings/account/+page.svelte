<script lang="ts">
  /**
   * Account Settings Page
   * Manage account-level settings like name, language, timezone
   */

  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import * as Select from '$lib/components/ui/select';
  import { authStore } from '$lib/stores/auth.svelte';
  import { onMount } from 'svelte';

  let accountName = $state('');
  let language = $state({ value: 'en' });
  let timezone = $state({ value: 'UTC' });
  let isSaving = $state(false);
  let successMessage = $state<string | null>(null);

  onMount(() => {
    // Load current account data
    if (authStore.currentAccount) {
      accountName = authStore.currentAccount.name || '';
    }
    // Try to load language and timezone from UI settings
    if (authStore.uiSettings) {
      language = { value: authStore.uiSettings.language || 'en' };
      timezone = { value: authStore.uiSettings.timezone || 'UTC' };
    }
  });

  async function handleSave() {
    isSaving = true;
    // Note: This is a placeholder implementation
    // Account settings API needs to be implemented on the backend
    try {
      // For now, just update UI settings
      await authStore.updateUISettings({
        ...authStore.uiSettings,
        language: language.value,
        timezone: timezone.value,
      });
      
      successMessage = 'Settings saved successfully!';
      setTimeout(() => {
        successMessage = null;
      }, 3000);
    } catch (error) {
      console.error('Error saving settings:', error);
    } finally {
      isSaving = false;
    }
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Account Settings</h1>
    <p class="text-muted-foreground mt-2">
      Manage your account preferences and general settings
    </p>
  </div>

  {#if successMessage}
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
      {successMessage}
    </div>
  {/if}

  <Card.Root>
    <Card.Header>
      <Card.Title>General Settings</Card.Title>
      <Card.Description>
        Update your account name, language, and timezone preferences
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="space-y-2">
        <Label for="account-name">Account Name</Label>
        <Input
          id="account-name"
          bind:value={accountName}
          placeholder="Enter account name"
        />
      </div>

      <div class="space-y-2">
        <Label for="language">Language</Label>
        <Select.Root bind:value={language}>
          <Select.Trigger id="language">
            <Select.Value placeholder="Select language" />
          </Select.Trigger>
          <Select.Content>
            <Select.Item value="en">English</Select.Item>
            <Select.Item value="es">Spanish</Select.Item>
            <Select.Item value="fr">French</Select.Item>
            <Select.Item value="de">German</Select.Item>
          </Select.Content>
        </Select.Root>
      </div>

      <div class="space-y-2">
        <Label for="timezone">Timezone</Label>
        <Select.Root bind:value={timezone}>
          <Select.Trigger id="timezone">
            <Select.Value placeholder="Select timezone" />
          </Select.Trigger>
          <Select.Content>
            <Select.Item value="UTC">UTC</Select.Item>
            <Select.Item value="America/New_York">Eastern Time</Select.Item>
            <Select.Item value="America/Chicago">Central Time</Select.Item>
            <Select.Item value="America/Los_Angeles">Pacific Time</Select.Item>
            <Select.Item value="Europe/London">London</Select.Item>
          </Select.Content>
        </Select.Root>
      </div>
    </Card.Content>
    <Card.Footer>
      <Button onclick={handleSave} disabled={isSaving}>
        {isSaving ? 'Saving...' : 'Save Changes'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
