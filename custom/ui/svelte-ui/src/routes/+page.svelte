<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  
  onMount(() => {
    if (typeof localStorage !== 'undefined') {
      const token = localStorage.getItem('auth_token');
      if (token) {
        // User is authenticated, redirect to app
        goto('/app');
      } else {
        // User is not authenticated, redirect to login
        goto('/app/login');
      }
    } else {
      goto('/app/login');
    }
  });
</script>

<svelte:head>
  <title>Chatwoot</title>
</svelte:head>

<div class="flex min-h-screen items-center justify-center bg-background">
  <div class="text-center">
    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-r-transparent"></div>
    <p class="mt-2 text-sm text-muted-foreground">Loading...</p>
  </div>
</div>
