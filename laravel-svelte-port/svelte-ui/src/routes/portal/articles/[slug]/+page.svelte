<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import PortalHeader from '$lib/components/portal/PortalHeader.svelte';
  import ArticleViewer from '$lib/components/portal/ArticleViewer.svelte';
  import { portalArticlesStore } from '$lib/portal/stores/articles.svelte';

  const slug = $derived($page.params.slug);
  const article = $derived(portalArticlesStore.selected);
  const loading = $derived(portalArticlesStore.loading);

  onMount(async () => {
    if (slug) {
      await portalArticlesStore.fetchArticle(slug);
    }
  });

  function handleSearch(query: string) {
    goto(`/portal/search?q=${encodeURIComponent(query)}`);
  }

  function handleBack() {
    if (article?.categoryId) {
      goto(`/portal/categories/${article.categoryId}`);
    } else {
      goto('/portal');
    }
  }
</script>

<PortalHeader portalName="Help Center" onsearch={handleSearch} />

<main class="portal-main">
  {#if loading}
    <div class="loading-state">
      <div class="spinner"></div>
      <p>Loading article...</p>
    </div>
  {:else if article}
    <ArticleViewer {article} onback={handleBack} />
  {:else}
    <div class="error-state">
      <h2>Article not found</h2>
      <p>The article you're looking for doesn't exist.</p>
      <a href="/portal" class="back-link">Go back to Help Center</a>
    </div>
  {/if}
</main>

<style>
  .portal-main {
    min-height: 60vh;
  }

  .loading-state,
  .error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
  }

  .spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 16px;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }

  .error-state h2 {
    margin: 0 0 12px 0;
    font-size: 24px;
    color: #1f2937;
  }

  .error-state p {
    margin: 0 0 24px 0;
    font-size: 16px;
    color: #6b7280;
  }

  .back-link {
    padding: 10px 20px;
    background: #667eea;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: background 0.2s ease;
  }

  .back-link:hover {
    background: #5568d3;
  }
</style>
