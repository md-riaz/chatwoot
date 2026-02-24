/**
 * Route guard utilities for authentication and authorization
 * Replaces Vue Router beforeEach guards
 */

import { goto } from '$app/navigation';
import type { Load } from '@sveltejs/kit';

/**
 * Check if user is authenticated
 */
export function isAuthenticated(): boolean {
  if (typeof localStorage === 'undefined') return false;

  try {
    const token = localStorage.getItem('auth_token');
    return !!token;
  } catch {
    return false;
  }
}

/**
 * Get current user from storage
 */
export function getCurrentUser(): any | null {
  if (typeof localStorage === 'undefined') return null;

  try {
    const userStr = localStorage.getItem('current_user');
    return userStr ? JSON.parse(userStr) : null;
  } catch {
    return null;
  }
}

/**
 * Check if user has specific role
 */
export function hasRole(role: string): boolean {
  const user = getCurrentUser();
  return user?.role === role;
}

/**
 * Check if user has any of the specified roles
 */
export function hasAnyRole(roles: string[]): boolean {
  const user = getCurrentUser();
  return roles.includes(user?.role);
}

/**
 * Require authentication guard
 * Use in +page.ts or +layout.ts load functions
 */
export function requireAuth(): void {
  if (!isAuthenticated()) {
    goto('/login');
  }
}

/**
 * Require specific role guard
 */
export function requireRole(role: string): void {
  if (!isAuthenticated()) {
    goto('/login');
    return;
  }

  if (!hasRole(role)) {
    goto('/unauthorized');
  }
}

/**
 * Require any of the specified roles
 */
export function requireAnyRole(roles: string[]): void {
  if (!isAuthenticated()) {
    goto('/login');
    return;
  }

  if (!hasAnyRole(roles)) {
    goto('/unauthorized');
  }
}

/**
 * Require account context
 */
export function requireAccount(accountId?: string): void {
  if (!isAuthenticated()) {
    goto('/login');
    return;
  }

  if (accountId) {
    const user = getCurrentUser();
    const hasAccess = user?.accounts?.some((a: any) => a.id === parseInt(accountId));

    if (!hasAccess) {
      goto('/unauthorized');
    }
  }
}

/**
 * Redirect if already authenticated
 * Use for login/signup pages
 */
export function redirectIfAuthenticated(to: string = '/'): void {
  if (isAuthenticated()) {
    goto(to);
  }
}

/**
 * Create a load function with auth guard
 * 
 * @example
 * ```typescript
 * // In +page.ts
 * export const load = createAuthGuard();
 * ```
 */
export function createAuthGuard(options: {
  requireAuth?: boolean;
  requireRole?: string;
  requireAnyRole?: string[];
  redirectTo?: string;
} = {}): Load {
  return async ({ url }) => {
    const {
      requireAuth: needsAuth = true,
      requireRole: role,
      requireAnyRole: anyRole,
      redirectTo = '/login'
    } = options;

    if (needsAuth && !isAuthenticated()) {
      throw goto(`${redirectTo}?redirect=${encodeURIComponent(url.pathname)}`);
    }

    if (role && !hasRole(role)) {
      throw goto('/unauthorized');
    }

    if (anyRole && !hasAnyRole(anyRole)) {
      throw goto('/unauthorized');
    }

    return {
      user: getCurrentUser()
    };
  };
}

/**
 * Guest guard - redirect if authenticated
 */
export function createGuestGuard(redirectTo: string = '/'): Load {
  return async () => {
    if (isAuthenticated()) {
      throw goto(redirectTo);
    }

    return {};
  };
}
