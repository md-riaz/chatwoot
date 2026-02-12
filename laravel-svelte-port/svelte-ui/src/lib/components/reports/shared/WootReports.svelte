<script lang="ts">
  import { onMount } from 'svelte';
  import { Button } from '$lib/components/ui/button';
  import { Download } from 'lucide-svelte';
  import ReportHeader from './ReportHeader.svelte';
  import ReportFilters from './ReportFilters.svelte';
  import ReportContainer from './ReportContainer.svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { GROUP_BY_FILTER } from '$lib/constants/reports';
  import { generateFileName } from '$lib/utils/downloadHelper';

  interface Props {
    type?: string;
    downloadButtonLabel?: string;
    reportTitle?: string;
    hasBackButton?: boolean;
    selectedItem?: any;
  }

  let {
    type = 'account',
    downloadButtonLabel = 'Download Reports',
    reportTitle = 'Download Reports',
    hasBackButton = false,
    selectedItem = null,
  }: Props = $props();

  const GROUP_BY_OPTIONS = {
    DAY: [{ id: 1, groupByKey: 'Day' }],
    WEEK: [
      { id: 1, groupByKey: 'Day' },
      { id: 2, groupByKey: 'Week' },
    ],
    MONTH: [
      { id: 1, groupByKey: 'Day' },
      { id: 2, groupByKey: 'Week' },
      { id: 3, groupByKey: 'Month' },
    ],
    YEAR: [
      { id: 2, groupByKey: 'Week' },
      { id: 3, groupByKey: 'Month' },
      { id: 4, groupByKey: 'Year' },
    ],
  };

  let from = $state(0);
  let to = $state(0);
  let groupBy = $state(GROUP_BY_FILTER[1]);
  let groupByfilterItemsList = $state(GROUP_BY_OPTIONS.DAY);
  let selectedGroupByFilter = $state<any>(null);
  let businessHours = $state(false);

  // Use $derived to maintain reactive reference to selectedItem prop
  const selectedItemRef = $derived(selectedItem);
  let selectedFilter = $state<any>(null);

  // Update local state when prop changes
  $effect(() => {
    selectedFilter = selectedItemRef;
  });

  $effect(() => {
    if (!selectedFilter && filterItemsList.length) {
      selectedFilter = filterItemsList[0];
    }
  });

  // Get filter items list from appropriate store based on type
  const filterItemsList = $derived.by(() => {
    switch (type) {
      case 'agent':
        return agentsStore.allAgents;
      case 'team':
        return teamsStore.allTeams;
      case 'inbox':
        return inboxesStore.allInboxes;
      case 'label':
        return labelsStore.allLabels;
      default:
        return [];
    }
  });

  const isAgentType = $derived(type === 'agent');

  const reportKeys = $derived({
    CONVERSATIONS: 'conversations_count',
    ...(!isAgentType && {
      INCOMING_MESSAGES: 'incoming_messages_count',
    }),
    OUTGOING_MESSAGES: 'outgoing_messages_count',
    FIRST_RESPONSE_TIME: 'avg_first_response_time',
    RESOLUTION_TIME: 'avg_resolution_time',
    RESOLUTION_COUNT: 'resolutions_count',
    REPLY_TIME: 'reply_time',
  });

  onMount(() => {
    // Fetch data from appropriate store based on type
    switch (type) {
      case 'agent':
        agentsStore.fetchAgents();
        break;
      case 'team':
        teamsStore.fetchTeams();
        break;
      case 'inbox':
        inboxesStore.fetchInboxes();
        break;
      case 'label':
        labelsStore.fetchLabels();
        break;
    }
  });

  function fetchAllData() {
    if (selectedFilter) {
      reportsStore.fetchAccountSummary({
        from,
        to,
        type,
        id: selectedFilter.id,
        groupBy: groupBy.period,
        businessHours,
      });
      fetchChartData();
    }
  }

  function fetchChartData() {
    Object.keys(reportKeys).forEach(async key => {
      try {
        const metric = reportKeys[key as keyof typeof reportKeys];
        if (!metric) return;

        await reportsStore.fetchAccountReport({
          metric,
          from,
          to,
          type,
          id: selectedFilter.id,
          groupBy: groupBy.period,
          businessHours,
        });
      } catch (error) {
        console.error('Failed to fetch chart data:', error);
      }
    });
  }

  function downloadReports() {
    const dispatchMethods: Record<string, string> = {
      agent: 'downloadAgentReports',
      label: 'downloadLabelReports',
      inbox: 'downloadInboxReports',
      team: 'downloadTeamReports',
    };

    if (dispatchMethods[type]) {
      const fileName = generateFileName({ type, to, businessHours });
      const params = { from, to, fileName, businessHours };
      reportsStore.dispatchAction(dispatchMethods[type], params);
    }
  }

  function onDateRangeChange(event: CustomEvent) {
    const { from: newFrom, to: newTo, groupBy: newGroupBy } = event.detail;
    from = newFrom;
    to = newTo;
    groupByfilterItemsList = fetchFilterItems(newGroupBy);

    if (!groupByfilterItemsList.length) {
      return;
    }

    const filterItems = groupByfilterItemsList.filter(
      item => item.id === groupBy.id
    );

    if (filterItems.length > 0) {
      selectedGroupByFilter = filterItems[0];
    } else {
      selectedGroupByFilter = groupByfilterItemsList[0];
      groupBy = GROUP_BY_FILTER[selectedGroupByFilter.id];
    }

    fetchAllData();
  }

  function onFilterChange(event: CustomEvent) {
    if (event.detail) {
      selectedFilter = event.detail;
      fetchAllData();
    }
  }

  function onGroupByFilterChange(event: CustomEvent) {
    if (!event.detail?.id) {
      return;
    }

    groupBy = GROUP_BY_FILTER[event.detail.id];
    fetchAllData();
  }

  function fetchFilterItems(groupByPeriod: string) {
    switch (groupByPeriod) {
      case GROUP_BY_FILTER[2].period:
        return GROUP_BY_OPTIONS.WEEK;
      case GROUP_BY_FILTER[3].period:
        return GROUP_BY_OPTIONS.MONTH;
      case GROUP_BY_FILTER[4].period:
        return GROUP_BY_OPTIONS.YEAR;
      default:
        return GROUP_BY_OPTIONS.DAY;
    }
  }

  function onBusinessHoursToggle(event: CustomEvent) {
    businessHours = event.detail;
    fetchAllData();
  }
</script>

<ReportHeader headerTitle={reportTitle} {hasBackButton}>
  <Button size="sm" onclick={downloadReports}>
    <Download class="mr-2 h-4 w-4" />
    {downloadButtonLabel}
  </Button>
</ReportHeader>

{#if filterItemsList.length}
  <ReportFilters
    {type}
    {filterItemsList}
    {groupByfilterItemsList}
    {selectedGroupByFilter}
    currentFilter={selectedFilter}
    on:date-range-change={onDateRangeChange}
    on:filter-change={onFilterChange}
    on:group-by-filter-change={onGroupByFilterChange}
    on:business-hours-toggle={onBusinessHoursToggle}
  />
{/if}

{#if filterItemsList.length}
  <ReportContainer {groupBy} {reportKeys} />
{/if}
