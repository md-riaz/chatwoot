<script lang="ts">
  /**
   * App Root - Redirect to Account Dashboard
   * Redirects to the user's default account or super admin dashboard
   * Matches Vue's validateAuthenticateRoutePermission behavior
   */
  
  import { goto } from '$app/navigation';
  import { authStore } from '$lib/stores/auth.svelte';
  import { onMount } from 'svelte';
  
  onMount(async () => {
    console.log('App root - checking user:', authStore.currentUser);
    console.log('User type:', authStore.currentUser.type);
    console.log('User accounts:', authStore.userAccounts);
    
    // Initialize auth if not already done
    if (!authStore.currentUser.id) {
      try {
        await authStore.setUser();
      } catch (error) {
        console.error('Failed to initialize auth:', error);
      }
    }
    
    // Check if user is logged in
    if (authStore.isLoggedIn) {
      // Both regular users and SuperAdmins should go to their account dashboard
      // SuperAdmins are also part of an account, so they should go to regular dashboard
      if (authStore.userAccounts.length > 0) {
        // Redirect to the first account's dashboard
        const firstAccount = authStore.userAccounts[0];
        console.log('User with accounts, redirecting to account:', firstAccount.id);
        goto(`/app/accounts/${firstAccount.id}/dashboard`, { replaceState: true });
      } else {
        // User is logged in but has no accounts
        console.warn('User is logged in but has no accounts');
        goto('/app/no-accounts', { replaceState: true });
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
