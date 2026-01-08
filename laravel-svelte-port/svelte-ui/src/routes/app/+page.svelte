<script lang="ts">
  /**
   * App Root - Redirect to Account Dashboard
   * Redirects to the user's default account
   */
  
  import { goto } from '$app/navigation';
  import { authStore } from '$lib/stores/auth.svelte';
  import { onMount } from 'svelte';
  
  onMount(() => {
    if (authStore.currentAccount?.id) {
      // Redirect to the current account's dashboard
      goto(`/app/accounts/${authStore.currentAccount.id}`, { replaceState: true });
    } else {
      // If no account, redirect to login or onboarding
      goto('/app/login', { replaceState: true });
    }
  });
</script>

<div class="flex items-center justify-center h-screen">
  <div class="text-center">
    <p class="text-muted-foreground">Loading...</p>
  </div>
</div>
