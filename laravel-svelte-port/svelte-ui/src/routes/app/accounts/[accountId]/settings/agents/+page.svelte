<script lang="ts">
  /**
   * Agents Management Page
   * List and manage agents/team members
   * Vue parity: app/javascript/dashboard/routes/dashboard/settings/agents/Index.vue
   */

  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { authStore } from '$lib/stores/auth.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Table from '$lib/components/ui/table';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import SectionLayout from '../account/components/SectionLayout.svelte';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';
  import type { Agent } from '$lib/api/agents';
  import AgentDialog from '$lib/components/agents/AgentDialog.svelte';
  import { Pen, Trash2, Plus } from 'lucide-svelte';

  let agents = $derived(agentsStore.sortedAgents);
  let isLoading = $derived(agentsStore.isLoading);
  let currentUserId = $derived(authStore.currentUser.id);

  let showCreateDialog = $state(false);
  let showEditDialog = $state(false);
  let editingAgent = $state<Agent | null>(null);

  // Delete confirm dialog state
  let showDeleteDialog = $state(false);
  let deletingAgent = $state<Agent | null>(null);

  // Computed: verified admins (for delete guard, matching Vue's verifiedAdministrators)
  let verifiedAdministrators = $derived(
    agents.filter(a => a.role === 'administrator' && a.confirmed)
  );

  onMount(() => {
    agentsStore.fetchAgents();
  });

  // Vue parity: showEditAction — hide edit for own row
  function showEditAction(agent: Agent) {
    return currentUserId !== agent.id;
  }

  // Vue parity: showDeleteAction — hide delete for self; guard last verified admin
  function showDeleteAction(agent: Agent) {
    if (currentUserId === agent.id) return false;
    if (!agent.confirmed) return true;
    if (agent.role === 'administrator') {
      return verifiedAdministrators.length !== 1;
    }
    return true;
  }

  function handleAddAgent() {
    showCreateDialog = true;
  }

  async function handleSubmitCreate(event: CustomEvent) {
    const data = event.detail;
    await agentsStore.createAgent(data);
    agentsStore.fetchAgents();
  }

  function handleEditAgent(agent: Agent) {
    editingAgent = agent;
    showEditDialog = true;
  }

  function handleDeleteClick(agent: Agent) {
    deletingAgent = agent;
    showDeleteDialog = true;
  }

  async function confirmDelete() {
    if (!deletingAgent) return;
    await agentsStore.deleteAgent(deletingAgent.id);
    showDeleteDialog = false;
    deletingAgent = null;
  }

  function cancelDelete() {
    showDeleteDialog = false;
    deletingAgent = null;
  }

  async function handleSubmitEdit(event: CustomEvent) {
    if (!editingAgent) return;
    const data = event.detail;
    await agentsStore.updateAgent(editingAgent.id, data);
    agentsStore.fetchAgents();
    editingAgent = null;
  }

  function getAgentRoleName(agent: Agent) {
    const role = agent.role || 'agent';
    return role.charAt(0).toUpperCase() + role.slice(1);
  }
</script>

<div class="flex flex-col w-full space-y-6">
  <BaseSettingsHeader title="Agents" />

  <div class="flex-grow flex-shrink min-w-0 space-y-6">
    <SectionLayout
      title="Agents List"
      description="Manage your team members and their permissions"
    >
      {#snippet headerActions()}
        <Button onclick={handleAddAgent}>
          <Plus class="mr-2 h-4 w-4" />
          Invite Agent
        </Button>
      {/snippet}

      {#if isLoading}
        <div class="flex justify-center items-center py-20">
          <div
            class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
          ></div>
        </div>
      {:else if agents.length === 0}
        <div
          class="text-center py-12 border rounded-lg bg-card text-card-foreground"
        >
          <div class="mb-4">
            <svg
              class="mx-auto h-16 w-16 text-muted-foreground"
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
          <h2 class="text-xl font-semibold mb-2">No agents found</h2>
          <p class="text-muted-foreground mb-4">
            Add team members to start collaborating
          </p>
          <Button onclick={handleAddAgent}>Invite Your First Agent</Button>
        </div>
      {:else}
        <div class="rounded-md border">
          <Table.Root>
            <Table.Body>
              {#each agents as agent}
                <Table.Row class="hover:bg-muted/50">
                  <Table.Cell class="py-4">
                    <div class="flex flex-row items-center gap-4">
                      <Avatar.Root class="h-10 w-10">
                        <Avatar.Image
                          src={agent.thumbnail || ''}
                          alt={agent.name}
                        />
                        <Avatar.Fallback
                          class="bg-primary/10 text-primary font-semibold"
                        >
                          {(agent.name || agent.email || 'A')
                            .charAt(0)
                            .toUpperCase()}
                        </Avatar.Fallback>
                      </Avatar.Root>
                      <div>
                        <span class="block font-medium capitalize">
                          {agent.name}
                        </span>
                        <span class="text-muted-foreground">{agent.email}</span>
                      </div>
                    </div>
                  </Table.Cell>

                  <Table.Cell class="py-4">
                    <span class="block font-medium w-fit capitalize">
                      {getAgentRoleName(agent)}
                    </span>
                  </Table.Cell>

                  <Table.Cell class="py-4">
                    <span class="text-muted-foreground">
                      {#if agent.confirmed}
                        Verified
                      {:else}
                        Verification Pending
                      {/if}
                    </span>
                  </Table.Cell>

                  <Table.Cell class="py-4 text-right">
                    <div class="flex justify-end gap-1">
                      {#if showEditAction(agent)}
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-muted-foreground hover:text-foreground"
                          title="Edit Agent"
                          onclick={() => handleEditAgent(agent)}
                        >
                          <Pen class="h-4 w-4" />
                        </Button>
                      {/if}
                      {#if showDeleteAction(agent)}
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                          title="Delete Agent"
                          onclick={() => handleDeleteClick(agent)}
                        >
                          <Trash2 class="h-4 w-4" />
                        </Button>
                      {/if}
                    </div>
                  </Table.Cell>
                </Table.Row>
              {/each}
            </Table.Body>
          </Table.Root>
        </div>
      {/if}
    </SectionLayout>
  </div>
</div>

<!-- Create Agent Dialog -->
<AgentDialog
  bind:open={showCreateDialog}
  mode="create"
  on:submit={handleSubmitCreate}
/>

<!-- Edit Agent Dialog -->
<AgentDialog
  bind:open={showEditDialog}
  mode="edit"
  agent={editingAgent}
  on:submit={handleSubmitEdit}
/>

<!-- Delete Confirm Dialog -->
<AlertDialog.Root bind:open={showDeleteDialog}>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Agent</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete <strong>{deletingAgent?.name}</strong>?
        This action cannot be undone.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel onclick={cancelDelete}>
        No, keep {deletingAgent?.name}
      </AlertDialog.Cancel>
      <AlertDialog.Action
        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
        onclick={confirmDelete}
      >
        Yes, delete {deletingAgent?.name}
      </AlertDialog.Action>
    </AlertDialog.Footer>
  </AlertDialog.Content>
</AlertDialog.Root>
