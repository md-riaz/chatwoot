<script lang="ts">
  /**
   * SLAMetrics Component
   * Displays SLA performance metrics
   * Vue Parity: Replaces SLA metrics display from Vue dashboard
   */
  import ReportMetricCard from '../shared/ReportMetricCard.svelte';

  interface Props {
    hitRate: number;
    noOfBreaches: number;
    noOfConversations: number;
    isLoading?: boolean;
  }

  let {
    hitRate = 0,
    noOfBreaches = 0,
    noOfConversations = 0,
    isLoading = false
  }: Props = $props();
</script>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
  <ReportMetricCard
    label="SLA Hit Rate"
    value={hitRate}
  />

  <ReportMetricCard
    label="SLA Breaches"
    value={noOfBreaches}
  />

  <ReportMetricCard
    label="Total Conversations"
    value={noOfConversations}
  />
</div>
