<script lang="ts">
  import { onMount } from 'svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { useLiveRefresh } from '$lib/composables/useLiveRefresh.svelte';
  import MetricCard from './MetricCard.svelte';
  import AgentTable from './AgentTable.svelte';
  
  // Get reactive data from store
  const uiFlags = $derived(reportsStore.overviewUIFlags);
  const agentConversationMetric = $derived(reportsStore.agentConversationMetric);
  
  // Mock agents data - TODO: Get from agents store
  let agents = $state<Array<{
    id: number;
    name: string;
    availableName?: string;
    email: string;
    thumbnail?: string;
    availabilityStatus?: 'online' | 'busy' | 'offline';
  }>>([]);
  
  // Data fetching
  async function fetchData() {
    await reportsStore.fetchAgentConversationMetric();
  }
  
  // Live refresh setup
  const { startRefetching } = useLiveRefresh(fetchData, { interval: 60000 });
  
  onMount(async () => {
    // TODO: Fetch agents from agents store
    // For now, using mock data that matches the metrics
    const { mockAgents } = await import('./test-data');
    agents = mockAgents;
    
    await fetchData();
    startRefetching();
  });
</script>

<div class="flex flex-row flex-wrap max-w-full">
  <MetricCard 
    header="Agent Conversations"
    isLive={true}
    class="w-full"
  >
    {#snippet children()}
      <AgentTable
        {agents}
        agentMetrics={agentConversationMetric}
        isLoading={uiFlags.isFetchingAgentConversationMetric}
      />
    {/snippet}
  </MetricCard>
</div>