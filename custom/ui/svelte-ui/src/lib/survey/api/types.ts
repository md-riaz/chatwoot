/**
 * Survey API Types
 * 
 * TypeScript interfaces for the survey (CSAT) API.
 */

export interface Survey {
  id: number;
  accountId: number;
  conversationId: number;
  contactId: number;
  rating?: number;
  feedback?: string;
  status: 'pending' | 'submitted' | 'expired';
  expiresAt: string;
  submittedAt?: string;
  createdAt: string;
}

export interface SurveySubmission {
  rating: number;
  feedback?: string;
}

export interface SurveyResponse {
  success: boolean;
  message?: string;
}

export interface ApiResponse<T> {
  data: T;
}
