<script lang="ts">
  import { reportsStore } from '$lib/stores/reports.svelte';
  import ReportMetricCard from './ReportMetricCard.svelte';
  import ReportChart from './ReportChart.svelte';
  import LoadingSkeleton from './LoadingSkeleton.svelte';

  interface Props {
    groupBy?: any;
    reportKeys?: Record<string, string>;
    accountSummaryKey?: string;
    summaryFetchingKey?: string;
  }

  let {
    groupBy = null,
    reportKeys = {},
    accountSummaryKey = 'getAccountSummary',
    summaryFetchingKey = 'getAccountSummaryFetchingStatus',
  }: Props = $props();

  const accountSummary = $derived(reportsStore.getData(accountSummaryKey));
  const isFetchingSummary = $derived(reportsStore.getUIFlag(summaryFetchingKey));

  const metrics = $derived([
    {
      key: 'CONVERSATIONS',
      label: 'Conversations',
      value: accountSummary?.conversations_count || 0,
      trend: accountSummary?.conversations_count_trend || 0,
    },
    {
      key: 'INCOMING_MESSAGES',
      label: 'Incoming Messages',
      value: accountSummary?.incoming_messages_count || 0,
      trend: accountSummary?.incoming_messages_count_trend || 0,
    },
    {
      key: 'OUTGOING_MESSAGES',
      label: 'Outgoing Messages',
      value: accountSummary?.outgoing_messages_count || 0,
      trend: accountSummary?.outgoing_messages_count_trend || 0,
    },
    {
      key: 'FIRST_RESPONSE_TIME',
      label: 'First Response Time',
      value: accountSummary?.avg_first_response_time || 0,
      trend: accountSummary?.avg_first_response_time_trend || 0,
      isTime: true,
    },
    {
      key: 'RESOLUTION_TIME',
      label: 'Resolution Time',
      value: accountSummary?.avg_resolution_time || 0,
      trend: accountSummary?.avg_resolution_time_trend || 0,
      isTime: true,
    },
    {
      key: 'RESOLUTION_COUNT',
      label: 'Resolutions',
      value: accountSummary?.resolutions_count || 0,
      trend: accountSummary?.resolutions_count_trend || 0,
    },
    {
      key: 'REPLY_TIME',
      label: 'Reply Time',
      value: accountSummary?.reply_time || 0,
      trend: accountSummary?.reply_time_trend || 0,
      isTime: true,
    },
  ]);

  const filteredMetrics = $derived(
    metrics.filter((metric) => reportKeys[metric.key])
  );
</script>

<div class="flex flex-col gap-6">
  <!-- Metrics Cards -->
  {#if isFetchingSummary}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      {#each Array(4) as _}
        <div class="h-[120px] animate-pulse bg-muted rounded"></div>
      {/each}
    </div>
  {:else}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      {#each filteredMetrics as metric}
        <ReportMetricCard
          label={metric.label}
          value={metric.value}
          trend={metric.trend}
          isTime={metric.isTime}
        />
      {/each}
    </div>
  {/if}

  <!-- Charts -->
  <div class="grid grid-cols-1 gap-6">
    {#each filteredMetrics as metric}
      <ReportChart
        title={metric.label}
        metricKey={reportKeys[metric.key]}
        {groupBy}
        isTime={metric.isTime}
      />
    {/each}
  </div>
</div>
