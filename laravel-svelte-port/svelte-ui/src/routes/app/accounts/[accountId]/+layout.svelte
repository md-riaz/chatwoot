<script lang="ts">
  /**
   * Account-based Layout
   * Ensures user has access to the specified account
   * Updated with Svelte 5 runes and shadcn-svelte components
   */
  
  import { authStore } from '$lib/stores/auth.svelte';
  import { redirect } from '@sveltejs/kit';
  
  // Define load function for SvelteKit
  export async function load({ params, url }: { params: { accountId: string }; url: URL }) {
    const accountId = parseInt(params.accountId);
    
    // Wait for auth to be initialized
    if (!authStore.currentUser.id) {
      try {
        await authStore.setUser();
      } catch (error) {
        console.error('Failed to initialize auth:', error);
        throw redirect(302, `/app/login?redirect=${encodeURIComponent(url.pathname)}`);
      }
    }
    
    // Check if user is authenticated
    if (!authStore.isLoggedIn) {
      throw redirect(302, `/app/login?redirect=${encodeURIComponent(url.pathname)}`);
    }
    
    // Check if user has access to this account
    const hasAccess = authStore.currentUser.accounts.some((acc: any) => acc.id === accountId);
    if (!hasAccess) {
      // Check if the account exists by trying to fetch it
      // If it doesn't exist, redirect to no-accounts
      // If it exists but user doesn't have access, redirect to unauthorized
      throw redirect(302, '/app/unauthorized');
    }
    
    // Check if account is suspended (would require API call in real implementation)
    // For now, we'll just return the account ID
    return {
      accountId
    };
  }
</script>

<!-- This layout will wrap all account-specific routes -->
<slot />