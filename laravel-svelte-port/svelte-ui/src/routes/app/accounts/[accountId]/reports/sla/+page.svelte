<script lang="ts">
  import { onMount } from 'svelte';
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import SLAMetrics from '$lib/components/reports/sla/SLAMetrics.svelte';
  import SLATable from '$lib/components/reports/sla/SLATable.svelte';
  import SLAReportFilters from '$lib/components/reports/sla/SLAReportFilters.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Download } from 'lucide-svelte';
  import { slaReportsStore } from '$lib/stores/slaReports.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { slaStore } from '$lib/stores/sla.svelte';
  import { generateFileName } from '$lib/utils/downloadHelper';

  let pageNumber = $state(1);
  let activeFilter = $state({
    from: 0,
    to: 0,
    assigned_agent_id: null,
    inbox_id: null,
    team_id: null,
    sla_policy_id: null,
    label_list: null,
  });

  const slaReports = $derived(slaReportsStore.getAll());
  const slaMetrics = $derived(slaReportsStore.getMetrics());
  const slaMeta = $derived(slaReportsStore.getMeta());
  const uiFlags = $derived(slaReportsStore.getUIFlags());

  onMount(() => {
    agentsStore.fetchAgents();
    inboxesStore.fetchInboxes();
    teamsStore.fetchTeams();
    labelsStore.fetchLabels();
    slaStore.fetchSLAs();
    fetchSLAMetrics();
    fetchSLAReports();
  });

  function fetchSLAReports({ pageNumber: page }: { pageNumber?: number } = {}) {
    slaReportsStore.get({
      page: page || pageNumber,
      ...activeFilter,
    });
  }

  function fetchSLAMetrics() {
    slaReportsStore.getMetrics(activeFilter);
  }

  function onPageChange(event: CustomEvent) {
    fetchSLAReports({ pageNumber: event.detail });
  }

  function onFilterChange(event: CustomEvent) {
    activeFilter = event.detail;
    fetchSLAReports();
    fetchSLAMetrics();
  }

  function downloadReports() {
    const type = 'sla';
    try {
      slaReportsStore.download({
        fileName: generateFileName({ type, to: activeFilter.to }),
        ...activeFilter,
      });
    } catch (error) {
      console.error('Failed to download SLA reports:', error);
    }
  }
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <ReportHeader headerTitle="SLA Reports">
      <Button size="sm" onclick={downloadReports}>
        <Download class="mr-2 h-4 w-4" />
        Download SLA Reports
      </Button>
    </ReportHeader>

    <div class="flex flex-col flex-1 gap-6">
      <SLAReportFilters on:filter-change={onFilterChange} />
      <SLAMetrics
        hitRate={slaMetrics.hitRate}
        noOfBreaches={slaMetrics.numberOfSLAMisses}
        noOfConversations={slaMetrics.numberOfConversations}
        isLoading={uiFlags.isFetchingMetrics}
      />
      <SLATable
        {slaReports}
        isLoading={uiFlags.isFetching}
        currentPage={Number(slaMeta.currentPage)}
        totalCount={Number(slaMeta.count)}
        on:page-change={onPageChange}
      />
    </div>
  </div>
</div>
