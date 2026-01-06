<script lang="ts">
  /**
   * Account Settings Page
   * Manage account-level settings like name, language, timezone
   */

  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { authStore } from '$lib/stores/auth.svelte';
  import { onMount } from 'svelte';

  let accountName = $state('');
  let language = $state('en');
  let timezone = $state('UTC');
  let isSaving = $state(false);
  let successMessage = $state<string | null>(null);

  onMount(() => {
    // Load current account data
    if (authStore.currentAccount) {
      accountName = authStore.currentAccount.name || '';
    }
    // Try to load language and timezone from UI settings
    if (authStore.uiSettings) {
      language = authStore.uiSettings.language || 'en';
      timezone = authStore.uiSettings.timezone || 'UTC';
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
        language,
        timezone,
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
        <select
          id="language"
          bind:value={language}
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
        >
          <option value="en">English</option>
          <option value="es">Spanish</option>
          <option value="fr">French</option>
          <option value="de">German</option>
        </select>
      </div>

      <div class="space-y-2">
        <Label for="timezone">Timezone</Label>
        <select
          id="timezone"
          bind:value={timezone}
          class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
        >
          <option value="UTC">UTC</option>
          <option value="America/New_York">Eastern Time</option>
          <option value="America/Chicago">Central Time</option>
          <option value="America/Los_Angeles">Pacific Time</option>
          <option value="Europe/London">London</option>
        </select>
      </div>
    </Card.Content>
    <Card.Footer>
      <Button onclick={handleSave} disabled={isSaving}>
        {isSaving ? 'Saving...' : 'Save Changes'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
