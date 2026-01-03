/**
 * Survey API
 * 
 * API methods for survey operations.
 */

import { getSurveyApi } from './client';
import type { Survey, SurveySubmission, SurveyResponse, ApiResponse } from './types';

/**
 * Get survey by token
 */
export async function getSurvey(token: string): Promise<Survey> {
  const api = getSurveyApi();
  const response = await api.get(`surveys/${token}`).json<ApiResponse<Survey>>();
  return response.data;
}

/**
 * Submit survey response
 */
export async function submitSurvey(
  token: string,
  submission: SurveySubmission
): Promise<SurveyResponse> {
  const api = getSurveyApi();
  return await api.post(`surveys/${token}`, { json: submission }).json<SurveyResponse>();
}
