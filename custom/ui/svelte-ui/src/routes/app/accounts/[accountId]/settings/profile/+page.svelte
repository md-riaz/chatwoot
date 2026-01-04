<script lang="ts">
  /**
   * Profile Settings Page
   * Manage user profile information
   */

  import { onMount } from 'svelte';
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { authStore } from '$lib/stores/auth.svelte';
  import * as authAPI from '$lib/api/auth';

  let name = $state('');
  let email = $state('');
  let currentPassword = $state('');
  let newPassword = $state('');
  let confirmPassword = $state('');
  let isSaving = $state(false);
  let isChangingPassword = $state(false);
  let error = $state<string | null>(null);
  let successMessage = $state<string | null>(null);

  onMount(() => {
    // Load current user data
    if (authStore.currentUser) {
      name = authStore.currentUser.displayName || authStore.currentUser.name || '';
      email = authStore.currentUser.email || '';
    }
  });

  async function handleSave() {
    try {
      isSaving = true;
      error = null;
      successMessage = null;

      await authAPI.updateProfile({
        displayName: name,
      });

      // Update the auth store
      await authStore.validityCheck();
      
      successMessage = 'Profile updated successfully!';
    } catch (err: any) {
      error = err.message || 'Failed to update profile';
    } finally {
      isSaving = false;
      // Clear success message after 3 seconds
      if (successMessage) {
        setTimeout(() => {
          successMessage = null;
        }, 3000);
      }
    }
  }

  async function handlePasswordChange() {
    if (newPassword !== confirmPassword) {
      error = 'Passwords do not match!';
      return;
    }

    if (newPassword.length < 6) {
      error = 'Password must be at least 6 characters long';
      return;
    }

    try {
      isChangingPassword = true;
      error = null;
      successMessage = null;

      await authAPI.updatePassword({
        currentPassword,
        password: newPassword,
        passwordConfirmation: confirmPassword,
      });

      // Clear password fields
      currentPassword = '';
      newPassword = '';
      confirmPassword = '';
      
      successMessage = 'Password changed successfully!';
    } catch (err: any) {
      error = err.message || 'Failed to change password';
    } finally {
      isChangingPassword = false;
      // Clear success message after 3 seconds
      if (successMessage) {
        setTimeout(() => {
          successMessage = null;
        }, 3000);
      }
    }
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Profile Settings</h1>
    <p class="text-muted-foreground mt-2">
      Update your personal information and password
    </p>
  </div>

  {#if error}
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded">
      {error}
    </div>
  {/if}

  {#if successMessage}
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
      {successMessage}
    </div>
  {/if}

  <Card.Root>
    <Card.Header>
      <Card.Title>Personal Information</Card.Title>
      <Card.Description>Update your name and email address</Card.Description>
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="space-y-2">
        <Label for="name">Full Name</Label>
        <Input id="name" bind:value={name} placeholder="Enter your name" />
      </div>

      <div class="space-y-2">
        <Label for="email">Email</Label>
        <Input
          id="email"
          type="email"
          bind:value={email}
          placeholder="Enter your email"
        />
      </div>
    </Card.Content>
    <Card.Footer>
      <Button onclick={handleSave} disabled={isSaving}>
        {isSaving ? 'Saving...' : 'Save Changes'}
      </Button>
    </Card.Footer>
  </Card.Root>

  <Card.Root>
    <Card.Header>
      <Card.Title>Change Password</Card.Title>
      <Card.Description>Update your password to keep your account secure</Card.Description>
    </Card.Header>
    <Card.Content class="space-y-4">
      <div class="space-y-2">
        <Label for="current-password">Current Password</Label>
        <Input
          id="current-password"
          type="password"
          bind:value={currentPassword}
          placeholder="Enter current password"
        />
      </div>

      <div class="space-y-2">
        <Label for="new-password">New Password</Label>
        <Input
          id="new-password"
          type="password"
          bind:value={newPassword}
          placeholder="Enter new password"
        />
      </div>

      <div class="space-y-2">
        <Label for="confirm-password">Confirm New Password</Label>
        <Input
          id="confirm-password"
          type="password"
          bind:value={confirmPassword}
          placeholder="Confirm new password"
        />
      </div>
    </Card.Content>
    <Card.Footer>
      <Button onclick={handlePasswordChange} disabled={isChangingPassword}>
        {isChangingPassword ? 'Changing...' : 'Change Password'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
