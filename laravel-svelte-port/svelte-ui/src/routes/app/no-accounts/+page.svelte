<script lang="ts">
  /**
   * No Accounts Page
   * Shown when user is logged in but has no accounts
   */
  
  import { Button } from '$lib/components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
  import { authStore } from '$lib/stores/auth.svelte';
  import { _ } from '$lib/i18n';
  
  async function handleCreateAccount() {
    // Redirect to account creation or show account creation form
    // This would depend on whether account creation from dashboard is enabled
    console.log('Creating new account...');
    // Implement actual account creation logic here
  }

  async function handleLogout() {
    await authStore.logout();
  }
</script>

<div class="flex min-h-screen items-center justify-center bg-background p-4">
  <Card class="w-full max-w-md">
    <CardHeader class="text-center">
      <CardTitle>{$_('auth.no_accounts.title')}</CardTitle>
      <CardDescription>
        {$_('auth.no_accounts.description')}
      </CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <p class="text-sm text-muted-foreground text-center">
        {$_('auth.no_accounts.note')}
      </p>
      <div class="flex flex-col gap-2">
        <Button onclick={handleCreateAccount} class="w-full">
          {$_('auth.no_accounts.create_account')}
        </Button>
        <Button variant="outline" onclick={handleLogout} class="w-full">
          {$_('auth.no_accounts.sign_out')}
        </Button>
      </div>
    </CardContent>
  </Card>
</div>