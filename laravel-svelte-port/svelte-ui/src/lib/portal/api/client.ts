/**
 * Portal API Client
 * 
 * HTTP client for portal (help center) public API endpoints.
 */

import ky, { type KyInstance } from 'ky';
import { transformKeysTo } from '$lib/api/transformers';

let portalApiInstance: KyInstance | null = null;

/**
 * Get portal API client
 */
export function getPortalApi(portalSlug?: string): KyInstance {
  if (portalApiInstance) {
    return portalApiInstance;
  }

  const baseUrl = import.meta.env.VITE_PORTAL_API_URL || 'http://localhost:3000/hc';
  const prefix = portalSlug ? `${baseUrl}/${portalSlug}` : baseUrl;

  portalApiInstance = ky.create({
    prefixUrl: prefix,
    timeout: 30000,
    headers: {
      'Content-Type': 'application/json',
    },
    hooks: {
      beforeRequest: [
        (request) => {
          // Transform request body to snake_case
          if (request.body && request.method !== 'GET' && request.method !== 'HEAD') {
            const contentType = request.headers.get('content-type');

            if (!contentType || contentType.includes('application/json')) {
              try {
                // Read body as text first since it could be a ReadableStream
                const bodyContent = typeof request.body === 'string' 
                  ? request.body 
                  : request.body.toString();
                const data = JSON.parse(bodyContent);
                const transformed = transformKeysTo(data, 'snake');
                // Return a new Request with the transformed body
                return new Request(request, {
                  body: JSON.stringify(transformed)
                });
              } catch (e) {
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
              const data = await response.json() as { message?: string };
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

  return portalApiInstance;
}

/**
 * Reset portal API client
 */
export function resetPortalApi() {
  portalApiInstance = null;
}

export default getPortalApi;
