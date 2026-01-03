/**
 * Route parameter utilities
 * Type-safe parameter extraction and validation
 */

import type { Params } from '@sveltejs/kit';

/**
 * Get route parameter as string
 */
export function getParam(params: Params, key: string): string | undefined {
  return params[key];
}

/**
 * Get route parameter as string with default
 */
export function getParamOrDefault(params: Params, key: string, defaultValue: string): string {
  return params[key] ?? defaultValue;
}

/**
 * Get route parameter as number
 */
export function getNumericParam(params: Params, key: string): number | undefined {
  const value = params[key];
  if (!value) return undefined;
  
  const num = parseInt(value, 10);
  return isNaN(num) ? undefined : num;
}

/**
 * Get route parameter as number with default
 */
export function getNumericParamOrDefault(params: Params, key: string, defaultValue: number): number {
  const value = getNumericParam(params, key);
  return value ?? defaultValue;
}

/**
 * Get required route parameter
 * Throws error if parameter is missing
 */
export function requireParam(params: Params, key: string): string {
  const value = params[key];
  if (!value) {
    throw new Error(`Required parameter '${key}' is missing`);
  }
  return value;
}

/**
 * Get required numeric route parameter
 * Throws error if parameter is missing or invalid
 */
export function requireNumericParam(params: Params, key: string): number {
  const value = params[key];
  if (!value) {
    throw new Error(`Required parameter '${key}' is missing`);
  }
  
  const num = parseInt(value, 10);
  if (isNaN(num)) {
    throw new Error(`Parameter '${key}' must be a number`);
  }
  
  return num;
}

/**
 * Parse query parameter as string
 */
export function getQueryParam(searchParams: URLSearchParams, key: string): string | null {
  return searchParams.get(key);
}

/**
 * Parse query parameter as number
 */
export function getQueryParamAsNumber(searchParams: URLSearchParams, key: string): number | null {
  const value = searchParams.get(key);
  if (!value) return null;
  
  const num = parseInt(value, 10);
  return isNaN(num) ? null : num;
}

/**
 * Parse query parameter as boolean
 */
export function getQueryParamAsBoolean(searchParams: URLSearchParams, key: string): boolean {
  const value = searchParams.get(key);
  return value === 'true' || value === '1';
}

/**
 * Parse query parameter as array
 */
export function getQueryParamAsArray(searchParams: URLSearchParams, key: string): string[] {
  return searchParams.getAll(key);
}

/**
 * Get pagination params from query string
 */
export interface PaginationParams {
  page: number;
  perPage: number;
}

export function getPaginationParams(
  searchParams: URLSearchParams,
  defaults: PaginationParams = { page: 1, perPage: 20 }
): PaginationParams {
  const page = getQueryParamAsNumber(searchParams, 'page') ?? defaults.page;
  const perPage = getQueryParamAsNumber(searchParams, 'per_page') ?? defaults.perPage;
  
  return {
    page: Math.max(1, page),
    perPage: Math.max(1, Math.min(100, perPage)) // Cap at 100
  };
}

/**
 * Get filter params from query string
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

export function getFilterParams(searchParams: URLSearchParams): FilterParams {
  return {
    status: getQueryParam(searchParams, 'status') ?? undefined,
    assigneeType: getQueryParam(searchParams, 'assignee_type') ?? undefined,
    inboxId: getQueryParamAsNumber(searchParams, 'inbox_id') ?? undefined,
    teamId: getQueryParamAsNumber(searchParams, 'team_id') ?? undefined,
    labels: getQueryParamAsArray(searchParams, 'label'),
    sort: getQueryParam(searchParams, 'sort') ?? undefined,
    order: (getQueryParam(searchParams, 'order') as 'asc' | 'desc') ?? 'desc'
  };
}

/**
 * Build query string from params object
 */
export function buildQueryString(params: Record<string, any>): string {
  const searchParams = new URLSearchParams();
  
  Object.entries(params).forEach(([key, value]) => {
    if (value === undefined || value === null) return;
    
    if (Array.isArray(value)) {
      value.forEach(v => searchParams.append(key, String(v)));
    } else {
      searchParams.append(key, String(value));
    }
  });
  
  const queryString = searchParams.toString();
  return queryString ? `?${queryString}` : '';
}

/**
 * Update single query parameter
 */
export function updateQueryParam(
  searchParams: URLSearchParams,
  key: string,
  value: string | number | boolean | null
): URLSearchParams {
  const newParams = new URLSearchParams(searchParams);
  
  if (value === null || value === undefined) {
    newParams.delete(key);
  } else {
    newParams.set(key, String(value));
  }
  
  return newParams;
}

/**
 * Update multiple query parameters
 */
export function updateQueryParams(
  searchParams: URLSearchParams,
  updates: Record<string, string | number | boolean | null>
): URLSearchParams {
  const newParams = new URLSearchParams(searchParams);
  
  Object.entries(updates).forEach(([key, value]) => {
    if (value === null || value === undefined) {
      newParams.delete(key);
    } else {
      newParams.set(key, String(value));
    }
  });
  
  return newParams;
}

/**
 * Remove query parameter
 */
export function removeQueryParam(searchParams: URLSearchParams, key: string): URLSearchParams {
  const newParams = new URLSearchParams(searchParams);
  newParams.delete(key);
  return newParams;
}

/**
 * Type-safe route params extractor
 */
export function extractRouteParams<T extends Record<string, string | number>>(
  params: Params,
  schema: Record<keyof T, 'string' | 'number'>
): T {
  const result = {} as T;
  
  for (const [key, type] of Object.entries(schema)) {
    const value = params[key];
    
    if (type === 'number') {
      const num = value ? parseInt(value, 10) : undefined;
      if (num !== undefined && !isNaN(num)) {
        result[key as keyof T] = num as T[keyof T];
      }
    } else {
      if (value) {
        result[key as keyof T] = value as T[keyof T];
      }
    }
  }
  
  return result;
}
