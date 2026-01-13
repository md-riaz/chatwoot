<script lang="ts">
  /**
   * Account Suspended Page
   * Shown when an account has been suspended
   */
  
  import { Button } from '$lib/components/ui/button';
  import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '$lib/components/ui/card';
  import { authStore } from '$lib/stores/auth.svelte';
  import { goto } from '$app/navigation';
</script>

<div class="flex min-h-screen items-center justify-center bg-background p-4">
  <Card class="w-full max-w-md">
    <CardHeader class="text-center">
      <CardTitle class="text-xl">Account Suspended</CardTitle>
      <CardDescription>
        This account has been temporarily suspended.
      </CardDescription>
    </CardHeader>
    <CardContent class="text-center">
      <p class="text-sm text-muted-foreground">
        Contact your administrator or support to reactivate this account.
      </p>
    </CardContent>
    <CardFooter class="flex flex-col gap-2">
      <Button 
        onclick={() => goto('/app')} 
        class="w-full"
      >
        Back to Dashboard
      </Button>
      <Button 
        variant="outline" 
        onclick={() => authStore.logout()} 
        class="w-full"
      >
        Sign Out
      </Button>
    </CardFooter>
  </Card>
</div>