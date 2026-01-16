/**
 * TypeScript types for API layer
 */

/**
 * Paginated API response
 */
export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    currentPage: number;
    nextPage: number | null;
    prevPage: number | null;
    totalPages: number;
    totalCount: number;
  };
}

/**
 * Standard API response wrapper
 */
export interface ApiResponse<T> {
  data: T;
  message?: string;
}

/**
 * Request options
 */
export interface RequestOptions extends RequestInit {
  skipAuth?: boolean;
  skipTransform?: boolean;
  timeout?: number;
}

/**
 * Upload progress callback
 */
export type UploadProgressCallback = (progress: number) => void;

/**
 * File upload options
 */
export interface UploadOptions {
  onProgress?: UploadProgressCallback;
  signal?: AbortSignal;
  method?: string;
  headers?: Record<string, string>;
}

/**
 * Common query parameters
 */
export interface QueryParams {
  page?: number;
  perPage?: number;
  sort?: string;
  order?: 'asc' | 'desc';
  search?: string;
  [key: string]: any;
}

/**
 * Error response from API
 */
export interface ApiErrorResponse {
  message: string;
  errors?: Record<string, string[]>;
  error?: string;
}

/**
 * Onboarding status response
 */
export interface OnboardingData {
  onboardingPending: boolean;
  adminExists?: boolean;
}
