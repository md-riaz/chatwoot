<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { authStore } from '$lib/stores/auth.svelte';
  import { globalConfig } from '$lib/stores/globalConfig.svelte';

  const currentUser = $derived(authStore.currentUser);
  const canUpdateEmail = $derived(!Boolean(globalConfig.get('disableUserProfileUpdate')));

  let name = $state('');
  let displayName = $state('');
  let email = $state('');
  let messageSignature = $state('');
  let currentPassword = $state('');
  let newPassword = $state('');
  let confirmPassword = $state('');

  let isSavingProfile = $state(false);
  let isChangingPassword = $state(false);
  let isDeletingAvatar = $state(false);
  let isResettingAccessToken = $state(false);
  let error = $state<string | null>(null);
  let successMessage = $state<string | null>(null);
  let successTimeout: ReturnType<typeof setTimeout> | null = null;

  $effect(() => {
    if (!currentUser.id) return;

    name = currentUser.name || '';
    displayName = currentUser.displayName || '';
    email = currentUser.email || '';
    messageSignature = currentUser.messageSignature || '';
  });

  function setSuccess(message: string) {
    successMessage = message;
    if (successTimeout) {
      clearTimeout(successTimeout);
    }
    successTimeout = setTimeout(() => {
      successMessage = null;
      successTimeout = null;
    }, 3000);
  }

  async function handleSaveProfile() {
    try {
      isSavingProfile = true;
      error = null;
      successMessage = null;

      await authStore.updateProfile({
        name,
        email,
        displayName,
        messageSignature,
      });

      setSuccess('Profile updated successfully.');
    } catch (err: any) {
      error = err?.message || authStore.error || 'Failed to update profile';
    } finally {
      isSavingProfile = false;
    }
  }

  async function handlePasswordChange() {
    if (newPassword !== confirmPassword) {
      error = 'Passwords do not match.';
      return;
    }

    if (newPassword.length < 6) {
      error = 'Password must be at least 6 characters long.';
      return;
    }

    try {
      isChangingPassword = true;
      error = null;
      successMessage = null;

      await authStore.updatePassword({
        currentPassword,
        password: newPassword,
        passwordConfirmation: confirmPassword,
      });

      currentPassword = '';
      newPassword = '';
      confirmPassword = '';
      setSuccess('Password changed successfully.');
    } catch (err: any) {
      error = err?.message || authStore.error || 'Failed to change password';
    } finally {
      isChangingPassword = false;
    }
  }

  async function handleDeleteAvatar() {
    try {
      isDeletingAvatar = true;
      error = null;
      await authStore.deleteAvatar();
      setSuccess('Profile picture removed.');
    } catch (err: any) {
      error = err?.message || 'Failed to remove profile picture';
    } finally {
      isDeletingAvatar = false;
    }
  }

  async function handleResetAccessToken() {
    try {
      isResettingAccessToken = true;
      error = null;

      await authStore.resetAccessToken();
      setSuccess('Access token reset successfully.');
    } catch (err: any) {
      error = err?.message || 'Failed to reset access token';
    } finally {
      isResettingAccessToken = false;
    }
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Profile Settings</h1>
    <p class="text-muted-foreground mt-2">
      Manage your profile details, message signature, password, and personal credentials.
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
      <Card.Title>Profile</Card.Title>
      <Card.Description>
        Update your name, display name, email, and reply signature.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-6">
      <div class="flex items-center gap-4">
        {#if currentUser.avatarUrl}
          <img
            src={currentUser.avatarUrl}
            alt={currentUser.name || 'User avatar'}
            class="h-16 w-16 rounded-full object-cover border"
          />
        {:else}
          <div class="h-16 w-16 rounded-full border bg-muted flex items-center justify-center text-lg font-semibold">
            {(currentUser.name || 'U').charAt(0).toUpperCase()}
          </div>
        {/if}

        <div class="space-y-2">
          <div class="text-sm text-muted-foreground">
            Profile picture management is limited to removing the current avatar in this migrated screen.
          </div>
          {#if currentUser.avatarUrl}
            <Button
              variant="outline"
              onclick={handleDeleteAvatar}
              disabled={isDeletingAvatar}
            >
              {isDeletingAvatar ? 'Removing...' : 'Remove Profile Picture'}
            </Button>
          {/if}
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-2">
        <div class="space-y-2">
          <Label for="name">Full Name</Label>
          <Input id="name" bind:value={name} placeholder="Enter your full name" />
        </div>

        <div class="space-y-2">
          <Label for="display-name">Display Name</Label>
          <Input
            id="display-name"
            bind:value={displayName}
            placeholder="Enter your display name"
          />
        </div>
      </div>

      <div class="space-y-2">
        <Label for="email">Email</Label>
        <Input
          id="email"
          type="email"
          bind:value={email}
          placeholder="Enter your email"
          disabled={!canUpdateEmail}
        />
        {#if !canUpdateEmail}
          <p class="text-sm text-muted-foreground">
            Email updates are disabled by the current installation configuration.
          </p>
        {/if}
      </div>

      <div class="space-y-2">
        <Label for="message-signature">Message Signature</Label>
        <Textarea
          id="message-signature"
          bind:value={messageSignature}
          rows={5}
          placeholder="Add a signature appended to your replies"
        />
      </div>
    </Card.Content>
    <Card.Footer>
      <Button onclick={handleSaveProfile} disabled={isSavingProfile}>
        {isSavingProfile ? 'Saving...' : 'Save Profile'}
      </Button>
    </Card.Footer>
  </Card.Root>

  <Card.Root>
    <Card.Header>
      <Card.Title>Password</Card.Title>
      <Card.Description>
        Change your password for this account.
      </Card.Description>
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

  <Card.Root>
    <Card.Header>
      <Card.Title>Access Token</Card.Title>
      <Card.Description>
        Reset the personal access token used for authenticated integrations.
      </Card.Description>
    </Card.Header>
    <Card.Content class="space-y-3">
      {#if currentUser.accessToken}
        <div class="rounded-md border bg-muted/40 px-3 py-2 font-mono text-sm break-all">
          {currentUser.accessToken}
        </div>
      {:else}
        <p class="text-sm text-muted-foreground">
          No access token is currently available for this user.
        </p>
      {/if}
    </Card.Content>
    <Card.Footer>
      <Button
        variant="outline"
        onclick={handleResetAccessToken}
        disabled={isResettingAccessToken}
      >
        {isResettingAccessToken ? 'Resetting...' : 'Reset Access Token'}
      </Button>
    </Card.Footer>
  </Card.Root>
</div>
