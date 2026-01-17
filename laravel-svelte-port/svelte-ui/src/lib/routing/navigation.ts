/**
 * Navigation helper utilities
 * Replaces Vue Router programmatic navigation
 */

import { goto as svelteGoto } from '$app/navigation';
import { page } from '$app/stores';
import { get } from 'svelte/store';

/**
 * Enhanced goto with query parameters
 */
export async function navigate(
  path: string,
  options: {
    replaceState?: boolean;
    keepScrollPosition?: boolean;
    query?: Record<string, string | number | boolean>;
    preserveQuery?: boolean;
  } = {}
) {
  const { query, preserveQuery, ...gotoOptions } = options;
  
  let finalPath = path;
  
  // Add query parameters
  if (query || preserveQuery) {
    const searchParams = new URLSearchParams();
    
    // Preserve existing query params if requested
    if (preserveQuery) {
      const currentPage = get(page);
      currentPage.url.searchParams.forEach((value, key) => {
        searchParams.append(key, value);
      });
    }
    
    // Add new query params
    if (query) {
      Object.entries(query).forEach(([key, value]) => {
        searchParams.set(key, String(value));
      });
    }
    
    const queryString = searchParams.toString();
    finalPath = queryString ? `${path}?${queryString}` : path;
  }
  
  return svelteGoto(finalPath, gotoOptions);
}

/**
 * Navigate back in history
 */
export function goBack(): void {
  if (typeof window !== 'undefined') {
    window.history.back();
  }
}

/**
 * Navigate forward in history
 */
export function goForward(): void {
  if (typeof window !== 'undefined') {
    window.history.forward();
  }
}

/**
 * Build frontend URL with account context
 * Replaces Vue Router's frontendURL helper
 */
export function frontendURL(path: string, accountId?: number | string): string {
  const basePath = accountId ? `/accounts/${accountId}` : '';
  const cleanPath = path.startsWith('/') ? path : `/${path}`;
  return `${basePath}${cleanPath}`;
}

/**
 * Build conversation URL
 */
export function conversationURL(accountId: number | string, conversationId: number | string): string {
  return frontendURL(`/conversations/${conversationId}`, accountId);
}

/**
 * Build contact URL
 */
export function contactURL(accountId: number | string, contactId: number | string): string {
  return frontendURL(`/contacts/${contactId}`, accountId);
}

/**
 * Build settings URL
 */
export function settingsURL(accountId: number | string, section?: string): string {
  const base = frontendURL('/settings', accountId);
  return section ? `${base}/${section}` : base;
}

/**
 * Build reports URL
 */
export function reportsURL(accountId: number | string, reportType?: string): string {
  const base = frontendURL('/reports', accountId);
  return reportType ? `${base}/${reportType}` : base;
}

export function accountScopedPath(accountId: number | string, path: string): string {
  const clean = path.startsWith('/') ? path.slice(1) : path;
  return `/app/accounts/${accountId}/${clean}`;
}

/**
 * Check if current route matches path
 */
export function isCurrentRoute(path: string): boolean {
  if (typeof window === 'undefined') return false;
  return window.location.pathname === path;
}

/**
 * Check if current route starts with path
 */
export function isRouteActive(path: string): boolean {
  if (typeof window === 'undefined') return false;
  return window.location.pathname.startsWith(path);
}

export function isAnyRouteActive(paths: string[]): boolean {
  if (typeof window === 'undefined') return false;
  return paths.some(path => window.location.pathname.startsWith(path));
}

/**
 * Get query parameter from current URL
 */
export function getQueryParam(key: string): string | null {
  if (typeof window === 'undefined') return null;
  const params = new URLSearchParams(window.location.search);
  return params.get(key);
}

/**
 * Get all query parameters from current URL
 */
export function getAllQueryParams(): Record<string, string> {
  if (typeof window === 'undefined') return {};
  
  const params = new URLSearchParams(window.location.search);
  const result: Record<string, string> = {};
  
  params.forEach((value, key) => {
    result[key] = value;
  });
  
  return result;
}

/**
 * Navigate with confirmation dialog
 */
export async function navigateWithConfirmation(
  path: string,
  message: string = 'Are you sure you want to leave this page?'
): Promise<void> {
  if (typeof window === 'undefined') return;
  
  const confirmed = window.confirm(message);
  if (confirmed) {
    await navigate(path);
  }
}

/**
 * Reload current page
 */
export function reloadPage(): void {
  if (typeof window !== 'undefined') {
    window.location.reload();
  }
}

/**
 * Navigation history management
 */
export const navigationHistory = {
  /**
   * Get navigation history length
   */
  getLength(): number {
    return typeof window !== 'undefined' ? window.history.length : 0;
  },
  
  /**
   * Check if can go back
   */
  canGoBack(): boolean {
    return this.getLength() > 1;
  },
  
  /**
   * Go to specific position in history
   */
  go(delta: number): void {
    if (typeof window !== 'undefined') {
      window.history.go(delta);
    }
  }
};

/**
 * Scroll to top of page
 */
export function scrollToTop(smooth: boolean = true): void {
  if (typeof window !== 'undefined') {
    window.scrollTo({
      top: 0,
      behavior: smooth ? 'smooth' : 'auto'
    });
  }
}

/**
 * Scroll to element by ID
 */
export function scrollToElement(elementId: string, smooth: boolean = true): void {
  if (typeof window === 'undefined') return;
  
  const element = document.getElementById(elementId);
  if (element) {
    element.scrollIntoView({
      behavior: smooth ? 'smooth' : 'auto',
      block: 'start'
    });
  }
}
