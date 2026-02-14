<script lang="ts">
  /**
   * Unauthorized Access Page
   * Shown when user doesn't have permission to access a resource
   */
  
  import { Button } from '$lib/components/ui/button';
  import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '$lib/components/ui/card';
  import { authStore } from '$lib/stores/auth.svelte';
  import { goto } from '$app/navigation';
  import { _ } from '$lib/i18n';
</script>

<div class="flex min-h-screen items-center justify-center bg-background p-4">
  <Card class="w-full max-w-md">
    <CardHeader class="text-center">
      <CardTitle class="text-xl">{$_('auth.unauthorized.title')}</CardTitle>
      <CardDescription>
        {$_('auth.unauthorized.description')}
      </CardDescription>
    </CardHeader>
    <CardContent class="text-center">
      <p class="text-sm text-muted-foreground">
        {$_('auth.unauthorized.note')}
      </p>
    </CardContent>
    <CardFooter class="flex flex-col gap-2">
      <Button 
        onclick={() => goto('/app')} 
        class="w-full"
      >
        {$_('auth.unauthorized.back_to_dashboard')}
      </Button>
      <Button 
        variant="outline" 
        onclick={() => authStore.logout()} 
        class="w-full"
      >
        {$_('auth.no_accounts.sign_out')}
      </Button>
    </CardFooter>
  </Card>
</div>