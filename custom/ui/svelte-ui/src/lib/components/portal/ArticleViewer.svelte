<script lang="ts">
  import type { Article } from '$lib/portal/api/types';
  import { portalArticlesStore } from '$lib/portal/stores/articles.svelte';
  import { ArrowLeft, ThumbsUp, ThumbsDown, Calendar, Eye } from 'lucide-svelte';

  interface Props {
    article: Article;
    onback?: () => void;
  }

  let { article, onback }: Props = $props();

  let feedbackGiven = $state<'helpful' | 'not_helpful' | null>(null);

  function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric',
    });
  }

  async function handleFeedback(isHelpful: boolean) {
    feedbackGiven = isHelpful ? 'helpful' : 'not_helpful';
    await portalArticlesStore.submitFeedback(article.id, isHelpful);
  }

  const updatedDate = $derived(formatDate(article.updatedAt));
</script>

<svelte:head>
  <title>{article.title} - Help Center</title>
  <meta name="description" content={article.description} />
  {#if article.meta.keywords}
    <meta name="keywords" content={article.meta.keywords.join(', ')} />
  {/if}
  {#if article.meta.ogImage}
    <meta property="og:image" content={article.meta.ogImage} />
  {/if}
</svelte:head>

<article class="article-viewer">
  {#if onback}
    <button class="back-button" onclick={onback}>
      <ArrowLeft size={20} />
      Back to articles
    </button>
  {/if}

  <header class="article-header">
    <h1 class="article-title">{article.title}</h1>
    <div class="article-meta">
      <span class="meta-item">
        <Calendar size={16} />
        Updated {updatedDate}
      </span>
      {#if article.views > 0}
        <span class="meta-item">
          <Eye size={16} />
          {article.views} {article.views === 1 ? 'view' : 'views'}
        </span>
      {/if}
    </div>
  </header>

  <div class="article-content">
    {@html article.content}
  </div>

  <footer class="article-footer">
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
  </footer>
</article>

<style>
  .article-viewer {
    max-width: 800px;
    margin: 0 auto;
    padding: 32px 24px;
  }

  .back-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    margin-bottom: 24px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .back-button:hover {
    background: #f9fafb;
    border-color: #d1d5db;
  }

  .article-header {
    margin-bottom: 32px;
  }

  .article-title {
    margin: 0 0 16px 0;
    font-size: 36px;
    font-weight: 700;
    color: #1f2937;
    line-height: 1.2;
  }

  .article-meta {
    display: flex;
    gap: 20px;
    font-size: 14px;
    color: #6b7280;
  }

  .meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .article-content {
    font-size: 16px;
    line-height: 1.8;
    color: #374151;
    margin-bottom: 48px;
  }

  .article-content :global(h2) {
    font-size: 28px;
    font-weight: 600;
    margin: 32px 0 16px 0;
    color: #1f2937;
  }

  .article-content :global(h3) {
    font-size: 22px;
    font-weight: 600;
    margin: 24px 0 12px 0;
    color: #1f2937;
  }

  .article-content :global(p) {
    margin: 0 0 16px 0;
  }

  .article-content :global(a) {
    color: #667eea;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 0.2s ease;
  }

  .article-content :global(a:hover) {
    border-bottom-color: #667eea;
  }

  .article-content :global(ul),
  .article-content :global(ol) {
    margin: 0 0 16px 0;
    padding-left: 28px;
  }

  .article-content :global(li) {
    margin-bottom: 8px;
  }

  .article-content :global(code) {
    background: #f3f4f6;
    padding: 3px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    color: #e83e8c;
  }

  .article-content :global(pre) {
    background: #1f2937;
    color: #f3f4f6;
    padding: 20px;
    border-radius: 8px;
    overflow-x: auto;
    margin: 0 0 16px 0;
  }

  .article-content :global(pre code) {
    background: none;
    padding: 0;
    color: inherit;
  }

  .article-content :global(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin: 24px 0;
  }

  .article-content :global(blockquote) {
    border-left: 4px solid #667eea;
    padding: 12px 20px;
    margin: 20px 0;
    background: #f9fafb;
    border-radius: 4px;
  }

  .article-footer {
    padding: 32px 0;
    border-top: 1px solid #e5e7eb;
  }

  .feedback-section {
    text-align: center;
  }

  .feedback-question {
    margin: 0 0 16px 0;
    font-size: 16px;
    font-weight: 600;
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
    gap: 8px;
    padding: 10px 20px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .feedback-button:hover:not(:disabled) {
    border-color: #667eea;
    color: #667eea;
  }

  .feedback-button.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
  }

  .feedback-button:disabled {
    cursor: not-allowed;
    opacity: 0.6;
  }

  .feedback-thanks {
    margin: 16px 0 0 0;
    font-size: 14px;
    color: #10b981;
    font-weight: 500;
  }

  @media (max-width: 768px) {
    .article-viewer {
      padding: 24px 16px;
    }

    .article-title {
      font-size: 28px;
    }

    .article-content {
      font-size: 15px;
    }

    .article-content :global(h2) {
      font-size: 24px;
    }

    .article-content :global(h3) {
      font-size: 20px;
    }

    .article-meta {
      flex-direction: column;
      gap: 8px;
    }
  }
</style>
