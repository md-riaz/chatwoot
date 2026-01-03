<script lang="ts">
  import { onMount } from 'svelte';
  import { reportsStore } from '$lib/stores/reports.svelte';
  import MetricsCards from '$lib/components/reports/MetricsCards.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { BarChart3, Users, Building2, RefreshCw } from '@lucide/svelte';
  
  const conversationMetrics = $derived(reportsStore.conversationMetrics);
  const agentMetrics = $derived(reportsStore.agentMetrics);
  const teamMetrics = $derived(reportsStore.teamMetrics);
  const topAgents = $derived(reportsStore.topAgents);
  const topTeams = $derived(reportsStore.topTeams);
  const isLoading = $derived(reportsStore.isLoading);
  const filters = $derived(reportsStore.filters);
  
  let since = $state(filters.since || '');
  let until = $state(filters.until || '');
  
  onMount(() => {
    reportsStore.fetchAllReports();
  });
  
  async function handleRefresh() {
    await reportsStore.fetchAllReports({ since, until });
  }
  
  function handleDateChange() {
    if (since && until) {
      reportsStore.setDateRange(since, until);
    }
  }
</script>

<div class="reports-page">
  <div class="header mb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <BarChart3 class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">Reports & Analytics</h1>
        </div>
        <p class="text-gray-600">
          Monitor team performance and conversation metrics
        </p>
      </div>
      
      <div class="flex items-center gap-2">
        <Input
          type="date"
          bind:value={since}
          onchange={handleDateChange}
          class="w-40"
        />
        <span class="text-sm text-gray-500">to</span>
        <Input
          type="date"
          bind:value={until}
          onchange={handleDateChange}
          class="w-40"
        />
        <Button onclick={handleRefresh} disabled={isLoading}>
          <RefreshCw class="h-4 w-4 mr-2 {isLoading ? 'animate-spin' : ''}" />
          Refresh
        </Button>
      </div>
    </div>
  </div>
  
  <!-- Conversation Metrics -->
  <div class="mb-6">
    <h2 class="text-lg font-semibold mb-4">Conversation Metrics</h2>
    <MetricsCards metrics={conversationMetrics} {isLoading} />
  </div>
  
  <!-- Top Agents -->
  {#if topAgents.length > 0}
    <div class="mb-6">
      <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <Users class="h-5 w-5" />
        Top Agents
      </h2>
      <Card.Root>
        <Card.Content class="p-0">
          <div class="divide-y">
            {#each topAgents as agent}
              <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                <div>
                  <p class="font-medium">{agent.agentName}</p>
                  <p class="text-sm text-gray-600">
                    {agent.conversationsCount} conversations • {agent.resolutionCount} resolved
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-600">Avg Response</p>
                  <p class="font-medium">
                    {Math.round(agent.avgFirstResponseTime / 60)}m
                  </p>
                </div>
              </div>
            {/each}
          </div>
        </Card.Content>
      </Card.Root>
    </div>
  {/if}
  
  <!-- Top Teams -->
  {#if topTeams.length > 0}
    <div class="mb-6">
      <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <Building2 class="h-5 w-5" />
        Top Teams
      </h2>
      <Card.Root>
        <Card.Content class="p-0">
          <div class="divide-y">
            {#each topTeams as team}
              <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                <div>
                  <p class="font-medium">{team.teamName}</p>
                  <p class="text-sm text-gray-600">
                    {team.conversationsCount} conversations
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-600">Avg Response</p>
                  <p class="font-medium">
                    {Math.round(team.avgFirstResponseTime / 60)}m
                  </p>
                </div>
              </div>
            {/each}
          </div>
        </Card.Content>
      </Card.Root>
    </div>
  {/if}
</div>

<style>
  .reports-page {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
  }
  
  .header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1.5rem;
  }
</style>
