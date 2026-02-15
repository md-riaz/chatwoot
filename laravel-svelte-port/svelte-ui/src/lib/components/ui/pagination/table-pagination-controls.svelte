<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import Select from '$lib/components/ui/select/select-native.svelte';

  let {
    currentPage = 1,
    totalItems = 0,
    itemsPerPage = 10,
    pageSizeOptions = [10, 25, 50, 100],
    class: className = '',
    onPageChange = (_page: number) => {},
    onPageSizeChange = (_value: number) => {},
  }: {
    currentPage?: number;
    totalItems?: number;
    itemsPerPage?: number;
    pageSizeOptions?: number[];
    class?: string;
    onPageChange?: (page: number) => void;
    onPageSizeChange?: (value: number) => void;
  } = $props();

  const itemsPerPageSafe = $derived(Math.max(1, itemsPerPage));
  const totalPages = $derived(Math.ceil(totalItems / itemsPerPageSafe));
  const startItem = $derived(
    totalItems === 0
      ? 0
      : Math.min(totalItems, (currentPage - 1) * itemsPerPageSafe + 1)
  );
  const endItem = $derived(
    Math.min(currentPage * itemsPerPageSafe, totalItems)
  );

  function handlePageSizeChange(event: Event) {
    const target = event.currentTarget as HTMLSelectElement;
    const parsedValue = Number.parseInt(target.value, 10);

    if (!Number.isNaN(parsedValue)) {
      onPageSizeChange(parsedValue);
    }
  }
</script>

{#if totalPages > 1}
  <div class={`mt-4 flex items-center justify-between px-2 ${className}`}>
    <div class="text-sm text-slate-700 dark:text-slate-300">
      Showing {startItem} to {endItem} of {totalItems} results
    </div>
    <div class="flex items-center gap-2">
      <Select
        value={String(itemsPerPage)}
        class="w-[120px]"
        onchange={handlePageSizeChange}
      >
        {#each pageSizeOptions as option}
          <option value={option}>{option} per page</option>
        {/each}
      </Select>

      <Button
        variant="outline"
        size="sm"
        onclick={() => onPageChange(currentPage - 1)}
        disabled={currentPage <= 1}
      >
        Previous
      </Button>
      <div class="text-sm text-slate-700 dark:text-slate-300">
        Page {currentPage} of {totalPages}
      </div>
      <Button
        variant="outline"
        size="sm"
        onclick={() => onPageChange(currentPage + 1)}
        disabled={currentPage >= totalPages}
      >
        Next
      </Button>
    </div>
  </div>
{/if}
