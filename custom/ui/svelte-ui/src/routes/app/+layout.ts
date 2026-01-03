/**
 * Authenticated App Layout - Load Function
 * Checks authentication status and redirects if not authenticated
 */

import { redirect } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ url }) => {
  // Check if user is authenticated (client-side check)
  if (typeof localStorage !== 'undefined') {
    const token = localStorage.getItem('chatwoot_auth_token');
    
    if (!token) {
      // Redirect to login page with return URL
      throw redirect(302, `/auth/login?redirect=${encodeURIComponent(url.pathname)}`);
    }
  }
  
  return {};
};
