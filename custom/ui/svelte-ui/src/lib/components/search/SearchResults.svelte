<script lang="ts">
  import { searchStore } from '$lib/stores/search.svelte';
  import type { SearchResult } from '$lib/api/search';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { MessageCircle, User, Mail, Loader2 } from '@lucide/svelte';
  
  const results = $derived(searchStore.results);
  const conversationResults = $derived(searchStore.conversationResults);
  const contactResults = $derived(searchStore.contactResults);
  const messageResults = $derived(searchStore.messageResults);
  const isSearching = $derived(searchStore.isSearching);
  const totalResults = $derived(searchStore.totalResults);
  
  function getIcon(type: string) {
    switch (type) {
      case 'conversation': return MessageCircle;
      case 'contact': return User;
      case 'message': return Mail;
      default: return MessageCircle;
    }
  }
  
  function getTypeLabel(type: string) {
    switch (type) {
      case 'conversation': return 'Conversation';
      case 'contact': return 'Contact';
      case 'message': return 'Message';
      default: return type;
    }
  }
  
  function formatDate(dateStr: string) {
    const date = new Date(dateStr);
    return date.toLocaleDateString();
  }
  
  function handleResultClick(result: SearchResult) {
    // TODO: Navigate to result
    console.log('Navigate to:', result);
  }
  
  function handleLoadMore() {
    searchStore.loadMore();
  }
</script>

<div class="search-results space-y-4">
  {#if conversationResults.length > 0}
    <div>
      <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">
        Conversations ({conversationResults.length})
      </h3>
      <div class="space-y-1">
        {#each conversationResults as result}
          <button
            class="w-full text-left p-3 hover:bg-gray-50 rounded-lg transition-colors flex items-start gap-3"
            onclick={() => handleResultClick(result)}
          >
            <MessageCircle class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <p class="font-medium text-sm truncate">{result.title}</p>
              {#if result.description}
                <p class="text-xs text-gray-600 truncate">{result.description}</p>
              {/if}
              <p class="text-xs text-gray-400 mt-1">{formatDate(result.createdAt)}</p>
            </div>
          </button>
        {/each}
      </div>
    </div>
  {/if}
  
  {#if contactResults.length > 0}
    <div>
      <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">
        Contacts ({contactResults.length})
      </h3>
      <div class="space-y-1">
        {#each contactResults as result}
          <button
            class="w-full text-left p-3 hover:bg-gray-50 rounded-lg transition-colors flex items-start gap-3"
            onclick={() => handleResultClick(result)}
          >
            {#if result.thumbnail}
              <img src={result.thumbnail} alt={result.title} class="h-8 w-8 rounded-full" />
            {:else}
              <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-xs font-semibold">
                {result.title.charAt(0)}
              </div>
            {/if}
            <div class="flex-1 min-w-0">
              <p class="font-medium text-sm truncate">{result.title}</p>
              {#if result.description}
                <p class="text-xs text-gray-600 truncate">{result.description}</p>
              {/if}
            </div>
          </button>
        {/each}
      </div>
    </div>
  {/if}
  
  {#if messageResults.length > 0}
    <div>
      <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">
        Messages ({messageResults.length})
      </h3>
      <div class="space-y-1">
        {#each messageResults as result}
          <button
            class="w-full text-left p-3 hover:bg-gray-50 rounded-lg transition-colors flex items-start gap-3"
            onclick={() => handleResultClick(result)}
          >
            <Mail class="h-5 w-5 text-gray-400 flex-shrink-0 mt-0.5" />
            <div class="flex-1 min-w-0">
              <p class="text-sm truncate">{result.description || result.title}</p>
              <p class="text-xs text-gray-400 mt-1">{formatDate(result.createdAt)}</p>
            </div>
          </button>
        {/each}
      </div>
    </div>
  {/if}
  
  {#if results.length < totalResults}
    <div class="text-center pt-2">
      <Button
        variant="ghost"
        size="sm"
        onclick={handleLoadMore}
        disabled={isSearching}
      >
        {#if isSearching}
          <Loader2 class="h-4 w-4 mr-2 animate-spin" />
        {/if}
        Load more ({results.length} of {totalResults})
      </Button>
    </div>
  {/if}
</div>
