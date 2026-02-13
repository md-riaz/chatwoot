<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { Input } from '$lib/components/ui/input';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Search, AlertCircle } from 'lucide-svelte';
  import { searchStore } from '$lib/stores/search.svelte';

  let searchQuery = $state($page.url.searchParams.get('q') || '');

  const accountId = $derived($page.params.accountId);
  const results = $derived(searchStore.results);
  const isSearching = $derived(searchStore.isSearching);
  const error = $derived(searchStore.error);

  async function handleSearch() {
    await searchStore.performSearch(searchQuery.trim());
  }

  function openResult(result: (typeof results)[number]) {
    if (result.type === 'conversation' && result.conversationId) {
      goto(`/app/accounts/${accountId}/conversations/${result.conversationId}`);
      return;
    }

    if (result.type === 'contact' && result.contactId) {
      goto(`/app/accounts/${accountId}/contacts/${result.contactId}`);
    }
  }
</script>

<div class="flex flex-col h-full p-6 space-y-6">
  <div class="flex items-center space-x-2">
    <div class="relative flex-1">
      <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
      <Input
        type="search"
        placeholder="Search conversations, contacts..."
        class="pl-9"
        bind:value={searchQuery}
        onkeydown={e => e.key === 'Enter' && handleSearch()}
      />
    </div>
    <Button
      onclick={handleSearch}
      disabled={isSearching || !searchQuery.trim()}
    >
      {isSearching ? 'Searching...' : 'Search'}
    </Button>
  </div>

  {#if error}
    <Card.Root>
      <Card.Content class="p-8 flex flex-col items-center gap-3">
        <AlertCircle class="h-8 w-8 text-destructive" />
        <p class="text-sm text-muted-foreground">{error}</p>
      </Card.Content>
    </Card.Root>
  {:else if isSearching}
    <div class="border rounded-lg p-8 text-muted-foreground">Searching...</div>
  {:else if !searchQuery.trim()}
    <div
      class="border rounded-lg p-8 flex items-center justify-center text-muted-foreground"
    >
      Type to search...
    </div>
  {:else if results.length === 0}
    <div
      class="border rounded-lg p-8 flex items-center justify-center text-muted-foreground"
    >
      No results found for "{searchQuery}"
    </div>
  {:else}
    <div class="space-y-3">
      {#each results as result (result.type + result.id)}
        <Card.Root
          class="cursor-pointer hover:border-primary"
          onclick={() => openResult(result)}
        >
          <Card.Content class="p-4">
            <p class="font-medium">{result.title}</p>
            {#if result.description}
              <p class="text-sm text-muted-foreground">{result.description}</p>
            {/if}
          </Card.Content>
        </Card.Root>
      {/each}
      {#if results.length < searchStore.totalResults}
        <Button
          variant="outline"
          onclick={() => searchStore.loadMore()}
          disabled={isSearching}
        >
          Load more
        </Button>
      {/if}
    </div>
  {/if}
</div>
