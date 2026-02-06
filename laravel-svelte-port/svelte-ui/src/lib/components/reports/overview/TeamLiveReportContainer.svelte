<script lang="ts">
  import { onMount } from 'svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { useLiveRefresh } from '$lib/composables/useLiveRefresh.svelte';
  import MetricCard from './MetricCard.svelte';
  import TeamTable from './TeamTable.svelte';
  
  // Get reactive data from store
  const uiFlags = $derived(reportsStore.overviewUIFlags);
  const teamConversationMetric = $derived(reportsStore.teamConversationMetric);
  
  // Mock teams data - TODO: Get from teams store
  let teams = $state<Array<{
    id: number;
    name: string;
  }>>([]);
  
  // Data fetching
  async function fetchData() {
    await reportsStore.fetchTeamConversationMetric();
  }
  
  // Live refresh setup
  const { startRefetching } = useLiveRefresh(fetchData, { interval: 60000 });
  
  onMount(async () => {
    // TODO: Fetch teams from teams store
    // For now, using mock data that matches the metrics
    const { mockTeams } = await import('./test-data');
    teams = mockTeams;
    
    await fetchData();
    startRefetching();
  });
</script>

<div class="flex flex-row flex-wrap max-w-full">
  <MetricCard 
    header="Team Conversations"
    isLive={true}
    class="w-full"
  >
    {#snippet children()}
      <TeamTable
        {teams}
        teamMetrics={teamConversationMetric}
        isLoading={uiFlags.isFetchingTeamConversationMetric}
      />
    {/snippet}
  </MetricCard>
</div>