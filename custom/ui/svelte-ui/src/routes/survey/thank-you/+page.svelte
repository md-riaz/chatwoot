<script lang="ts">
  import { CheckCircle } from 'lucide-svelte';
</script>

<svelte:head>
  <title>Thank You - Survey Submitted</title>
</svelte:head>

<div class="thank-you-page">
  <div class="thank-you-card">
    <div class="icon-wrapper">
      <CheckCircle size={64} class="check-icon" />
    </div>

    <h1>Thank you for your feedback!</h1>
    <p>
      We appreciate you taking the time to share your experience with us.
      Your feedback helps us improve our service.
    </p>

    <div class="actions">
      <a href="/" class="home-button">Go to Home</a>
    </div>
  </div>
</div>

<style>
  .thank-you-page {
    width: 100%;
    max-width: 600px;
    margin: 0 auto;
  }

  .thank-you-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    padding: 60px 40px;
    text-align: center;
  }

  .icon-wrapper {
    margin-bottom: 24px;
  }

  :global(.check-icon) {
    color: #22c55e;
  }

  .thank-you-card h1 {
    margin: 0 0 16px 0;
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
  }

  .thank-you-card p {
    margin: 0;
    font-size: 16px;
    color: #6b7280;
    line-height: 1.6;
  }

  .actions {
    margin-top: 32px;
  }

  .home-button {
    display: inline-block;
    padding: 12px 32px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.2s ease;
  }

  .home-button:hover {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
  }

  @media (max-width: 768px) {
    .thank-you-card {
      padding: 40px 24px;
    }

    .thank-you-card h1 {
      font-size: 24px;
    }

    .thank-you-card p {
      font-size: 15px;
    }
  }
</style>
