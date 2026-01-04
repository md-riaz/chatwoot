<script lang="ts">
  /**
   * Unauthorized Access Page
   * Shown when user tries to access a route they don't have permission for
   */
  
  import { Button } from '$lib/components/ui/button';
  import { goto } from '$app/navigation';
  
  function goBack() {
    window.history.back();
  }
  
  function goToLogin() {
    goto('/app/login');
  }
</script>

<div class="flex min-h-screen items-center justify-center bg-background">
  <div class="mx-auto max-w-md space-y-6 text-center">
    <div class="space-y-2">
      <h1 class="text-4xl font-bold tracking-tight">403</h1>
      <h2 class="text-2xl font-semibold">Unauthorized Access</h2>
      <p class="text-muted-foreground">
        You don't have permission to access this page.
      </p>
    </div>
    
    <div class="flex gap-4 justify-center">
      <Button on:click={goBack} variant="outline">
        Go Back
      </Button>
      <Button on:click={goToLogin}>
        Go to Login
      </Button>
    </div>
  </div>
</div>
