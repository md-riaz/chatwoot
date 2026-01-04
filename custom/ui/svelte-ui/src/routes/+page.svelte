<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  
  let loading = true;
  
  // Redirect based on authentication status
  // Note: svelte-ui uses 'chatwoot_auth_token' while sveltekit-ui uses 'auth_token'
  // This is consistent with each project's existing implementation
  onMount(async () => {
    if (typeof localStorage !== 'undefined') {
      const token = localStorage.getItem('chatwoot_auth_token');
      if (token) {
        // User is authenticated, redirect to app
        goto('/app');
      } else {
        // User is not authenticated, redirect to login
        goto('/auth/login');
      }
    } else {
      goto('/auth/login');
    }
    loading = false;
  });
</script>

<svelte:head>
  <title>Chatwoot</title>
</svelte:head>

{#if loading}
  <div class="flex min-h-screen items-center justify-center bg-background">
    <div class="text-center">
      <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
      <p class="mt-2 text-sm text-muted-foreground">Loading...</p>
    </div>
  </div>
{/if}
