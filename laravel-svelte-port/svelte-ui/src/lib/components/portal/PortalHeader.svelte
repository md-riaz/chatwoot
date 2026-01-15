<script lang="ts">
  import { Search } from 'lucide-svelte';
  import type { Snippet } from 'svelte';

  interface Props {
    portalName: string;
    searchQuery?: string;
    onsearch?: (query: string) => void;
    children?: Snippet;
  }

  let { portalName, searchQuery = '', onsearch, children }: Props = $props();

  let localQuery = $state('');
  
  // Initialize localQuery when searchQuery prop changes
  $effect(() => {
    localQuery = searchQuery;
  });

  function handleSearch(e: Event) {
    e.preventDefault();
    if (onsearch && localQuery.trim()) {
      onsearch(localQuery.trim());
    }
  }
</script>

<header class="portal-header">
  <div class="header-container">
    <div class="header-content">
      <h1 class="portal-title">{portalName}</h1>
      
      <form class="search-form" onsubmit={handleSearch}>
        <div class="search-input-wrapper">
          <Search class="search-icon" size={20} />
          <input
            type="text"
            bind:value={localQuery}
            placeholder="Search for help..."
            class="search-input"
          />
        </div>
      </form>

      {#if children}
        <div class="header-actions">
          {@render children()}
        </div>
      {/if}
    </div>
  </div>
</header>

<style>
  .portal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 48px 24px;
  }

  .header-container {
    max-width: 1200px;
    margin: 0 auto;
  }

  .header-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
  }

  .portal-title {
    margin: 0;
    font-size: 32px;
    font-weight: 700;
    text-align: center;
  }

  .search-form {
    width: 100%;
    max-width: 600px;
  }

  .search-input-wrapper {
    position: relative;
    width: 100%;
  }

  :global(.search-icon) {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
  }

  .search-input {
    width: 100%;
    padding: 14px 16px 14px 48px;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-family: inherit;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s ease;
  }

  .search-input:focus {
    outline: none;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
  }

  .header-actions {
    display: flex;
    gap: 12px;
  }

  @media (max-width: 768px) {
    .portal-header {
      padding: 32px 16px;
    }

    .portal-title {
      font-size: 24px;
    }

    .search-input {
      font-size: 14px;
    }
  }
</style>
