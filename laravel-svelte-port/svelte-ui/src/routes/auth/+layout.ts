/**
 * Auth Layout - Load Function
 * Redirects authenticated users to dashboard
 */

import { redirect } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async () => {
  // Check if user is already authenticated (client-side check)
  if (typeof localStorage !== 'undefined') {
    const token = localStorage.getItem('auth_token');
    
    if (token) {
      // Redirect to app dashboard
      throw redirect(302, '/app');
    }
  }
  
  return {};
};
