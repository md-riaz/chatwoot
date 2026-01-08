<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import PortalHeader from '$lib/components/portal/PortalHeader.svelte';
  import ArticleCard from '$lib/components/portal/ArticleCard.svelte';
  import { portalCategoriesStore } from '$lib/portal/stores/categories.svelte';
  import { portalArticlesStore } from '$lib/portal/stores/articles.svelte';
  import { ChevronRight } from 'lucide-svelte';

  const slug = $derived($page.params.slug);
  const category = $derived(portalCategoriesStore.selected);
  const articles = $derived(portalArticlesStore.allArticles);
  const loading = $derived(portalArticlesStore.loading);

  onMount(async () => {
    if (slug) {
      await portalCategoriesStore.fetchCategory(slug);
      await portalArticlesStore.fetchArticlesByCategory(slug);
    }
  });

  function handleSearch(query: string) {
    goto(`/portal/search?q=${encodeURIComponent(query)}`);
  }

  function handleArticleClick(articleSlug: string) {
    goto(`/portal/articles/${articleSlug}`);
  }
</script>

<svelte:head>
  <title>{category?.name || 'Category'} - Help Center</title>
  {#if category?.description}
    <meta name="description" content={category.description} />
  {/if}
</svelte:head>

<PortalHeader portalName="Help Center" onsearch={handleSearch} />

<main class="portal-main">
  <div class="container">
    <nav class="breadcrumb">
      <a href="/portal" class="breadcrumb-link">Home</a>
      <ChevronRight size={16} class="breadcrumb-separator" />
      <span class="breadcrumb-current">{category?.name || 'Category'}</span>
    </nav>

    {#if category}
      <header class="category-header">
        <h1 class="category-title">{category.name}</h1>
        {#if category.description}
          <p class="category-description">{category.description}</p>
        {/if}
      </header>
    {/if}

    {#if loading}
      <div class="loading-state">
        <div class="spinner"></div>
        <p>Loading articles...</p>
      </div>
    {:else if articles.length === 0}
      <div class="empty-state">
        <p>No articles found in this category</p>
      </div>
    {:else}
      <div class="articles-list">
        {#each articles as article (article.id)}
          <ArticleCard {article} onclick={() => handleArticleClick(article.slug)} />
        {/each}
      </div>
    {/if}
  </div>
</main>

<style>
  .portal-main {
    padding: 48px 24px;
  }

  .container {
    max-width: 900px;
    margin: 0 auto;
  }

  .breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    color: #6b7280;
  }

  .breadcrumb-link {
    color: #667eea;
    text-decoration: none;
    transition: color 0.2s ease;
  }

  .breadcrumb-link:hover {
    color: #5568d3;
  }

  .breadcrumb-current {
    color: #1f2937;
    font-weight: 500;
  }

  .category-header {
    margin-bottom: 32px;
  }

  .category-title {
    margin: 0 0 12px 0;
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
  }

  .category-description {
    margin: 0;
    font-size: 16px;
    color: #6b7280;
    line-height: 1.6;
  }

  .articles-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .loading-state,
  .empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
    color: #6b7280;
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

  @media (max-width: 768px) {
    .portal-main {
      padding: 32px 16px;
    }

    .category-title {
      font-size: 24px;
    }

    .category-description {
      font-size: 14px;
    }
  }
</style>
