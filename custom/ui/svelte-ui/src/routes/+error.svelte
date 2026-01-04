<script lang="ts">
  /**
   * Error Page
   * Beautiful error pages for 404 and other errors
   */
  
  import { page } from '$app/stores';
  import { Button } from '$lib/components/ui/button';
  import { goto } from '$app/navigation';
  
  $: status = $page.status;
  $: message = $page.error?.message || 'An error occurred';
  
  function goHome() {
    goto('/');
  }
  
  function goBack() {
    window.history.back();
  }
</script>

<svelte:head>
  <title>{status} - Error | Chatwoot</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center bg-background px-4">
  <div class="mx-auto max-w-2xl space-y-8 text-center">
    <!-- Error Code -->
    <div class="space-y-2">
      <h1 class="text-9xl font-bold tracking-tight text-primary">
        {status}
      </h1>
      
      <!-- Error Message -->
      <h2 class="text-3xl font-semibold tracking-tight">
        {#if status === 404}
          Page Not Found
        {:else if status === 403}
          Access Denied
        {:else if status === 500}
          Server Error
        {:else}
          Something Went Wrong
        {/if}
      </h2>
      
      <p class="text-lg text-muted-foreground max-w-md mx-auto">
        {#if status === 404}
          The page you're looking for doesn't exist or has been moved.
        {:else if status === 403}
          You don't have permission to access this resource.
        {:else if status === 500}
          We're experiencing technical difficulties. Please try again later.
        {:else}
          {message}
        {/if}
      </p>
    </div>
    
    <!-- Actions -->
    <div class="flex gap-4 justify-center flex-wrap">
      <Button on:click={goBack} variant="outline" size="lg">
        Go Back
      </Button>
      <Button on:click={goHome} size="lg">
        Go to Home
      </Button>
    </div>
    
    <!-- Help Text -->
    {#if status === 404}
      <div class="text-sm text-muted-foreground">
        <p>If you believe this is a mistake, please contact support.</p>
      </div>
    {/if}
  </div>
</div>
