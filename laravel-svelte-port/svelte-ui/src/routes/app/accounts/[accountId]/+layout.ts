import { authStore } from '$lib/stores/auth.svelte';
import { redirect } from '@sveltejs/kit';

export const ssr = false;

export async function load({ params, url }) {
  const accountId = parseInt(params.accountId);
  
  // Wait for auth to be initialized
  if (!authStore.currentUser.id) {
    try {
      // If we're on the client (which we are due to ssr=false), 
      // authStore might need to check validity if not in localStorage
      // But authStore loads from localStorage in constructor.
      // If it's not there, we redirect.
      if (!authStore.isLoggedIn) {
         throw redirect(302, `/app/login?redirect=${encodeURIComponent(url.pathname)}`);
      }
    } catch (error) {
      if (error instanceof Response) throw error; // Re-throw redirects
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
