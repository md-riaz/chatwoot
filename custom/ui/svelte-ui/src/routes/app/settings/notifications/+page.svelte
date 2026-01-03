<script lang="ts">
  /**
   * Notifications Settings Page
   * Manage notification preferences
   */

  import { onMount } from 'svelte';
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Switch } from '$lib/components/ui/switch';
  import { Label } from '$lib/components/ui/label';
  import { authStore } from '$lib/stores/auth.svelte';

  let emailNotifications = $state({
    newMessages: true,
    mentions: true,
    assignments: true,
    updates: false,
  });

  let pushNotifications = $state({
    enabled: true,
    sound: true,
    desktop: true,
  });

  let isSaving = $state(false);
  let successMessage = $state<string | null>(null);

  onMount(() => {
    // Load notification preferences from UI settings
    const uiSettings = authStore.uiSettings;
    if (uiSettings?.notificationPreferences) {
      const prefs = uiSettings.notificationPreferences;
      if (prefs.email) {
        emailNotifications = { ...emailNotifications, ...prefs.email };
      }
      if (prefs.push) {
        pushNotifications = { ...pushNotifications, ...prefs.push };
      }
    }
  });

  async function handleSave() {
    try {
      isSaving = true;

      // Save notification preferences to UI settings
      await authStore.updateUISettings({
        ...authStore.uiSettings,
        notificationPreferences: {
          email: emailNotifications,
          push: pushNotifications,
        },
      });

      successMessage = 'Notification preferences saved successfully!';
      setTimeout(() => {
        successMessage = null;
      }, 3000);
    } catch (error) {
      console.error('Error saving notification preferences:', error);
    } finally {
      isSaving = false;
    }
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Notification Preferences</h1>
    <p class="text-muted-foreground mt-2">
      Choose how you want to be notified about updates
    </p>
  </div>

  {#if successMessage}
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
      {successMessage}
    </div>
  {/if}

  <Card.Root>
    <Card.Header>
      <Card.Title>Email Notifications</Card.Title>
      <Card.Description>
        Receive email notifications for important events
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label for="email-new-messages">New Messages</Label>
          <p class="text-sm text-muted-foreground">
            Get notified when you receive new messages
          </p>
        </div>
        <Switch
          id="email-new-messages"
          bind:checked={emailNotifications.newMessages}
        />
      </div>

      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label for="email-mentions">Mentions</Label>
          <p class="text-sm text-muted-foreground">
            Get notified when someone mentions you
          </p>
        </div>
        <Switch
          id="email-mentions"
          bind:checked={emailNotifications.mentions}
        />
      </div>

      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label for="email-assignments">Assignments</Label>
          <p class="text-sm text-muted-foreground">
            Get notified about new conversation assignments
          </p>
        </div>
        <Switch
          id="email-assignments"
          bind:checked={emailNotifications.assignments}
        />
      </div>

      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label for="email-updates">Product Updates</Label>
          <p class="text-sm text-muted-foreground">
            Receive updates about new features and improvements
          </p>
        </div>
        <Switch id="email-updates" bind:checked={emailNotifications.updates} />
      </div>
    </Card.Content>
  </Card.Root>

  <Card.Root>
    <Card.Header>
      <Card.Title>Push Notifications</Card.Title>
      <Card.Description>
        Receive push notifications in your browser
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label for="push-enabled">Enable Push Notifications</Label>
          <p class="text-sm text-muted-foreground">
            Allow browser push notifications
          </p>
        </div>
        <Switch id="push-enabled" bind:checked={pushNotifications.enabled} />
      </div>

      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label for="push-sound">Notification Sound</Label>
          <p class="text-sm text-muted-foreground">Play sound for notifications</p>
        </div>
        <Switch id="push-sound" bind:checked={pushNotifications.sound} />
      </div>

      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <Label for="push-desktop">Desktop Notifications</Label>
          <p class="text-sm text-muted-foreground">
            Show desktop notifications when app is in background
          </p>
        </div>
        <Switch id="push-desktop" bind:checked={pushNotifications.desktop} />
      </div>
    </Card.Content>
  </Card.Root>

  <div class="flex justify-end">
    <Button onclick={handleSave} disabled={isSaving}>
      {isSaving ? 'Saving...' : 'Save Preferences'}
    </Button>
  </div>
</div>
