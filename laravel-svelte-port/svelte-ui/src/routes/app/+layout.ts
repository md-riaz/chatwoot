/**
 * Authenticated App Layout - Load Function
 * Checks authentication status and redirects if not authenticated
 */

import { redirect } from '@sveltejs/kit';
import type { LayoutLoad } from './$types';

export const load: LayoutLoad = async ({ url }) => {
  // Paths that don't require authentication
  const publicPaths = ['/app/login', '/app/signup', '/app/unauthorized'];

  // Skip auth check for public paths
  if (publicPaths.some(path => url.pathname.startsWith(path))) {
    return {};
  }

  // Check if user is authenticated (client-side check)
  if (typeof localStorage !== 'undefined') {
    const token = localStorage.getItem('auth_token');
    const user = localStorage.getItem('current_user');

    console.log('Auth check - Token exists:', !!token);
    console.log('Auth check - User exists:', !!user);
    console.log('Auth check - Current path:', url.pathname);

    if (!token) {
      console.log('No token found, redirecting to login');
      // Redirect to login page with return URL
      throw redirect(302, `/app/login?redirect=${encodeURIComponent(url.pathname)}`);
    }

    console.log('Token found, allowing access');
  }

  return {};
};
