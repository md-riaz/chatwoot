<script lang="ts">
  import type { Category } from '$lib/portal/api/types';
  import { FolderOpen } from 'lucide-svelte';

  interface Props {
    category: Category;
    onclick?: () => void;
  }

  let { category, onclick }: Props = $props();
</script>

<button class="category-card" {onclick}>
  <div class="category-icon">
    {#if category.icon}
      <span class="icon-emoji">{category.icon}</span>
    {:else}
      <FolderOpen size={24} />
    {/if}
  </div>

  <div class="category-content">
    <h3 class="category-name">{category.name}</h3>
    {#if category.description}
      <p class="category-description">{category.description}</p>
    {/if}
    <div class="category-meta">
      {category.articleCount} {category.articleCount === 1 ? 'article' : 'articles'}
    </div>
  </div>
</button>

<style>
  .category-card {
    display: flex;
    gap: 16px;
    padding: 20px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    text-align: left;
    transition: all 0.2s ease;
    width: 100%;
  }

  .category-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
  }

  .category-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
  }

  .icon-emoji {
    font-size: 28px;
  }

  .category-content {
    flex: 1;
    min-width: 0;
  }

  .category-name {
    margin: 0 0 6px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
  }

  .category-description {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #6b7280;
    line-height: 1.5;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
  }

  .category-meta {
    font-size: 13px;
    color: #9ca3af;
  }

  @media (max-width: 768px) {
    .category-card {
      padding: 16px;
    }

    .category-icon {
      width: 48px;
      height: 48px;
    }

    .icon-emoji {
      font-size: 24px;
    }

    .category-name {
      font-size: 16px;
    }
  }
</style>
