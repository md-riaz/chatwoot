<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import ReportFilterSelector from '$lib/components/reports/shared/ReportFilterSelector.svelte';
  import ReportContainer from '$lib/components/reports/shared/ReportContainer.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Download } from 'lucide-svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';
  import { generateFileName } from '$lib/utils/downloadHelper';

  const REPORTS_KEYS = {
    CONVERSATIONS: 'conversations_count',
    INCOMING_MESSAGES: 'incoming_messages_count',
    OUTGOING_MESSAGES: 'outgoing_messages_count',
    FIRST_RESPONSE_TIME: 'avg_first_response_time',
    RESOLUTION_TIME: 'avg_resolution_time',
    RESOLUTION_COUNT: 'resolutions_count',
    REPLY_TIME: 'reply_time',
  };

  let from = $state(0);
  let to = $state(0);
  let groupBy = $state(GROUP_BY_FILTER[1]);
  let businessHours = $state(false);

  function fetchAllData() {
    fetchAccountSummary();
    fetchChartData();
  }

  function fetchAccountSummary() {
    try {
      reportsStore.fetchAccountSummary(getRequestPayload());
    } catch (error) {
      console.error('Failed to fetch account summary:', error);
    }
  }

  function fetchChartData() {
    Object.keys(REPORTS_KEYS).forEach(async (key) => {
      try {
        await reportsStore.fetchAccountReport({
          metric: REPORTS_KEYS[key as keyof typeof REPORTS_KEYS],
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

  function downloadConversationReports() {
    const fileName = generateFileName({
      type: 'conversation',
      to,
      businessHours,
    });
    reportsStore.downloadConversationsSummaryReports({
      from,
      to,
      fileName,
      businessHours,
    });
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
    <ReportHeader headerTitle="Conversation Reports">
      <Button size="sm" onclick={downloadConversationReports}>
        <Download class="mr-2 h-4 w-4" />
        Download Conversation Reports
      </Button>
    </ReportHeader>
    
    <div class="flex flex-col gap-3">
      <ReportFilterSelector
        showAgentsFilter={false}
        showGroupByFilter={true}
        on:filter-change={onFilterChange}
      />
      <ReportContainer {groupBy} reportKeys={REPORTS_KEYS} />
    </div>
  </div>
</div>
