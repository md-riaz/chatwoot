<script lang="ts">
  import { csatStore } from '$lib/stores/csat.svelte';
  import ReportMetricCard from '../shared/ReportMetricCard.svelte';
  import LoadingSkeleton from '../shared/LoadingSkeleton.svelte';

  interface Props {
    filters: any;
  }

  let { filters }: Props = $props();

  const metrics = $derived(csatStore.metrics);
  const isLoading = $derived(csatStore.getUIFlags().isFetchingMetrics);

  const metricsData = $derived([
    {
      label: 'Total Responses',
      value: metrics?.totalResponses || 0,
      infoText: 'Total number of CSAT responses received',
    },
    {
      label: 'Satisfaction Score',
      value: metrics?.satisfactionScore || 0,
      infoText: 'Average customer satisfaction score',
    },
    {
      label: 'Response Rate',
      value: metrics?.responseRate || 0,
      infoText: 'Percentage of customers who responded',
    },
  ]);
</script>

<div class="flex flex-wrap mx-0 shadow outline-1 outline outline-border rounded-xl bg-card px-6 py-5">
  {#if isLoading}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
      {#each Array(3) as _}
        <div class="h-[100px] animate-pulse bg-muted rounded"></div>
      {/each}
    </div>
  {:else}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full">
      {#each metricsData as metric}
        <ReportMetricCard
          label={metric.label}
          value={metric.value}
          infoText={metric.infoText}
        />
      {/each}
    </div>
  {/if}
</div>
