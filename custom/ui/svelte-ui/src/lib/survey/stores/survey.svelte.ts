/**
 * Survey Store
 * 
 * Manages survey state.
 */

import * as surveyApi from '../api/survey';
import type { Survey, SurveySubmission } from '../api/types';

class SurveyStore {
  private survey = $state<Survey | null>(null);
  private isLoading = $state(false);
  private isSubmitting = $state(false);
  private isSubmitted = $state(false);
  private error = $state<string | null>(null);

  // Getters
  get currentSurvey() {
    return this.survey;
  }

  get loading() {
    return this.isLoading;
  }

  get submitting() {
    return this.isSubmitting;
  }

  get submitted() {
    return this.isSubmitted;
  }

  get errorMessage() {
    return this.error;
  }

  // Derived values
  get isExpired() {
    return $derived(() => {
      if (!this.survey) return false;
      const expiryDate = new Date(this.survey.expiresAt);
      return expiryDate < new Date();
    });
  }

  get isPending() {
    return $derived(this.survey?.status === 'pending');
  }

  // Actions
  async fetchSurvey(token: string): Promise<void> {
    this.isLoading = true;
    this.error = null;

    try {
      const survey = await surveyApi.getSurvey(token);
      this.survey = survey;

      // Check if already submitted or expired
      if (survey.status === 'submitted') {
        this.isSubmitted = true;
      } else if (survey.status === 'expired' || this.isExpired()) {
        this.error = 'This survey has expired';
      }
    } catch (err: any) {
      this.error = err.message || 'Failed to load survey';
    } finally {
      this.isLoading = false;
    }
  }

  async submitSurvey(token: string, submission: SurveySubmission): Promise<boolean> {
    this.isSubmitting = true;
    this.error = null;

    try {
      const response = await surveyApi.submitSurvey(token, submission);
      
      if (response.success) {
        this.isSubmitted = true;
        if (this.survey) {
          this.survey = {
            ...this.survey,
            status: 'submitted',
            rating: submission.rating,
            feedback: submission.feedback,
            submittedAt: new Date().toISOString(),
          };
        }
        return true;
      } else {
        this.error = response.message || 'Failed to submit survey';
        return false;
      }
    } catch (err: any) {
      this.error = err.message || 'Failed to submit survey';
      return false;
    } finally {
      this.isSubmitting = false;
    }
  }

  reset() {
    this.survey = null;
    this.isLoading = false;
    this.isSubmitting = false;
    this.isSubmitted = false;
    this.error = null;
  }
}

export const surveyStore = new SurveyStore();
