<script lang="ts">
  /**
   * Agents Management Page
   * List and manage agents/team members
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';

  let accountId = $derived($page.params.accountId);
  let agents = $derived(agentsStore.sortedAgents);
  let isLoading = $derived(agentsStore.isLoading);

  onMount(() => {
    agentsStore.fetchAgents();
  });

  function handleAddAgent() {
    goto(`/app/${accountId}/settings/agents/new`);
  }

  function handleViewAgent(agentId: number) {
    goto(`/app/${accountId}/settings/agents/${agentId}`);
  }

  function getRoleBadgeClass(role: string) {
    if (role === 'administrator') return 'bg-purple-100 text-purple-800';
    if (role === 'agent') return 'bg-blue-100 text-blue-800';
    return 'bg-gray-100 text-gray-800';
  }

  function getStatusBadgeClass(status: string) {
    if (status === 'online') return 'bg-green-100 text-green-800';
    if (status === 'busy') return 'bg-yellow-100 text-yellow-800';
    return 'bg-gray-100 text-gray-800';
  }
</script>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold">Agents</h1>
      <p class="text-muted-foreground mt-2">
        Manage your team members and their permissions
      </p>
    </div>
    <Button onclick={handleAddAgent}>Add Agent</Button>
  </div>

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if agents.length === 0}
    <Card.Root class="text-center py-12">
      <Card.Content>
        <div class="mb-4">
          <svg
            class="mx-auto h-16 w-16 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
            />
          </svg>
        </div>
        <h2 class="text-xl font-semibold mb-2">No agents yet</h2>
        <p class="text-gray-600 mb-4">Add team members to start collaborating</p>
        <Button onclick={handleAddAgent}>Add Your First Agent</Button>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="grid gap-4">
      {#each agents as agent}
        <Card.Root
          class="hover:shadow-md transition-shadow cursor-pointer"
          onclick={() => handleViewAgent(agent.id)}
        >
          <Card.Content class="p-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                  <span class="text-lg font-semibold text-primary">
                    {agent.name.charAt(0)}
                  </span>
                </div>
                <div>
                  <h3 class="font-semibold text-lg">{agent.name}</h3>
                  <p class="text-sm text-gray-600">{agent.email}</p>
                </div>
              </div>
              <div class="flex gap-2">
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium {getRoleBadgeClass(
                    agent.role
                  )}"
                >
                  {agent.role}
                </span>
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium {getStatusBadgeClass(
                    agent.availabilityStatus
                  )}"
                >
                  {agent.availabilityStatus}
                </span>
              </div>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {/if}
</div>
