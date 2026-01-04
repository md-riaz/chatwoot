/**
 * Widget API Client
 * 
 * HTTP client for widget public API endpoints.
 * Uses website token for authentication instead of user auth tokens.
 */

import ky, { type KyInstance } from 'ky';
import { transformKeysTo } from '$lib/api/transformers';

let widgetApiInstance: KyInstance | null = null;
let currentWebsiteToken: string | null = null;

/**
 * Initialize or get widget API client
 */
export function getWidgetApi(websiteToken?: string): KyInstance {
  // Update token if provided
  if (websiteToken) {
    currentWebsiteToken = websiteToken;
  }

  // Return existing instance if token hasn't changed
  if (widgetApiInstance && websiteToken === currentWebsiteToken) {
    return widgetApiInstance;
  }

  // Create new instance
  widgetApiInstance = ky.create({
    prefixUrl: import.meta.env.VITE_WIDGET_API_URL || 'http://localhost:3000/public/api/v1',
    timeout: 30000,
    headers: {
      'Content-Type': 'application/json',
      ...(currentWebsiteToken && { 'X-Website-Token': currentWebsiteToken }),
    },
    hooks: {
      beforeRequest: [
        (request) => {
          // Add website token to headers
          if (currentWebsiteToken) {
            request.headers.set('X-Website-Token', currentWebsiteToken);
          }

          // Transform request body to snake_case
          if (request.body && request.method !== 'GET') {
            const contentType = request.headers.get('content-type');
            
            // Only transform JSON payloads (not FormData for file uploads)
            if (contentType?.includes('application/json')) {
              try {
                const data = JSON.parse(request.body as string);
                const transformed = transformKeysTo(data, 'snake');
                request.body = JSON.stringify(transformed);
              } catch (e) {
                // If parsing fails, leave body as is
                console.warn('Failed to parse request body for transformation:', e);
              }
            }
          }
        },
      ],
      afterResponse: [
        async (_request, _options, response) => {
          // Transform response to camelCase
          if (response.ok) {
            const contentType = response.headers.get('content-type');
            if (contentType?.includes('application/json')) {
              try {
                const data = await response.clone().json();
                const transformed = transformKeysTo(data, 'camel');
                return new Response(JSON.stringify(transformed), {
                  status: response.status,
                  statusText: response.statusText,
                  headers: response.headers,
                });
              } catch (e) {
                // If transformation fails, return original response
                return response;
              }
            }
          }
          return response;
        },
      ],
      beforeError: [
        async (error) => {
          const { response } = error;
          if (response) {
            try {
              const data = await response.json();
              error.message = data.message || `API Error: ${response.status}`;
            } catch (e) {
              error.message = `API Error: ${response.status}`;
            }
          }
          return error;
        },
      ],
    },
  });

  return widgetApiInstance;
}

/**
 * Reset widget API client (useful for testing)
 */
export function resetWidgetApi() {
  widgetApiInstance = null;
  currentWebsiteToken = null;
}

/**
 * Set website token for subsequent API calls
 */
export function setWebsiteToken(token: string) {
  currentWebsiteToken = token;
  // Force recreation of API instance with new token
  widgetApiInstance = null;
}

export default getWidgetApi;
