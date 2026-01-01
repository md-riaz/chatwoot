<script lang="ts">
  import { cn } from '$lib/utils';
  import { Checkbox } from '$lib/components/ui/checkbox';

  interface Column {
    key: string;
    label: string;
    width?: string;
  }

  interface Props {
    columns: Column[];
    data: Record<string, string | number | boolean>[];
    selectable?: boolean;
    selectedRows?: number[];
    onSelectionChange?: (selected: number[]) => void;
    class?: string;
  }

  let { columns, data = [], selectable = false, selectedRows = [], onSelectionChange, class: className }: Props = $props();

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
</script>

<div class={cn('overflow-auto rounded-lg border border-slate-200 dark:border-slate-700', className)}>
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
      {#each data as row, index}
        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800">
          {#if selectable}
            <td class="px-4 py-3">
              <Checkbox 
                checked={selectedRows.includes(index)}
                onCheckedChange={() => toggleRow(index)}
              />
            </td>
          {/if}
          {#each columns as column}
            <td class="px-4 py-3 text-sm text-slate-900 dark:text-slate-100">
              {row[column.key]}
            </td>
          {/each}
        </tr>
      {/each}
    </tbody>
  </table>
</div>
