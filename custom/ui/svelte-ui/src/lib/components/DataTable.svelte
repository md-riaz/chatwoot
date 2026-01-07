<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { cn } from '$lib/utils';
  import { ChevronLeft, ChevronRight } from 'lucide-svelte';

  interface Column {
    key: string;
    label: string;
    width?: string;
    sortable?: boolean;
    render?: (value: any, row: any) => string;
  }

  interface Pagination {
    page: number;
    perPage: number;
    total: number;
  }

  interface Props {
    columns: Column[];
    data: Record<string, any>[];
    selectable?: boolean;
    selectedRows?: number[];
    onSelectionChange?: (selected: number[]) => void;
    loading?: boolean;
    pagination?: Pagination;
    onPageChange?: (page: number) => void;
    onRowClick?: (row: any, index: number) => void;
    class?: string;
  }

  let { 
    columns, 
    data = [], 
    selectable = false, 
    selectedRows = [], 
    onSelectionChange,
    loading = false,
    pagination,
    onPageChange,
    onRowClick,
    class: className 
  }: Props = $props();

  function toggleRow(index: number) {
    if (selectedRows.includes(index)) {
      onSelectionChange?.(selectedRows.filter(i => i !== index));
    } else {
      onSelectionChange?.([...selectedRows, index]);
    }
  }

  function toggleAll() {
    if (selectedRows.length === data.length) {
      onSelectionChange?.([]);
    } else {
      onSelectionChange?.(data.map((_, i) => i));
    }
  }

  function handleRowClick(row: any, index: number) {
    if (onRowClick) {
      onRowClick(row, index);
    }
  }

  function getCellValue(row: any, column: Column): string {
    const value = row[column.key];
    if (column.render) {
      return column.render(value, row);
    }
    return value != null ? String(value) : '-';
  }

  $effect(() => {
    // Ensure page is valid
    if (pagination && pagination.page < 1) {
      pagination.page = 1;
    }
  });
</script>

<div class={cn('space-y-4', className)}>
  <div class={cn('overflow-auto rounded-lg border border-slate-200 dark:border-slate-700', loading && 'opacity-50 pointer-events-none')}>
    <table class="w-full">
      <thead class="bg-slate-50 dark:bg-slate-800">
        <tr>
          {#if selectable}
            <th class="px-4 py-3 text-left w-12">
              <Checkbox 
                checked={selectedRows.length === data.length && data.length > 0}
                onCheckedChange={toggleAll}
              />
            </th>
          {/if}
          {#each columns as column}
            <th class="px-4 py-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider" style:width={column.width}>
              {column.label}
            </th>
          {/each}
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-700">
        {#if loading}
          <tr>
            <td colspan={columns.length + (selectable ? 1 : 0)} class="px-4 py-8 text-center text-sm text-slate-500">
              Loading...
            </td>
          </tr>
        {:else if data.length === 0}
          <tr>
            <td colspan={columns.length + (selectable ? 1 : 0)} class="px-4 py-8 text-center text-sm text-slate-500">
              No data available
            </td>
          </tr>
        {:else}
          {#each data as row, index}
            <tr 
              class={cn(
                'hover:bg-slate-50 dark:hover:bg-slate-800',
                onRowClick && 'cursor-pointer'
              )}
              onclick={() => handleRowClick(row, index)}
            >
              {#if selectable}
                <td class="px-4 py-3" onclick={(e) => e.stopPropagation()}>
                  <Checkbox 
                    checked={selectedRows.includes(index)}
                    onCheckedChange={() => toggleRow(index)}
                  />
                </td>
              {/if}
              {#each columns as column}
                <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
                  {@html getCellValue(row, column)}
                </td>
              {/each}
            </tr>
          {/each}
        {/if}
      </tbody>
    </table>
  </div>

  {#if pagination && pagination.total > pagination.perPage}
    <div class="flex items-center justify-between px-2">
      <div class="text-sm text-slate-700 dark:text-slate-300">
        Showing {(pagination.page - 1) * pagination.perPage + 1} to {Math.min(pagination.page * pagination.perPage, pagination.total)} of {pagination.total} results
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          onclick={() => onPageChange?.(pagination.page - 1)}
          disabled={pagination.page <= 1 || loading}
        >
          <ChevronLeft class="h-4 w-4" />
          Previous
        </Button>
        <div class="text-sm text-slate-700 dark:text-slate-300">
          Page {pagination.page} of {Math.ceil(pagination.total / pagination.perPage)}
        </div>
        <Button
          variant="outline"
          size="sm"
          onclick={() => onPageChange?.(pagination.page + 1)}
          disabled={pagination.page >= Math.ceil(pagination.total / pagination.perPage) || loading}
        >
          Next
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  {/if}
</div>
