<script lang="ts">
  import { Input } from "$lib/components/ui/input";
  import { Button } from "$lib/components/ui/button";
  import { Search } from "lucide-svelte";
  import { page } from "$app/stores";
  
  let searchQuery = $state($page.url.searchParams.get('q') || '');
  let isSearching = $state(false);

  function handleSearch() {
    isSearching = true;
    // TODO: Implement actual search logic using API
    setTimeout(() => {
        isSearching = false;
    }, 500);
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
        onkeydown={(e) => e.key === 'Enter' && handleSearch()}
      />
    </div>
    <Button onclick={handleSearch} disabled={isSearching}>
      {isSearching ? 'Searching...' : 'Search'}
    </Button>
  </div>

  <div class="flex-1 border rounded-lg p-8 flex items-center justify-center text-muted-foreground">
    {#if searchQuery}
        <span>Search results for "{searchQuery}" will appear here.</span>
    {:else}
        <span>Type to search...</span>
    {/if}
  </div>
</div>
