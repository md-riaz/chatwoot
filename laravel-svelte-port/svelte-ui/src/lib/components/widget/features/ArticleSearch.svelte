<script lang="ts">
  import { Search } from 'lucide-svelte';
  import * as articleApi from '$lib/widget/api/article';
  import type { Article } from '$lib/widget/api/types';
  import ArticleCard from './ArticleCard.svelte';

  interface Props {
    onselect?: (article: Article) => void;
  }

  let { onselect }: Props = $props();

  let searchQuery = $state('');
  let articles = $state<Article[]>([]);
  let isSearching = $state(false);
  let searchTimeout: number | null = null;

  // Debounced search
  $effect(() => {
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    if (searchQuery.trim().length >= 2) {
      searchTimeout = window.setTimeout(async () => {
        await performSearch();
      }, 300);
    } else {
      articles = [];
    }

    return () => {
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }
    };
  });

  async function performSearch() {
    if (!searchQuery.trim()) return;

    isSearching = true;

    try {
      const results = await articleApi.searchArticles(searchQuery.trim());
      articles = results;
    } catch (err) {
      console.error('Failed to search articles:', err);
      articles = [];
    } finally {
      isSearching = false;
    }
  }

  function handleSelect(article: Article) {
    if (onselect) {
      onselect(article);
    }
  }
</script>

<div class="article-search">
  <div class="search-header">
    <h3>Help Articles</h3>
    <p>Search our knowledge base</p>
  </div>

  <div class="search-input-container">
    <Search size={18} class="search-icon" />
    <input
      type="text"
      bind:value={searchQuery}
      placeholder="Search for help..."
      class="search-input"
    />
  </div>

  <div class="search-results">
    {#if isSearching}
      <div class="loading-state">
        <div class="spinner"></div>
        <p>Searching...</p>
      </div>
    {:else if searchQuery.trim().length >= 2 && articles.length === 0}
      <div class="empty-state">
        <p>No articles found</p>
      </div>
    {:else if articles.length > 0}
      <div class="articles-list">
        {#each articles as article (article.id)}
          <ArticleCard {article} onclick={() => handleSelect(article)} />
        {/each}
      </div>
    {:else}
      <div class="placeholder-state">
        <p>Type to search for articles</p>
      </div>
    {/if}
  </div>
</div>

<style>
  .article-search {
    display: flex;
    flex-direction: column;
    height: 100%;
    background: #f9fafb;
  }

  .search-header {
    padding: 24px 20px 16px;
    background: white;
    border-bottom: 1px solid #e5e7eb;
  }

  .search-header h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
  }

  .search-header p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
  }

  .search-input-container {
    position: relative;
    padding: 16px;
    background: white;
    border-bottom: 1px solid #e5e7eb;
  }

  :global(.search-icon) {
    position: absolute;
    left: 28px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
  }

  .search-input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.2s ease;
  }

  .search-input:focus {
    outline: none;
    border-color: var(--widget-color, #1f93ff);
  }

  .search-results {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
  }

  .loading-state,
  .empty-state,
  .placeholder-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
    color: #6b7280;
    font-size: 14px;
  }

  .spinner {
    width: 32px;
    height: 32px;
    border: 3px solid #e5e7eb;
    border-top-color: var(--widget-color, #1f93ff);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 12px;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  .articles-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .search-results::-webkit-scrollbar {
    width: 6px;
  }

  .search-results::-webkit-scrollbar-track {
    background: transparent;
  }

  .search-results::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
  }
</style>
