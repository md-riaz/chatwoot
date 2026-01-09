<script lang="ts">
  /**
   * App Root - Redirect to Account Dashboard
   * Redirects to the user's default account or super admin dashboard
   */
  
  import { goto } from '$app/navigation';
  import { authStore } from '$lib/stores/auth.svelte';
  import { onMount } from 'svelte';
  
  onMount(() => {
    console.log('App root - checking user:', authStore.currentUser);
    console.log('User type:', authStore.currentUser.type);
    console.log('User accounts:', authStore.userAccounts);
    
    // Check if user is logged in
    if (authStore.isLoggedIn) {
      // If user is SuperAdmin, redirect to super admin dashboard
      if (authStore.currentUser.type === 'SuperAdmin') {
        console.log('SuperAdmin detected, redirecting to super admin dashboard');
        goto('/app/super_admin', { replaceState: true });
        return;
      }
      
      // For regular users, check if they have accounts
      if (authStore.userAccounts.length > 0) {
        // Redirect to the first account's dashboard
        const firstAccount = authStore.userAccounts[0];
        console.log('Regular user with accounts, redirecting to account:', firstAccount.id);
        goto(`/app/accounts/${firstAccount.id}`, { replaceState: true });
      } else {
        // User is logged in but has no accounts
        console.warn('User is logged in but has no accounts');
        goto('/app/login', { replaceState: true });
      }
    } else {
      // User is not logged in
      console.log('User not logged in, redirecting to login');
      goto('/app/login', { replaceState: true });
    }
  });
</script>

<div class="flex items-center justify-center h-screen">
  <div class="text-center">
    <p class="text-muted-foreground">Loading...</p>
  </div>
</div>
