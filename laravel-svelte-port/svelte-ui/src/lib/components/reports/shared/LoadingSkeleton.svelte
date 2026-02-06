<script lang="ts">
  interface Props {
    type: 'metrics' | 'table' | 'heatmap' | 'card';
    rows?: number;
    columns?: number;
  }
  
  let { type, rows = 5, columns = 3 }: Props = $props();
</script>

{#if type === 'metrics'}
  <!-- Metrics cards skeleton -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    {#each Array(4) as _}
      <div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between mb-4">
          <div class="w-24 h-4 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
          <div class="w-6 h-6 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
        </div>
        <div class="w-16 h-8 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
      </div>
    {/each}
  </div>

{:else if type === 'table'}
  <!-- Table skeleton -->
  <div class="overflow-auto rounded-lg border border-slate-200 dark:border-slate-700">
    <table class="w-full">
      <thead class="bg-slate-50 dark:bg-slate-800">
        <tr>
          {#each Array(columns) as _}
            <th class="px-4 py-3">
              <div class="w-20 h-4 bg-slate-200 dark:bg-slate-600 rounded animate-pulse"></div>
            </th>
          {/each}
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-700">
        {#each Array(rows) as _}
          <tr>
            {#each Array(columns) as _}
              <td class="px-4 py-3">
                <div class="w-full h-4 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
              </td>
            {/each}
          </tr>
        {/each}
      </tbody>
    </table>
  </div>

{:else if type === 'heatmap'}
  <!-- Heatmap skeleton -->
  <div class="grid relative w-full gap-x-4 gap-y-2.5 grid-cols-[80px_1fr]">
    <div class="grid gap-[5px] flex-shrink-0">
      {#each Array(7) as _}
        <div class="w-full rounded-sm bg-slate-200 dark:bg-slate-700 animate-pulse h-8 min-w-[70px]"></div>
      {/each}
    </div>
    <div class="grid gap-[5px] w-full min-w-[700px]">
      {#each Array(7) as _}
        <div class="grid gap-[5px] grid-cols-[repeat(24,_1fr)]">
          {#each Array(24) as _}
            <div class="w-full h-8 rounded-sm bg-slate-200 dark:bg-slate-700 animate-pulse"></div>
          {/each}
        </div>
      {/each}
    </div>
  </div>

{:else if type === 'card'}
  <!-- Generic card skeleton -->
  <div class="bg-white dark:bg-slate-900 p-6 rounded-xl shadow border border-slate-200 dark:border-slate-700">
    <div class="flex items-center justify-between mb-6">
      <div class="w-32 h-6 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
      <div class="w-16 h-4 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
    </div>
    <div class="space-y-4">
      {#each Array(rows) as _}
        <div class="w-full h-4 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
      {/each}
    </div>
  </div>
{/if}