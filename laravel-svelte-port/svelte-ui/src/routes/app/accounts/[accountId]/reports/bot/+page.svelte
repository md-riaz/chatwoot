<script lang="ts">
  import { onMount } from 'svelte';
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import BotMetrics from '$lib/components/reports/bot/BotMetrics.svelte';
  import ReportFilterSelector from '$lib/components/reports/shared/ReportFilterSelector.svelte';
  import ReportContainer from '$lib/components/reports/shared/ReportContainer.svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';

  let from = $state(0);
  let to = $state(0);
  let groupBy = $state(GROUP_BY_FILTER[1]);
  let businessHours = $state(false);

  const reportKeys = {
    BOT_RESOLUTION_COUNT: 'bot_resolutions_count',
    BOT_HANDOFF_COUNT: 'bot_handoffs_count',
  };

  const requestPayload = $derived({
    from,
    to,
  });

  function fetchAllData() {
    fetchBotSummary();
    fetchChartData();
  }

  function fetchBotSummary() {
    try {
      reportsStore.fetchBotSummary(getRequestPayload());
    } catch (error) {
      console.error('Failed to fetch bot summary:', error);
    }
  }

  function fetchChartData() {
    Object.keys(reportKeys).forEach(async (key) => {
      try {
        await reportsStore.fetchAccountReport({
          metric: reportKeys[key as keyof typeof reportKeys],
          ...getRequestPayload(),
        });
      } catch (error) {
        console.error('Failed to fetch chart data:', error);
      }
    });
  }

  function getRequestPayload() {
    return {
      from,
      to,
      groupBy: groupBy?.period,
      businessHours,
    };
  }

  function onFilterChange(event: CustomEvent) {
    const { from: newFrom, to: newTo, groupBy: newGroupBy, businessHours: newBusinessHours } = event.detail;
    from = newFrom;
    to = newTo;
    groupBy = newGroupBy;
    businessHours = newBusinessHours;
    fetchAllData();
  }
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <ReportHeader headerTitle="Bot Reports" />

    <div class="flex flex-col gap-4">
      <ReportFilterSelector
        showAgentsFilter={false}
        showGroupByFilter={true}
        showBusinessHoursSwitch={false}
        on:filter-change={onFilterChange}
      />

      <BotMetrics filters={requestPayload} />
      <ReportContainer
        accountSummaryKey="getBotSummary"
        summaryFetchingKey="getBotSummaryFetchingStatus"
        {groupBy}
        {reportKeys}
      />
    </div>
  </div>
</div>
