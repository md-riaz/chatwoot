<script lang="ts">
  import { Badge } from '$lib/components/ui/badge';
  import { Input } from '$lib/components/ui/input';
  import { Button } from '$lib/components/ui/button';
  
  export let categories: {
    id: string;
    name: string;
    articleCount: number;
    subcategories?: typeof categories;
  }[] = [];

  export let popularArticles: {
    id: string;
    title: string;
    views: number;
  }[] = [];

  export let onCategoryClick: (id: string) => void = () => {};
  export let onArticleClick: (id: string) => void = () => {};
  export let onSearch: (query: string) => void = () => {};

  let searchQuery = '';
  let expandedCategories = new Set<string>();

  function toggleCategory(id: string) {
    if (expandedCategories.has(id)) {
      expandedCategories.delete(id);
    } else {
      expandedCategories.add(id);
    }
    expandedCategories = expandedCategories;
  }

  function handleSearch() {
    onSearch(searchQuery);
  }
</script>

<aside class="w-64 border-r bg-background p-4 space-y-6">
  <div class="space-y-2">
    <Input
      bind:value={searchQuery}
      placeholder="Search articles..."
      onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && handleSearch()}
    />
    <Button class="w-full" variant="outline" size="sm" onclick={handleSearch}>
      Search
    </Button>
  </div>

  <div class="space-y-2">
    <h3 class="font-semibold text-sm text-muted-foreground uppercase">Categories</h3>
    <nav class="space-y-1">
      {#each categories as category}
        <div>
          <button
            class="w-full flex items-center justify-between py-2 px-3 rounded-md hover:bg-accent text-left"
            onclick={() => {
              toggleCategory(category.id);
              onCategoryClick(category.id);
            }}
          >
            <span>{category.name}</span>
            <Badge variant="secondary" class="text-xs">{category.articleCount}</Badge>
          </button>
          
          {#if category.subcategories && expandedCategories.has(category.id)}
            <div class="ml-4 mt-1 space-y-1">
              {#each category.subcategories as sub}
                <button
                  class="w-full flex items-center justify-between py-1 px-2 rounded-md hover:bg-accent text-sm text-left"
                  onclick={() => onCategoryClick(sub.id)}
                >
                  <span>{sub.name}</span>
                  <Badge variant="outline" class="text-xs">{sub.articleCount}</Badge>
                </button>
              {/each}
            </div>
          {/if}
        </div>
      {/each}
    </nav>
  </div>

  {#if popularArticles.length > 0}
    <div class="space-y-2">
      <h3 class="font-semibold text-sm text-muted-foreground uppercase">Popular Articles</h3>
      <div class="space-y-2">
        {#each popularArticles as article}
          <button
            class="w-full text-left py-2 px-3 rounded-md hover:bg-accent text-sm"
            onclick={() => onArticleClick(article.id)}
          >
            <div class="line-clamp-2">{article.title}</div>
            <div class="text-xs text-muted-foreground mt-1">{article.views} views</div>
          </button>
        {/each}
      </div>
    </div>
  {/if}
</aside>
