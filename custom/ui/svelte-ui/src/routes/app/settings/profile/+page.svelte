<script lang="ts">
  /**
   * Profile Settings Page
   * Manage user profile information
   */

  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';

  let name = $state('John Doe');
  let email = $state('john@example.com');
  let currentPassword = $state('');
  let newPassword = $state('');
  let confirmPassword = $state('');
  let isSaving = $state(false);
  let isChangingPassword = $state(false);

  async function handleSave() {
    isSaving = true;
    // TODO: Implement save functionality
    setTimeout(() => {
      isSaving = false;
      alert('Profile updated successfully!');
    }, 1000);
  }

  async function handlePasswordChange() {
    if (newPassword !== confirmPassword) {
      alert('Passwords do not match!');
      return;
    }

    isChangingPassword = true;
    // TODO: Implement password change functionality
    setTimeout(() => {
      isChangingPassword = false;
      currentPassword = '';
      newPassword = '';
      confirmPassword = '';
      alert('Password changed successfully!');
    }, 1000);
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Profile Settings</h1>
    <p class="text-muted-foreground mt-2">
      Update your personal information and password
    </p>
  </div>

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
