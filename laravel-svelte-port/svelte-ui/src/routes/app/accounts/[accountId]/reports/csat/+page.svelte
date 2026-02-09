<script lang="ts">
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import ReportHeader from '$lib/components/reports/shared/ReportHeader.svelte';
  import CsatMetrics from '$lib/components/reports/csat/CsatMetrics.svelte';
  import CsatTable from '$lib/components/reports/csat/CsatTable.svelte';
  import CsatFilters from '$lib/components/reports/csat/CsatFilters.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Download } from 'lucide-svelte';
  import { csatStore } from '$lib/stores/csat.svelte';
  import { accountStore } from '$lib/stores/account.svelte';
  import { generateFileName } from '$lib/utils/downloadHelper';
  import { FEATURE_FLAGS } from '$lib/constants/featureFlags';

  let pageIndex = $state(0);
  let from = $state(0);
  let to = $state(0);
  let userIds = $state<number[]>([]);
  let inbox = $state<number | null>(null);
  let team = $state<number | null>(null);
  let rating = $state<number | null>(null);

  const requestPayload = $derived({
    from,
    to,
    user_ids: userIds,
    inbox_id: inbox,
    team_id: team,
    rating,
  });

  const isTeamsEnabled = $derived(
    accountStore.isFeatureEnabled(FEATURE_FLAGS.TEAM_MANAGEMENT)
  );

  function getAllData() {
    try {
      csatStore.getMetrics(requestPayload);
      getResponses();
    } catch (error) {
      console.error('Failed to fetch CSAT data:', error);
    }
  }

  function getResponses() {
    csatStore.get({
      page: pageIndex + 1,
      ...requestPayload,
    });
  }

  function downloadReports() {
    const type = 'csat';
    try {
      csatStore.downloadCSATReports({
        fileName: generateFileName({ type, to }),
        ...requestPayload,
      });
    } catch (error) {
      console.error('Failed to download CSAT reports:', error);
    }
  }

  function onPageNumberChange(event: CustomEvent) {
    pageIndex = event.detail;
    getResponses();
  }

  function onFilterChange(event: CustomEvent) {
    const {
      from: newFrom,
      to: newTo,
      selectedAgents,
      selectedInbox,
      selectedTeam,
      selectedRating,
    } = event.detail;

    from = newFrom;
    to = newTo;
    userIds = selectedAgents.map((el: any) => el.id);
    inbox = selectedInbox?.id;
    team = selectedTeam?.id;
    rating = selectedRating?.value;

    getAllData();
  }
</script>

<div class="h-full flex flex-col bg-background">
  <div class="w-full mx-auto max-w-[80rem] px-6">
    <ReportHeader headerTitle="CSAT Reports">
      <Button size="sm" onclick={downloadReports}>
        <Download class="mr-2 h-4 w-4" />
        Download CSAT Reports
      </Button>
    </ReportHeader>

    <div class="flex flex-col gap-6">
      <CsatFilters
        showTeamFilter={isTeamsEnabled}
        on:filter-change={onFilterChange}
      />
      <CsatMetrics filters={requestPayload} />
      <CsatTable {pageIndex} on:page-change={onPageNumberChange} />
    </div>
  </div>
</div>
