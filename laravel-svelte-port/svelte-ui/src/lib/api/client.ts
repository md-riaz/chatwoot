/**
 * Enhanced API client with request/response transformation and error handling
 * Replaces axios from Vue application with modern ky-based client
 */

import ky, { type KyInstance, type Options } from 'ky';
import { keysToCamel, keysToSnake } from './transformers';
import { ApiError, NetworkError, handleHttpError } from './errors';
import type { RequestOptions, UploadOptions, UploadProgressCallback } from './types';

/**
 * Get auth token from storage
 */
function getAuthToken(): string | null {
  if (typeof localStorage === 'undefined') return null;
  try {
    return localStorage.getItem('auth_token');
  } catch (e) {
    return null;
  }
}

/**
 * Create base ky instance with configuration
 */
const createApiClient = (): KyInstance => {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000';

  return ky.create({
    prefixUrl: baseUrl,
    timeout: 30000,
    retry: {
      limit: 3,
      methods: ['get'],
      statusCodes: [408, 413, 429, 500, 502, 503, 504],
      backoffLimit: 30000
    },
    hooks: {
      beforeRequest: [
        async (request, options: RequestOptions) => {
          // Add authentication header
          if (!options.skipAuth) {
            const token = getAuthToken();
            if (token) {
              request.headers.set('Authorization', `Bearer ${token}`);
            }
          }

          // Transform request body to snake_case
          if (
            request.body &&
            request.method !== 'GET' &&
            request.method !== 'HEAD' &&
            !options.skipTransform
          ) {
            const contentType = request.headers.get('content-type');
            
            // Only transform JSON bodies
            if (!contentType || contentType.includes('application/json')) {
              try {
                // Handle both string and ReadableStream bodies
                const bodyContent = typeof request.body === 'string' 
                  ? request.body 
                  : (request.body ? await new Response(request.body).text() : '');
                
                if (bodyContent) {
                  const data = JSON.parse(bodyContent);
                  const transformed = keysToSnake(data);
                  request.headers.set('content-type', 'application/json');
                  return new Request(request, {
                    body: JSON.stringify(transformed)
                  });
                }
              } catch (e) {
                // Body is not JSON, leave as is
              }
            }
          }
        }
      ],
      afterResponse: [
        async (request, options: RequestOptions, response) => {
          // Don't transform if explicitly skipped
          if (options.skipTransform) {
            return response;
          }

          // Transform response body to camelCase
          if (response.ok) {
            const contentType = response.headers.get('content-type');
            if (contentType?.includes('application/json')) {
              try {
                const data = await response.json();
                const transformed = keysToCamel(data);
                return new Response(JSON.stringify(transformed), response);
              } catch (e) {
                // Response is not JSON or already consumed
                return response;
              }
            }
          }
          
          return response;
        }
      ],
      beforeError: [
        async (error) => {
          const { request, response } = error;
          
          if (!response) {
            // Network error
            throw new NetworkError('Network error: Unable to connect to server');
          }

          // Handle specific error status codes
          const endpoint = request.url;
          
          if (response.status === 401) {
            // Clear auth token on 401
            if (typeof localStorage !== 'undefined') {
              localStorage.removeItem('auth_token');
            }
            
            // Redirect to login if in browser
            if (typeof window !== 'undefined' && window.location) {
              window.location.href = '/login';
            }
          }

          // Parse and throw ApiError
          await handleHttpError(response, endpoint);
          
          // Return the error to satisfy the hook type
          return error;
        }
      ]
    }
  });
};

/**
 * API client instance
 */
export const api = createApiClient();

/**
 * Helper function to upload files with progress tracking
 */
export async function uploadFile(
  endpoint: string,
  file: File,
  options: UploadOptions = {}
): Promise<any> {
  const formData = new FormData();
  formData.append('file', file);

  // Create XMLHttpRequest for progress tracking
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest();
    const token = getAuthToken();
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://localhost:3000';

    xhr.open('POST', `${baseUrl}/${endpoint}`);
    
    if (token) {
      xhr.setRequestHeader('Authorization', `Bearer ${token}`);
    }

    // Progress tracking
    if (options.onProgress) {
      xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
          const progress = (e.loaded / e.total) * 100;
          options.onProgress?.(progress);
        }
      });
    }

    // Handle completion
    xhr.addEventListener('load', () => {
      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          const response = JSON.parse(xhr.responseText);
          resolve(keysToCamel(response));
        } catch (e) {
          resolve(xhr.responseText);
        }
      } else {
        try {
          const errorData = JSON.parse(xhr.responseText);
          reject(new ApiError(xhr.status, errorData.message || 'Upload failed', errorData));
        } catch (e) {
          reject(new ApiError(xhr.status, 'Upload failed'));
        }
      }
    });

    // Handle errors
    xhr.addEventListener('error', () => {
      reject(new NetworkError('Upload failed due to network error'));
    });

    xhr.addEventListener('abort', () => {
      reject(new Error('Upload cancelled'));
    });

    // Support cancellation
    if (options.signal) {
      options.signal.addEventListener('abort', () => {
        xhr.abort();
      });
    }

    xhr.send(formData);
  });
}

/**
 * Helper to create query string from params
 */
export function buildQueryString(params: Record<string, any>): string {
  const transformed = keysToSnake(params);
  const searchParams = new URLSearchParams();
  
  Object.entries(transformed).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      searchParams.append(key, String(value));
    }
  });
  
  const queryString = searchParams.toString();
  return queryString ? `?${queryString}` : '';
}

/**
 * Helper to convert params object to Record<string, string> for ky searchParams
 * Arrays are converted to comma-separated strings
 * Returns undefined if params is undefined or empty
 */
export function toSearchParams(params?: Record<string, any>): Record<string, string> | undefined {
  if (!params || Object.keys(params).length === 0) {
    return undefined;
  }
  
  const result: Record<string, string> = {};
  
  Object.entries(params).forEach(([key, value]) => {
    if (value !== undefined && value !== null) {
      if (Array.isArray(value)) {
        // Convert arrays to comma-separated strings
        result[key] = value.join(',');
      } else {
        result[key] = String(value);
      }
    }
  });
  
  return Object.keys(result).length > 0 ? result : undefined;
}

export default api;
