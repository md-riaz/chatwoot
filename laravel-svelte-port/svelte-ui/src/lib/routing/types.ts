/**
 * TypeScript types for routing
 */

import type { Load } from '@sveltejs/kit';

/**
 * Route guard options
 */
export interface RouteGuardOptions {
  requireAuth?: boolean;
  requireRole?: string;
  requireAnyRole?: string[];
  redirectTo?: string;
}

/**
 * Navigation options
 */
export interface NavigationOptions {
  replaceState?: boolean;
  keepScrollPosition?: boolean;
  query?: Record<string, string | number | boolean>;
  preserveQuery?: boolean;
}

/**
 * Pagination parameters
 */
export interface PaginationParams {
  page: number;
  perPage: number;
}

/**
 * Filter parameters
 */
export interface FilterParams {
  status?: string;
  assigneeType?: string;
  inboxId?: number;
  teamId?: number;
  labels?: string[];
  sort?: string;
  order?: 'asc' | 'desc';
}

/**
 * Route parameter schema
 */
export type RouteParamType = 'string' | 'number';

/**
 * Extract type from schema
 */
export type ExtractParamType<T extends RouteParamType> = 
  T extends 'number' ? number : string;
