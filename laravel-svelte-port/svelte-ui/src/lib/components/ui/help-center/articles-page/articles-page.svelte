<script lang="ts">
  import { Button } from '../../../button/index.js';
  import { Input } from '../../../input/index.js';
  import { Badge } from '../../../badge/index.js';
  import { Card } from '../../../card/index.js';
  import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../../../select/index.js';
  import { Search, Plus, Edit2, Trash2, Eye, FileText } from 'lucide-svelte';

  export let articles: Article[] = [];
  export let categories: Category[] = [];
  export let searchQuery: string = '';
  export let selectedCategory: string = 'all';
  export let sortBy: 'date' | 'popularity' | 'title' = 'date';
  export let currentPage: number = 1;
  export let itemsPerPage: number = 10;
  export let onArticleSelect: (id: string) => void = () => {};
  export let onArticleCreate: () => void = () => {};
  export let onArticleEdit: (id: string) => void = () => {};
  export let onArticleDelete: (id: string) => void = () => {};
  export let onArticlePreview: (id: string) => void = () => {};

  interface Article {
    id: string;
    title: string;
    description: string;
    category: string;
    author: string;
    status: 'published' | 'draft' | 'archived';
    views: number;
    createdAt: string;
    updatedAt: string;
  }

  interface Category {
    id: string;
    name: string;
    slug: string;
    count: number;
  }

  $: filteredArticles = articles
    .filter((article) => {
      const matchesSearch =
        searchQuery === '' ||
        article.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
        article.description.toLowerCase().includes(searchQuery.toLowerCase());
      const matchesCategory =
        selectedCategory === 'all' || article.category === selectedCategory;
      return matchesSearch && matchesCategory;
    })
    .sort((a, b) => {
      if (sortBy === 'date') {
        return new Date(b.createdAt).getTime() - new Date(a.createdAt).getTime();
      } else if (sortBy === 'popularity') {
        return b.views - a.views;
      } else {
        return a.title.localeCompare(b.title);
      }
    });

  $: paginatedArticles = filteredArticles.slice(
    (currentPage - 1) * itemsPerPage,
    currentPage * itemsPerPage
  );

  $: totalPages = Math.ceil(filteredArticles.length / itemsPerPage);

  function getStatusColor(status: Article['status']): string {
    switch (status) {
      case 'published':
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
      case 'draft':
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
      case 'archived':
        return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }

  function formatDate(dateString: string): string {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    }).format(date);
  }
</script>

<div class="flex flex-col h-full space-y-4">
  <!-- Header with Search and Actions -->
  <div class="flex items-center justify-between gap-4">
    <div class="flex items-center flex-1 gap-2">
      <div class="relative flex-1 max-w-md">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
        <Input
          type="search"
          placeholder="Search articles..."
          bind:value={searchQuery}
          class="pl-9"
        />
      </div>
      <Select bind:value={selectedCategory}>
        <SelectTrigger class="w-[180px]">
          <SelectValue placeholder="All Categories" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="all">All Categories</SelectItem>
          {#each categories as category}
            <SelectItem value={category.id}>
              {category.name} ({category.count})
            </SelectItem>
          {/each}
        </SelectContent>
      </Select>
      <Select bind:value={sortBy}>
        <SelectTrigger class="w-[160px]">
          <SelectValue placeholder="Sort by" />
        </SelectTrigger>
        <SelectContent>
          <SelectItem value="date">Date</SelectItem>
          <SelectItem value="popularity">Popularity</SelectItem>
          <SelectItem value="title">Title</SelectItem>
        </SelectContent>
      </Select>
    </div>
    <Button onclick={onArticleCreate}>
      <Plus class="mr-2 h-4 w-4" />
      New Article
    </Button>
  </div>

  <!-- Articles List -->
  <div class="flex-1 overflow-auto space-y-3">
    {#if paginatedArticles.length === 0}
      <Card class="p-8 text-center">
        <FileText class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">No articles found</h3>
        <p class="text-sm text-muted-foreground mb-4">
          {searchQuery || selectedCategory !== 'all'
            ? 'Try adjusting your search or filters'
            : 'Get started by creating your first article'}
        </p>
        {#if !searchQuery && selectedCategory === 'all'}
          <Button onclick={onArticleCreate}>Create Article</Button>
        {/if}
      </Card>
    {:else}
      {#each paginatedArticles as article}
        <Card class="p-4 hover:shadow-md transition-shadow">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1 space-y-2 cursor-pointer" onclick={() => onArticleSelect(article.id)} role="button" tabindex="0" on:keydown={(e) => e.key === 'Enter' && onArticleSelect(article.id)}>
              <div class="flex items-center gap-2">
                <h3 class="text-lg font-semibold hover:text-primary transition-colors">
                  {article.title}
                </h3>
                <Badge class={getStatusColor(article.status)}>
                  {article.status}
                </Badge>
              </div>
              <p class="text-sm text-muted-foreground line-clamp-2">
                {article.description}
              </p>
              <div class="flex items-center gap-4 text-xs text-muted-foreground">
                <span>{article.author}</span>
                <span>•</span>
                <span>{formatDate(article.updatedAt)}</span>
                <span>•</span>
                <span>{article.views} views</span>
              </div>
            </div>
            <div class="flex items-center gap-1">
              <Button
                variant="ghost"
                size="sm"
                onclick={() => onArticlePreview(article.id)}
                title="Preview"
              >
                <Eye class="h-4 w-4" />
              </Button>
              <Button
                variant="ghost"
                size="sm"
                onclick={() => onArticleEdit(article.id)}
                title="Edit"
              >
                <Edit2 class="h-4 w-4" />
              </Button>
              <Button
                variant="ghost"
                size="sm"
                onclick={() => onArticleDelete(article.id)}
                title="Delete"
                class="text-destructive hover:text-destructive"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </Card>
      {/each}
    {/if}
  </div>

  <!-- Pagination -->
  {#if totalPages > 1}
    <div class="flex items-center justify-between">
      <div class="text-sm text-muted-foreground">
        Showing {(currentPage - 1) * itemsPerPage + 1} to {Math.min(
          currentPage * itemsPerPage,
          filteredArticles.length
        )} of {filteredArticles.length} articles
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          disabled={currentPage === 1}
          onclick={() => (currentPage = 1)}
        >
          First
        </Button>
        <Button
          variant="outline"
          size="sm"
          disabled={currentPage === 1}
          onclick={() => (currentPage -= 1)}
        >
          Previous
        </Button>
        <span class="text-sm px-4">
          Page {currentPage} of {totalPages}
        </span>
        <Button
          variant="outline"
          size="sm"
          disabled={currentPage === totalPages}
          onclick={() => (currentPage += 1)}
        >
          Next
        </Button>
        <Button
          variant="outline"
          size="sm"
          disabled={currentPage === totalPages}
          onclick={() => (currentPage = totalPages)}
        >
          Last
        </Button>
      </div>
    </div>
  {/if}
</div>
