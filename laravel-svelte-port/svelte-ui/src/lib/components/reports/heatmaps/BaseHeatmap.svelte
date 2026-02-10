<script lang="ts">
  import { useHeatmapTooltip } from '$lib/composables/useHeatmapTooltip.svelte';
  import HeatmapTooltip from './HeatmapTooltip.svelte';
  import EmptyState from '../shared/EmptyState.svelte';
  import ErrorBoundary from '../shared/ErrorBoundary.svelte';
  import LoadingSkeleton from '../shared/LoadingSkeleton.svelte';
  import { 
    groupHeatmapByDay, 
    getQuantileIntervals, 
    getHeatmapLevelClass,
    formatHeatmapDate,
    getDayOfWeek,
    fillMissingHours,
    type HeatmapRow
  } from '$lib/utils/heatmapUtils';
  import type { HeatmapData } from '$lib/stores/reports.svelte';
  
  interface Props {
    heatmapData: HeatmapData[];
    numberOfRows?: number;
    isLoading?: boolean;
    colorScheme?: 'blue' | 'green';
    error?: string | null;
    onRetry?: () => void;
  }
  
  let { 
    heatmapData = [], 
    numberOfRows = 7, 
    isLoading = false, 
    colorScheme = 'blue',
    error = null,
    onRetry
  }: Props = $props();
  
  // Tooltip functionality
  const tooltip = useHeatmapTooltip();
  
  // Process heatmap data into rows
  const dataRows = $derived.by(() => {
    const groupedData = groupHeatmapByDay(heatmapData);
    return Array.from(groupedData.keys())
      .sort((a, b) => new Date(b).getTime() - new Date(a).getTime()) // Most recent first
      .slice(0, numberOfRows)
      .map(dateKey => {
        const dayData = groupedData.get(dateKey) || [];
        const filledData = fillMissingHours(dayData, dateKey);
        return {
          dateKey,
          data: filledData,
          dataHash: filledData.map(d => d.value).join(',')
        };
      });
  });
  
  // Calculate quantile ranges for color intensity
  const quantileRange = $derived.by(() => {
    const flattenedData = heatmapData.map(data => data.value);
    return getQuantileIntervals(flattenedData, [0.2, 0.4, 0.6, 0.8, 0.9, 0.99]);
  });
  
  // Get CSS class for heatmap cell
  function getHeatmapClass(value: number): string {
    return getHeatmapLevelClass(value, quantileRange, colorScheme);
  }
  
  // Handle cell hover
  function handleCellHover(event: MouseEvent, value: number) {
    tooltip.show(event, value);
  }
  
  function handleCellLeave() {
    tooltip.hide();
  }
</script>

<div class="grid relative w-full gap-x-4 gap-y-2.5 overflow-y-scroll md:overflow-visible grid-cols-[80px_1fr]">
  <ErrorBoundary {error} {onRetry}>
    {#if isLoading}
      <LoadingSkeleton type="heatmap" />
    {:else if !heatmapData.length}
      <div class="col-span-2">
        <EmptyState
          title="No data available"
          description="There is no heatmap data to display for the selected time range. Try selecting a different date range or check back later."
          icon="chart"
        />
      </div>
    {:else}
      <!-- Day labels -->
      <div class="grid gap-[5px] flex-shrink-0">
        {#each dataRows as row}
          <div class="h-8 min-w-[70px] text-slate-900 dark:text-slate-100 text-[10px] font-semibold flex flex-col items-end justify-center">
            {getDayOfWeek(row.dateKey)}
            <time class="font-normal text-slate-600 dark:text-slate-400">
              {formatHeatmapDate(row.dateKey)}
            </time>
          </div>
        {/each}
      </div>
      
      <!-- Heatmap grid -->
      <div class="grid gap-[5px] w-full min-w-[700px]" style="content-visibility: auto">
        {#each dataRows as row (row.dateKey)}
          <div class="grid gap-[5px] grid-cols-[repeat(24,_1fr)]" style="content-visibility: auto">
            {#each row.data as data}
              <div
                class="h-8 rounded-sm cursor-pointer transition-all duration-150 hover:scale-105 {getHeatmapClass(data.value)}"
                onmouseenter={(e) => handleCellHover(e, data.value)}
                onmouseleave={handleCellLeave}
                role="gridcell"
                tabindex="0"
                aria-label="{data.value} conversations at {new Date(data.timestamp * 1000).getHours()}:00"
              ></div>
            {/each}
          </div>
        {/each}
      </div>
      
      <!-- Spacer -->
      <div></div>
      
      <!-- Hour labels -->
      <div class="grid grid-cols-[repeat(24,_1fr)] gap-[5px] w-full text-[8px] font-semibold h-5 text-slate-900 dark:text-slate-100">
        {#each Array(24) as _, i}
          <div class="flex items-center justify-center">
            {i}
          </div>
        {/each}
      </div>
    {/if}
  </ErrorBoundary>
  
  <!-- Tooltip -->
  <HeatmapTooltip
    visible={tooltip.visible()}
    x={tooltip.x()}
    y={tooltip.y()}
    value={tooltip.value()}
  />
</div>