<script lang="ts">
  import type { Article } from '$lib/widget/api/types';
  import { ArrowLeft, ThumbsUp, ThumbsDown } from 'lucide-svelte';

  interface Props {
    article: Article;
    onback?: () => void;
  }

  let { article, onback }: Props = $props();

  let feedbackGiven = $state<'helpful' | 'not_helpful' | null>(null);

  function handleFeedback(isHelpful: boolean) {
    feedbackGiven = isHelpful ? 'helpful' : 'not_helpful';
    // In production, this would send feedback to the API
    console.log('Article feedback:', { articleId: article.id, isHelpful });
  }
</script>

<div class="article-viewer">
  <div class="article-header">
    {#if onback}
      <button class="back-button" onclick={onback} aria-label="Go back">
        <ArrowLeft size={20} />
      </button>
    {/if}
    <h2 class="article-title">{article.title}</h2>
  </div>

  <div class="article-content">
    {@html article.content}
  </div>

  <div class="article-footer">
    <div class="feedback-section">
      <p class="feedback-question">Was this article helpful?</p>
      <div class="feedback-buttons">
        <button
          class="feedback-button"
          class:active={feedbackGiven === 'helpful'}
          onclick={() => handleFeedback(true)}
          disabled={feedbackGiven !== null}
        >
          <ThumbsUp size={18} />
          Yes
        </button>
        <button
          class="feedback-button"
          class:active={feedbackGiven === 'not_helpful'}
          onclick={() => handleFeedback(false)}
          disabled={feedbackGiven !== null}
        >
          <ThumbsDown size={18} />
          No
        </button>
      </div>
      {#if feedbackGiven}
        <p class="feedback-thanks">Thanks for your feedback!</p>
      {/if}
    </div>
  </div>
</div>

<style>
  .article-viewer {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: white;
  }

  .article-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
  }

  .back-button {
    background: none;
    border: none;
    padding: 8px;
    cursor: pointer;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s ease;
  }

  .back-button:hover {
    background: #f3f4f6;
    color: #374151;
  }

  .article-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    flex: 1;
  }

  .article-content {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    line-height: 1.6;
    color: #374151;
  }

  .article-content::-webkit-scrollbar {
    width: 6px;
  }

  .article-content::-webkit-scrollbar-track {
    background: transparent;
  }

  .article-content::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
  }

  .article-content :global(h1) {
    font-size: 24px;
    font-weight: 600;
    margin: 0 0 16px 0;
  }

  .article-content :global(h2) {
    font-size: 20px;
    font-weight: 600;
    margin: 24px 0 12px 0;
  }

  .article-content :global(h3) {
    font-size: 16px;
    font-weight: 600;
    margin: 20px 0 8px 0;
  }

  .article-content :global(p) {
    margin: 0 0 16px 0;
  }

  .article-content :global(a) {
    color: var(--widget-color, #1f93ff);
    text-decoration: none;
  }

  .article-content :global(a:hover) {
    text-decoration: underline;
  }

  .article-content :global(ul),
  .article-content :global(ol) {
    margin: 0 0 16px 0;
    padding-left: 24px;
  }

  .article-content :global(li) {
    margin-bottom: 8px;
  }

  .article-content :global(code) {
    background: #f3f4f6;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
  }

  .article-content :global(pre) {
    background: #f3f4f6;
    padding: 12px;
    border-radius: 6px;
    overflow-x: auto;
    margin: 0 0 16px 0;
  }

  .article-content :global(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 16px 0;
  }

  .article-footer {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
  }

  .feedback-section {
    text-align: center;
  }

  .feedback-question {
    margin: 0 0 12px 0;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
  }

  .feedback-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
  }

  .feedback-button {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .feedback-button:hover:not(:disabled) {
    border-color: var(--widget-color, #1f93ff);
    color: var(--widget-color, #1f93ff);
  }

  .feedback-button.active {
    background: var(--widget-color, #1f93ff);
    border-color: var(--widget-color, #1f93ff);
    color: white;
  }

  .feedback-button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
  }

  .feedback-thanks {
    margin: 12px 0 0 0;
    font-size: 13px;
    color: #10b981;
  }
</style>
