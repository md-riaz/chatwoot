<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  let {
    currentPage = 1,
    totalItems = 0,
    itemsPerPage = 16,
    class: className = '',
    onPageChange = (_page: number) => {},
    ...restProps
  }: {
    currentPage?: number;
    totalItems?: number;
    itemsPerPage?: number;
    class?: string;
    onPageChange?: (page: number) => void;
  } = $props();

  const totalPages = $derived(Math.ceil(totalItems / itemsPerPage));
  const startItem = $derived((currentPage - 1) * itemsPerPage + 1);
  const endItem = $derived(Math.min(currentPage * itemsPerPage, totalItems));

  function goToPage(page: number) {
    if (page >= 1 && page <= totalPages) {
      onPageChange(page);
    }
  }

  function getVisiblePages(): (number | string)[] {
    const pages: (number | string)[] = [];
    const maxVisible = 5;
    
    if (totalPages <= maxVisible) {
      for (let i = 1; i <= totalPages; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);
      
      if (currentPage > 3) {
        pages.push('...');
      }
      
      const start = Math.max(2, currentPage - 1);
      const end = Math.min(totalPages - 1, currentPage + 1);
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      
      if (currentPage < totalPages - 2) {
        pages.push('...');
      }
      
      pages.push(totalPages);
    }
    
    return pages;
  }
</script>

<div class={cn('flex items-center justify-between px-4 py-3 border-t', className)} {...restProps}>
  <div class="text-sm text-muted-foreground">
    Showing <span class="font-medium">{startItem}</span> to <span class="font-medium">{endItem}</span> of{' '}
    <span class="font-medium">{totalItems}</span> results
  </div>
  
  <div class="flex items-center gap-1">
    <Button
      variant="outline"
      size="sm"
      disabled={currentPage === 1}
      onclick={() => goToPage(currentPage - 1)}
    >
      Previous
    </Button>
    
    {#each getVisiblePages() as page}
      {#if page === '...'}
        <span class="px-2 text-muted-foreground">...</span>
      {:else}
        <Button
          variant={currentPage === page ? 'default' : 'outline'}
          size="sm"
          onclick={() => goToPage(page as number)}
        >
          {page}
        </Button>
      {/if}
    {/each}
    
    <Button
      variant="outline"
      size="sm"
      disabled={currentPage === totalPages}
      onclick={() => goToPage(currentPage + 1)}
    >
      Next
    </Button>
  </div>
</div>
