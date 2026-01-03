<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import PortalHeader from '$lib/components/portal/PortalHeader.svelte';
  import CategoryCard from '$lib/components/portal/CategoryCard.svelte';
  import { portalCategoriesStore } from '$lib/portal/stores/categories.svelte';

  const categories = $derived(portalCategoriesStore.topLevelCategories);
  const loading = $derived(portalCategoriesStore.loading);

  onMount(async () => {
    await portalCategoriesStore.fetchCategories();
  });

  function handleSearch(query: string) {
    goto(`/portal/search?q=${encodeURIComponent(query)}`);
  }

  function handleCategoryClick(categorySlug: string) {
    goto(`/portal/categories/${categorySlug}`);
  }
</script>

<svelte:head>
  <title>Help Center</title>
  <meta name="description" content="Find answers to your questions" />
</svelte:head>

<PortalHeader portalName="Help Center" onsearch={handleSearch} />

<main class="portal-main">
  <div class="container">
    <h2 class="section-title">Browse by Category</h2>

    {#if loading}
      <div class="loading-state">
        <div class="spinner"></div>
        <p>Loading categories...</p>
      </div>
    {:else if categories.length === 0}
      <div class="empty-state">
        <p>No categories found</p>
      </div>
    {:else}
      <div class="categories-grid">
        {#each categories as category (category.id)}
          <CategoryCard {category} onclick={() => handleCategoryClick(category.slug)} />
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
    max-width: 1200px;
    margin: 0 auto;
  }

  .section-title {
    margin: 0 0 32px 0;
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    text-align: center;
  }

  .categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
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

    .section-title {
      font-size: 24px;
      margin-bottom: 24px;
    }

    .categories-grid {
      grid-template-columns: 1fr;
      gap: 16px;
    }
  }
</style>
