<script lang="ts">
  import { Input } from '$lib/components/ui/input';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { Card } from '$lib/components/ui/card';
  import * as Select from '$lib/components/ui/select';

  export let results: {
    id: string;
    title: string;
    content: string;
    category: string;
    locale: string;
    relevance: number;
  }[] = [];

  export let recentSearches: string[] = [];
  export let categories: string[] = [];
  export let locales: string[] = [];
  export let onSearch: (query: string, filters: { category?: string; locale?: string }) => void = () => {};
  export let onResultClick: (id: string) => void = () => {};
  export let searching = false;

  let query = '';
  let selectedCategory = $state({ value: '' });
  let selectedLocale = $state({ value: '' });

  function handleSearch() {
    const filters: { category?: string; locale?: string } = {};
    if (selectedCategory.value) filters.category = selectedCategory.value;
    if (selectedLocale.value) filters.locale = selectedLocale.value;
    onSearch(query, filters);
  }

  function handleRecentSearch(search: string) {
    query = search;
    handleSearch();
  }

  function highlightText(text: string, query: string): string {
    if (!query) return text;
    const regex = new RegExp(`(${query})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-200">$1</mark>');
  }
</script>

<div class="w-full max-w-4xl mx-auto space-y-6 p-6">
  <div class="space-y-4">
    <div class="flex gap-2">
      <Input
        bind:value={query}
        placeholder="Search articles..."
        class="flex-1"
        onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && handleSearch()}
      />
      <Button onclick={handleSearch} disabled={searching}>
        {searching ? 'Searching...' : 'Search'}
      </Button>
    </div>

    <div class="flex gap-2">
      {#if categories.length > 0}
        <Select.Root bind:selected={selectedCategory}>
          <Select.Trigger class="w-[180px]">
            <Select.Value placeholder="All Categories" />
          </Select.Trigger>
          <Select.Content>
            <Select.Item value="">All Categories</Select.Item>
            {#each categories as cat}
              <Select.Item value={cat}>{cat}</Select.Item>
            {/each}
          </Select.Content>
        </Select.Root>
      {/if}

      {#if locales.length > 0}
        <Select.Root bind:selected={selectedLocale}>
          <Select.Trigger class="w-[180px]">
            <Select.Value placeholder="All Languages" />
          </Select.Trigger>
          <Select.Content>
            <Select.Item value="">All Languages</Select.Item>
            {#each locales as locale}
              <Select.Item value={locale}>{locale.toUpperCase()}</Select.Item>
          {/each}
          </Select.Content>
        </Select.Root>
      {/if}
    </div>
  </div>

  {#if recentSearches.length > 0 && results.length === 0}
    <div class="space-y-2">
      <h3 class="text-sm font-semibold text-muted-foreground">Recent Searches</h3>
      <div class="flex flex-wrap gap-2">
        {#each recentSearches as search}
          <Badge variant="secondary" class="cursor-pointer" onclick={() => handleRecentSearch(search)}>
            {search}
          </Badge>
        {/each}
      </div>
    </div>
  {/if}

  <div class="space-y-3">
    {#if results.length > 0}
      <div class="text-sm text-muted-foreground">
        Found {results.length} result{results.length !== 1 ? 's' : ''}
      </div>
    {/if}

    {#each results as result}
      <Card class="p-4 hover:bg-accent cursor-pointer transition-colors" onclick={() => onResultClick(result.id)}>
        <div class="space-y-2">
          <div class="flex items-start justify-between gap-4">
            <h3 class="font-semibold">{@html highlightText(result.title, query)}</h3>
            <Badge variant="outline">{(result.relevance * 100).toFixed(0)}% match</Badge>
          </div>
          <p class="text-sm text-muted-foreground line-clamp-2">
            {@html highlightText(result.content, query)}
          </p>
          <div class="flex gap-2">
            <Badge variant="secondary">{result.category}</Badge>
            <Badge variant="outline">{result.locale.toUpperCase()}</Badge>
          </div>
        </div>
      </Card>
    {/each}

    {#if results.length === 0 && query && !searching}
      <Card class="p-8 text-center">
        <p class="text-muted-foreground">No results found for "{query}"</p>
        <p class="text-sm text-muted-foreground mt-2">Try different keywords or check your filters</p>
      </Card>
    {/if}
  </div>
</div>
