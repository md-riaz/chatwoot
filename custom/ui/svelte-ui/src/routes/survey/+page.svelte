<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { surveyStore } from '$lib/survey/stores/survey.svelte';
  import SurveyForm from '$lib/components/survey/SurveyForm.svelte';
  import { Loader2 } from 'lucide-svelte';

  const token = $derived($page.url.searchParams.get('token') || '');
  const survey = $derived(surveyStore.currentSurvey);
  const loading = $derived(surveyStore.loading);
  const submitted = $derived(surveyStore.submitted);
  const error = $derived(surveyStore.errorMessage);

  onMount(async () => {
    if (!token) {
      goto('/survey/invalid');
      return;
    }

    await surveyStore.fetchSurvey(token);

    // Redirect if already submitted
    if (surveyStore.submitted) {
      goto('/survey/thank-you');
    }
  });

  function handleSubmit() {
    goto('/survey/thank-you');
  }
</script>

<svelte:head>
  <title>Customer Satisfaction Survey</title>
  <meta name="description" content="We'd love to hear your feedback" />
</svelte:head>

<div class="survey-page">
  <div class="survey-card">
    {#if loading}
      <div class="loading-state">
        <Loader2 size={48} class="spinner" />
        <p>Loading survey...</p>
      </div>
    {:else if error}
      <div class="error-state">
        <h2>Oops!</h2>
        <p>{error}</p>
        <a href="/" class="home-link">Go to Home</a>
      </div>
    {:else if survey}
      <div class="survey-header">
        <h1>We'd love your feedback!</h1>
        <p>Your opinion helps us improve our service</p>
      </div>

      <SurveyForm {token} onsubmit={handleSubmit} />
    {:else}
      <div class="error-state">
        <h2>Survey Not Found</h2>
        <p>This survey link may be invalid or expired.</p>
        <a href="/" class="home-link">Go to Home</a>
      </div>
    {/if}
  </div>
</div>

<style>
  .survey-page {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
  }

  .survey-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }

  .survey-header {
    padding: 40px 24px 24px;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }

  .survey-header h1 {
    margin: 0 0 12px 0;
    font-size: 32px;
    font-weight: 700;
  }

  .survey-header p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
  }

  .loading-state,
  .error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 24px;
    text-align: center;
  }

  .loading-state p,
  .error-state p {
    margin: 16px 0 0 0;
    font-size: 16px;
    color: #6b7280;
  }

  .error-state h2 {
    margin: 0 0 12px 0;
    font-size: 24px;
    color: #1f2937;
  }

  .home-link {
    margin-top: 24px;
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: background 0.2s ease;
  }

  .home-link:hover {
    background: #5568d3;
  }

  :global(.spinner) {
    animation: spin 1s linear infinite;
    color: #667eea;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  @media (max-width: 768px) {
    .survey-header h1 {
      font-size: 24px;
    }

    .survey-header p {
      font-size: 14px;
    }
  }
</style>
