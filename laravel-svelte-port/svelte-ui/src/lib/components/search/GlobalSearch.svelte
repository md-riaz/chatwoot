<script lang="ts">
  import { searchStore } from '$lib/stores/search.svelte';
  import { onMount } from 'svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Dialog from '$lib/components/ui/dialog';
  import { Input } from '$lib/components/ui/input';
  import { Search, Command, X, Loader2, Clock, TrendingUp } from '@lucide/svelte';
  import SearchResults from './SearchResults.svelte';
  
  let isOpen = $state(false);
  let searchInput: HTMLInputElement;
  
  const query = $derived(searchStore.query);
  const isSearching = $derived(searchStore.isSearching);
  const hasResults = $derived(searchStore.hasResults);
  const history = $derived(searchStore.history);
  
  onMount(() => {
    // Cmd+K or Ctrl+K to open search
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault();
        isOpen = true;
      }
      
      // Escape to close
      if (e.key === 'Escape' && isOpen) {
        isOpen = false;
      }
    };
    
    document.addEventListener('keydown', handleKeyDown);
    return () => document.removeEventListener('keydown', handleKeyDown);
  });
  
  function handleOpenChange(open: boolean) {
    isOpen = open;
    if (open) {
      setTimeout(() => searchInput?.focus(), 100);
    } else {
      searchStore.clearQuery();
    }
  }
  
  async function handleSearch(e: Event) {
    e.preventDefault();
    const formData = new FormData(e.target as HTMLFormElement);
    const q = formData.get('query') as string;
    
    if (q.trim()) {
      await searchStore.performSearch(q);
    }
  }
  
  function handleHistoryClick(q: string) {
    searchStore.setQuery(q);
    searchStore.performSearch(q);
  }
  
  function handleRemoveHistory(q: string) {
    searchStore.removeFromHistory(q);
  }
</script>

<!-- Trigger Button -->
<Button 
  variant="outline" 
  class="relative w-full justify-start text-sm text-muted-foreground sm:pr-12 md:w-64"
  onclick={() => (isOpen = true)}
>
  <Search class="mr-2 h-4 w-4" />
  <span>Search...</span>
  <kbd class="pointer-events-none absolute right-1.5 top-2 hidden h-5 select-none items-center gap-1 rounded border bg-muted px-1.5 font-mono text-[10px] font-medium opacity-100 sm:flex">
    <Command class="h-3 w-3" />K
  </kbd>
</Button>

<!-- Search Dialog -->
<Dialog.Root open={isOpen} onOpenChange={handleOpenChange}>
  <Dialog.Content class="max-w-3xl max-h-[80vh] p-0">
    <div class="flex flex-col h-full">
      <!-- Search Input -->
      <div class="border-b p-4">
        <form onsubmit={handleSearch} class="relative">
          <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
          <Input
            bind:this={searchInput}
            type="search"
            name="query"
            placeholder="Search conversations, contacts, messages..."
            class="pl-10 pr-10"
            value={query}
            oninput={(e: Event & { currentTarget: HTMLInputElement }) => searchStore.setQuery(e.currentTarget.value)}
          />
          {#if isSearching}
            <Loader2 class="absolute right-3 top-3 h-4 w-4 animate-spin text-muted-foreground" />
          {:else if query}
            <button
              type="button"
              class="absolute right-3 top-3 text-muted-foreground hover:text-foreground"
              onclick={() => searchStore.clearQuery()}
            >
              <X class="h-4 w-4" />
            </button>
          {/if}
        </form>
      </div>
      
      <!-- Results or History -->
      <div class="flex-1 overflow-y-auto p-4">
        {#if query && hasResults}
          <SearchResults />
        {:else if query && !isSearching && !hasResults}
          <div class="text-center py-12">
            <Search class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <p class="text-sm text-gray-600">No results found for "{query}"</p>
          </div>
        {:else if !query && history.length > 0}
          <div>
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <Clock class="h-4 w-4" />
                Recent Searches
              </h3>
              <Button
                variant="ghost"
                size="sm"
                class="text-xs"
                onclick={() => searchStore.clearHistory()}
              >
                Clear
              </Button>
            </div>
            <div class="space-y-1">
              {#each history as item}
                <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded group">
                  <button
                    class="flex-1 text-left text-sm"
                    onclick={() => handleHistoryClick(item)}
                  >
                    {item}
                  </button>
                  <button
                    class="opacity-0 group-hover:opacity-100 transition-opacity"
                    onclick={() => handleRemoveHistory(item)}
                  >
                    <X class="h-3 w-3 text-gray-400" />
                  </button>
                </div>
              {/each}
            </div>
          </div>
        {:else}
          <div class="text-center py-12">
            <TrendingUp class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <p class="text-sm text-gray-600">Start typing to search</p>
          </div>
        {/if}
      </div>
    </div>
  </Dialog.Content>
</Dialog.Root>
