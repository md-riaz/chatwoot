<script lang="ts">
  /**
   * Account Settings Page
   * Manage account-level settings like name, language, timezone
   */

  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';

  let accountName = $state('My Account');
  let language = $state('en');
  let timezone = $state('UTC');
  let isSaving = $state(false);

  async function handleSave() {
    isSaving = true;
    // TODO: Implement save functionality
    setTimeout(() => {
      isSaving = false;
      alert('Settings saved successfully!');
    }, 1000);
  }
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Account Settings</h1>
    <p class="text-muted-foreground mt-2">
      Manage your account preferences and general settings
    </p>
  </div>

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
