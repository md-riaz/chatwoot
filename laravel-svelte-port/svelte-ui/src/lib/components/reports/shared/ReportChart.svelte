<script lang="ts">
  import { Card } from '$lib/components/ui/card';
  import { reportsStore } from '$lib/stores/reports.svelte';

  interface Props {
    title: string;
    metricKey: string;
    isTime?: boolean;
    groupBy?: string | null;
  }

  let { title, metricKey, isTime = false }: Props = $props();

  const rawChartData = $derived(reportsStore.getChartData(metricKey));
  const isLoading = $derived(reportsStore.getUIFlag(`isFetching_${metricKey}`));

  const chartData = $derived.by<{ data: number[]; labels: string[] }>(() => {
    if (
      rawChartData &&
      Array.isArray(rawChartData.data) &&
      Array.isArray(rawChartData.labels)
    ) {
      return {
        data: rawChartData.data.map((point: unknown) => Number(point) || 0),
        labels: rawChartData.labels.map((label: unknown) => String(label ?? '')),
      };
    }

    const fallbackValue = Number(
      (rawChartData as Record<string, unknown> | null)?.[metricKey]
    );

    if (!Number.isFinite(fallbackValue)) {
      return { data: [], labels: [] };
    }

    return {
      data: [fallbackValue],
      labels: ['Total'],
    };
  });

  const points = $derived<number[]>(chartData.data);
  const labels = $derived<string[]>(chartData.labels);
  const maxValue = $derived(points.length ? Math.max(...points, 1) : 1);

  function formatTime(seconds: number): string {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);

    if (hours > 0) {
      return `${hours}h ${minutes}m`;
    }

    if (minutes > 0) {
      return `${minutes}m ${secs}s`;
    }

    return `${secs}s`;
  }

  function formatValue(value: number): string {
    return isTime ? formatTime(value) : value.toLocaleString();
  }
</script>

<Card class="p-6">
  <h3 class="text-lg font-semibold mb-4">{title}</h3>

  {#if isLoading}
    <div class="h-[300px] animate-pulse bg-muted rounded"></div>
  {:else if points.length > 0}
    <div class="h-[300px] flex items-end gap-1 rounded bg-muted/20 p-3 overflow-x-auto">
      {#each points as point, index (labels[index] ?? index)}
        <div class="flex flex-col items-center min-w-[18px]" title={formatValue(point)}>
          <div
            class="w-4 rounded-t bg-primary/70 hover:bg-primary transition-colors"
            style={`height: ${Math.max((point / maxValue) * 220, 2)}px`}
          ></div>
          {#if labels[index]}
            <span class="mt-1 text-[10px] text-muted-foreground truncate max-w-[18px]">
              {labels[index]}
            </span>
          {/if}
        </div>
      {/each}
    </div>
  {:else}
    <div class="h-[300px] flex items-center justify-center text-muted-foreground">
      No data available
    </div>
  {/if}
</Card>
