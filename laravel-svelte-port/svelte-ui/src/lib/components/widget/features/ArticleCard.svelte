<script lang="ts">
  import type { Article } from '$lib/widget/api/types';
  import { FileText } from 'lucide-svelte';

  interface Props {
    article: Article;
    onclick?: () => void;
  }

  let { article, onclick }: Props = $props();

  function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
    });
  }

  const updatedDate = $derived(formatDate(article.updatedAt));
</script>

<button class="article-card" {onclick}>
  <div class="article-icon">
    <FileText size={20} />
  </div>

  <div class="article-content">
    <h4 class="article-title">{article.title}</h4>
    {#if article.description}
      <p class="article-description">{article.description}</p>
    {/if}
    <div class="article-meta">
      <span class="meta-item">Updated {updatedDate}</span>
      {#if article.views}
        <span class="meta-item">{article.views} views</span>
      {/if}
    </div>
  </div>
</button>

<style>
  .article-card {
    display: flex;
    gap: 12px;
    padding: 14px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
    width: 100%;
  }

  .article-card:hover {
    border-color: var(--widget-color, #1f93ff);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }

  .article-icon {
    width: 40px;
    height: 40px;
    background: #f3f4f6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
    flex-shrink: 0;
  }

  .article-content {
    flex: 1;
    min-width: 0;
  }

  .article-title {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  .article-description {
    margin: 0 0 8px 0;
    font-size: 13px;
    color: #6b7280;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  .article-meta {
    display: flex;
    gap: 12px;
    font-size: 11px;
    color: #9ca3af;
  }

  .meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
  }
</style>
