<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import SectionLayout from '../account/components/SectionLayout.svelte';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Table from '$lib/components/ui/table';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import { Plus, Pen, Trash2 } from 'lucide-svelte';
  import type { Team } from '$lib/api/teams';

  const accountId = $derived(Number($page.params.accountId));
  const teams = $derived(teamsStore.allTeams);
  const loading = $derived(teamsStore.uiFlags.isFetching);

  const currentUserRole = $derived(authStore.currentRole);
  const isAdmin = $derived(currentUserRole === 'administrator');

  onMount(() => {
    teamsStore.fetchTeams();
  });

  function handleAdd() {
    goto(`/app/accounts/${accountId}/settings/teams/new`);
  }

  function handleEdit(team: Team) {
    goto(`/app/accounts/${accountId}/settings/teams/${team.id}/edit`);
  }

  // Delete dialog state
  let showDeleteDialog = $state(false);
  let deletingTeam = $state<Team | null>(null);
  let isDeleting = $derived(teamsStore.uiFlags.isDeleting);

  function handleDeleteClick(team: Team) {
    deletingTeam = team;
    showDeleteDialog = true;
  }

  async function confirmDelete() {
    if (!deletingTeam) return;
    await teamsStore.deleteTeam(deletingTeam.id);
    showDeleteDialog = false;
    deletingTeam = null;
  }

  function cancelDelete() {
    showDeleteDialog = false;
    deletingTeam = null;
  }
</script>

<div class="flex flex-col max-w-4xl mx-auto w-full p-6">
  <BaseSettingsHeader title="Teams" />

  <div class="flex-grow flex-shrink min-w-0 space-y-6">
    <SectionLayout
      title="Teams List"
      description="Organize your agents into teams"
    >
      {#snippet headerActions()}
        {#if isAdmin}
          <Button onclick={handleAdd}>
            <Plus class="mr-2 h-4 w-4" />
            Create New Team
          </Button>
        {/if}
      {/snippet}

      {#if loading}
        <div class="flex justify-center items-center py-20">
          <div
            class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
          ></div>
        </div>
      {:else if teams.length === 0}
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
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
          </div>
          <h2 class="text-xl font-semibold mb-2">No teams found</h2>
          <p class="text-muted-foreground mb-4">
            Create a team to organize your agents and manage conversations
            efficiently.
          </p>
          {#if isAdmin}
            <Button onclick={handleAdd}>Create Your First Team</Button>
          {/if}
        </div>
      {:else}
        <div class="rounded-md border">
          <Table.Root>
            <Table.Body>
              {#each teams as team}
                <Table.Row class="hover:bg-muted/50 transition-colors">
                  <Table.Cell class="py-4 pl-4 pr-4 align-top w-full">
                    <span class="block font-medium capitalize text-base mb-1"
                      >{team.name}</span
                    >
                    <p class="text-sm text-muted-foreground m-0">
                      {team.description}
                    </p>
                  </Table.Cell>

                  <Table.Cell
                    class="py-4 pr-4 align-top text-right whitespace-nowrap"
                  >
                    <div class="flex justify-end gap-1">
                      {#if isAdmin}
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-muted-foreground hover:text-foreground"
                          title="Edit Team"
                          onclick={() => handleEdit(team)}
                        >
                          <Pen class="h-4 w-4" />
                        </Button>
                        <Button
                          variant="ghost"
                          size="icon"
                          class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                          title="Delete Team"
                          onclick={() => handleDeleteClick(team)}
                          disabled={isDeleting && deletingTeam?.id === team.id}
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

<!-- Delete Confirm Dialog -->
<AlertDialog.Root bind:open={showDeleteDialog}>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Team</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete <strong>{deletingTeam?.name}</strong>?
        This action cannot be undone.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel onclick={cancelDelete} disabled={isDeleting}>
        Cancel
      </AlertDialog.Cancel>
      <AlertDialog.Action
        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
        onclick={confirmDelete}
        disabled={isDeleting}
      >
        {isDeleting ? 'Deleting...' : 'Delete Team'}
      </AlertDialog.Action>
    </AlertDialog.Footer>
  </AlertDialog.Content>
</AlertDialog.Root>
