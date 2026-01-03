<script lang="ts">
  import { surveyStore } from '$lib/survey/stores/survey.svelte';
  import RatingInput from '$lib/components/survey/RatingInput.svelte';

  interface Props {
    token: string;
    onsubmit?: () => void;
  }

  let { token, onsubmit }: Props = $props();

  let rating = $state(0);
  let feedback = $state('');

  const submitting = $derived(surveyStore.submitting);
  const error = $derived(surveyStore.errorMessage);
  const canSubmit = $derived(rating > 0 && !submitting);

  async function handleSubmit(e: Event) {
    e.preventDefault();
    
    if (!canSubmit) return;

    const success = await surveyStore.submitSurvey(token, {
      rating,
      feedback: feedback.trim() || undefined,
    });

    if (success && onsubmit) {
      onsubmit();
    }
  }
</script>

<form class="survey-form" onsubmit={handleSubmit}>
  <div class="form-section">
    <h2 class="section-title">How would you rate your experience?</h2>
    <RatingInput type="emoji" bind:value={rating} onchange={(v) => (rating = v)} disabled={submitting} />
  </div>

  <div class="form-section">
    <label for="feedback" class="section-label">
      Tell us more about your experience (optional)
    </label>
    <textarea
      id="feedback"
      bind:value={feedback}
      placeholder="Share your thoughts..."
      rows="4"
      class="feedback-textarea"
      disabled={submitting}
    />
  </div>

  {#if error}
    <div class="error-message">{error}</div>
  {/if}

  <button type="submit" class="submit-button" disabled={!canSubmit}>
    {submitting ? 'Submitting...' : 'Submit Feedback'}
  </button>
</form>

<style>
  .survey-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 32px 24px;
  }

  .form-section {
    margin-bottom: 32px;
  }

  .section-title {
    margin: 0 0 16px 0;
    font-size: 24px;
    font-weight: 600;
    color: #1f2937;
    text-align: center;
  }

  .section-label {
    display: block;
    margin-bottom: 12px;
    font-size: 16px;
    font-weight: 500;
    color: #374151;
  }

  .feedback-textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-family: inherit;
    font-size: 15px;
    line-height: 1.5;
    resize: vertical;
    transition: border-color 0.2s ease;
  }

  .feedback-textarea:focus {
    outline: none;
    border-color: #667eea;
  }

  .feedback-textarea:disabled {
    background: #f9fafb;
    color: #9ca3af;
    cursor: not-allowed;
  }

  .error-message {
    padding: 12px;
    margin-bottom: 16px;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    color: #dc2626;
    font-size: 14px;
    text-align: center;
  }

  .submit-button {
    width: 100%;
    padding: 14px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .submit-button:hover:not(:disabled) {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  .submit-button:disabled {
    background: #d1d5db;
    cursor: not-allowed;
    transform: none;
  }

  @media (max-width: 768px) {
    .survey-form {
      padding: 24px 16px;
    }

    .section-title {
      font-size: 20px;
    }
  }
</style>
