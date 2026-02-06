<script lang="ts">
  import { onMount } from 'svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import { useLiveRefresh } from '$lib/composables/useLiveRefresh.svelte';
  import MetricCard from './MetricCard.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { ChevronDown } from 'lucide-svelte';
  
  // Get reactive data from store
  const uiFlags = $derived(reportsStore.overviewUIFlags);
  const accountConversationMetric = $derived(reportsStore.accountConversationMetric);
  const agentStatus = $derived(reportsStore.agentStatus);
  
  // Team filtering state
  let selectedTeam = $state<number | null>(null);
  let teams = $state<Array<{ id: number; name: string }>>([]);
  let showTeamDropdown = $state(false);
  
  // Computed values
  const selectedTeamLabel = $derived(
    !selectedTeam 
      ? 'All Teams' 
      : teams.find(t => t.id === selectedTeam)?.name || 'All Teams'
  );
  
  const conversationMetrics = $derived({
    'Open': accountConversationMetric.open,
    'Unattended': accountConversationMetric.unattended,
    'Unassigned': accountConversationMetric.unassigned
  });
  
  const agentStatusMetrics = $derived({
    'Online': agentStatus.online,
    'Busy': agentStatus.busy,
    'Offline': agentStatus.offline
  });
  
  // Data fetching
  async function fetchData() {
    const params = selectedTeam ? { teamId: selectedTeam } : {};
    await Promise.all([
      reportsStore.fetchAccountConversationMetric(params),
      reportsStore.fetchAgentStatus()
    ]);
  }
  
  // Live refresh setup
  const { startRefetching } = useLiveRefresh(fetchData, { interval: 60000 });
  
  // Team selection handler
  function handleTeamSelect(teamId: number | null) {
    selectedTeam = teamId;
    showTeamDropdown = false;
    fetchData();
  }
  
  onMount(async () => {
    // TODO: Fetch teams from teams store
    // For now, using mock data
    const { mockTeams } = await import('./test-data');
    teams = mockTeams;
    
    await fetchData();
    startRefetching();
  });
</script>

<div class="flex flex-col items-center md:flex-row gap-4">
  <!-- Conversation Metrics (65% width) -->
  <div class="flex-1 w-full max-w-full md:w-[65%] md:max-w-[65%] conversation-metric">
    <MetricCard
      header="Open Conversations"
      isLive={true}
      isLoading={uiFlags.isFetchingAccountConversationMetric}
      loadingMessage="Loading conversation metrics..."
    >
      {#snippet control()}
        {#if teams.length > 0}
          <DropdownMenu.Root bind:open={showTeamDropdown}>
            <DropdownMenu.Trigger asChild>
              <Button
                variant="outline"
                size="sm"
                class="capitalize"
              >
                {selectedTeamLabel}
                <ChevronDown class="ml-2 h-4 w-4" />
              </Button>
            </DropdownMenu.Trigger>
            <DropdownMenu.Content class="w-56">
              <DropdownMenu.Item onclick={() => handleTeamSelect(null)}>
                All Teams
              </DropdownMenu.Item>
              <DropdownMenu.Separator />
              {#each teams as team}
                <DropdownMenu.Item onclick={() => handleTeamSelect(team.id)}>
                  {team.name}
                </DropdownMenu.Item>
              {/each}
            </DropdownMenu.Content>
          </DropdownMenu.Root>
        {/if}
      {/snippet}
      
      {#snippet children()}
        {#each Object.entries(conversationMetrics) as [name, value]}
          <div class="flex-1 min-w-0 pb-2">
            <h3 class="text-base text-slate-600 dark:text-slate-400">
              {name}
            </h3>
            <p class="text-slate-900 dark:text-slate-100 text-3xl mb-0 mt-1">
              {value}
            </p>
          </div>
        {/each}
      {/snippet}
    </MetricCard>
  </div>
  
  <!-- Agent Status (35% width) -->
  <div class="flex-1 w-full max-w-full md:w-[35%] md:max-w-[35%]">
    <MetricCard
      header="Agent status"
      isLive={true}
      isLoading={uiFlags.isFetchingAgentStatus}
      loadingMessage="Loading agent status..."
    >
      {#snippet children()}
        {#each Object.entries(agentStatusMetrics) as [name, value]}
          <div class="flex-1 min-w-0 pb-2">
            <h3 class="text-base text-slate-600 dark:text-slate-400">
              {name}
            </h3>
            <p class="text-slate-900 dark:text-slate-100 text-3xl mb-0 mt-1">
              {value}
            </p>
          </div>
        {/each}
      {/snippet}
    </MetricCard>
  </div>
</div>