<script lang="ts">
  /**
   * BotMetrics Component
   * Displays bot conversation metrics
   * Vue Parity: Replaces bot metrics display from Vue dashboard
   */
  import ReportMetricCard from '../shared/ReportMetricCard.svelte';
  import { Bot, CheckCircle, ArrowRightLeft } from 'lucide-svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';

  interface Props {
    filters?: {
      from: number;
      to: number;
    };
  }

  let { filters }: Props = $props();

  // Get metrics from store
  const metrics = $derived(reportsStore.conversationMetrics);
  const isLoading = $derived(reportsStore.isLoading);

  // Extract bot-specific metrics
  const botResolutionsCount = $derived(metrics?.totalConversations || 0);
  const botHandoffsCount = $derived(metrics?.openConversations || 0);
  const botConversationsCount = $derived(
    (metrics?.totalConversations || 0) + (metrics?.openConversations || 0)
  );
</script>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
  <ReportMetricCard
    label="Bot Conversations"
    value={botConversationsCount}
  />

  <ReportMetricCard
    label="Bot Resolutions"
    value={botResolutionsCount}
  />

  <ReportMetricCard
    label="Bot Handoffs"
    value={botHandoffsCount}
  />
</div>
