/**
 * Survey API Client
 * 
 * HTTP client for survey (CSAT) public API endpoints.
 */

import ky, { type KyInstance } from 'ky';
import { transformKeys } from '$lib/api/transformers';

let surveyApiInstance: KyInstance | null = null;

/**
 * Get survey API client
 */
export function getSurveyApi(): KyInstance {
  if (surveyApiInstance) {
    return surveyApiInstance;
  }

  surveyApiInstance = ky.create({
    prefixUrl: import.meta.env.VITE_SURVEY_API_URL || 'http://localhost:3000/public/api/v1',
    timeout: 30000,
    headers: {
      'Content-Type': 'application/json',
    },
    hooks: {
      beforeRequest: [
        (request) => {
          // Transform request body to snake_case
          if (request.body && request.method !== 'GET') {
            const contentType = request.headers.get('content-type');

            if (contentType?.includes('application/json')) {
              try {
                const data = JSON.parse(request.body as string);
                const transformed = transformKeys(data, 'snake');
                request.body = JSON.stringify(transformed);
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
                const transformed = transformKeys(data, 'camel');
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

  return surveyApiInstance;
}

/**
 * Reset survey API client
 */
export function resetSurveyApi() {
  surveyApiInstance = null;
}

export default getSurveyApi;
