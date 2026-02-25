<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import SectionLayout from '../../../account/components/SectionLayout.svelte';
  import BaseSettingsHeader from '../../../components/BaseSettingsHeader.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import * as Table from '$lib/components/ui/table';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import { toast } from 'svelte-sonner';
  import type { Team } from '$lib/api/teams';

  const accountId = $derived(Number($page.params.accountId));
  const teamId = $derived(Number($page.params.teamId));

  // Team Form State
  let name = $state('');
  let description = $state('');
  let allowAutoAssign = $state(true);

  let isUpdatingTeam = $derived(teamsStore.uiFlags.isUpdating);
  let isLoading = $derived(teamsStore.uiFlags.isFetchingItem);

  // Agents State
  let selectedAgents = $state<number[]>([]);
  let isUpdatingMembers = $derived(teamsStore.uiFlags.isUpdatingMembers);

  const teamMembers = $derived(teamsStore.selectedTeamMembers);
  const agents = $derived(agentsStore.sortedAgents);

  let allSelected = $derived(
    agents.length > 0 && selectedAgents.length === agents.length
  );

  // Delete State
  let showDeleteDialog = $state(false);
  let isDeleting = $derived(teamsStore.uiFlags.isDeleting);

  onMount(async () => {
    if (teamId) {
      await Promise.all([
        teamsStore.fetchTeam(teamId),
        teamsStore.fetchTeamMembers(teamId),
        agentsStore.fetchAgents(),
      ]);

      const team = teamsStore.allTeams.find(t => t.id === teamId);
      if (team) {
        name = team.name;
        description = team.description || '';
        allowAutoAssign = team.allowAutoAssign;

        selectedAgents =
          teamsStore.teamMembers.get(teamId)?.map(member => member.id) || [];
      }
    }
  });

  async function handleUpdateTeam() {
    if (!name.trim()) {
      toast.error('Team name is required');
      return;
    }

    const data = {
      name,
      description,
      allow_auto_assign: allowAutoAssign,
    };

    const result = await teamsStore.updateTeam(teamId, data);
    if (result) {
      toast.success('Team updated successfully');
    }
  }

  function toggleAllAgents(checked: boolean) {
    if (checked) {
      selectedAgents = agents.map(a => a.id);
    } else {
      selectedAgents = [];
    }
  }

  function toggleAgent(agentId: number, checked: boolean) {
    if (checked) {
      if (!selectedAgents.includes(agentId)) {
        selectedAgents = [...selectedAgents, agentId];
      }
    } else {
      selectedAgents = selectedAgents.filter(id => id !== agentId);
    }
  }

  async function handleUpdateMembers() {
    const success = await teamsStore.updateTeamMembers(teamId, selectedAgents);
    if (success) {
      toast.success('Team members updated successfully');
    } else {
      toast.error('Failed to update team members');
    }
  }

  async function confirmDelete() {
    await teamsStore.deleteTeam(teamId);
    toast.success('Team deleted successfully');
    showDeleteDialog = false;
    goto(`/app/accounts/${accountId}/settings/teams`);
  }
</script>

<div class="flex flex-col w-full space-y-8">
  <BaseSettingsHeader title="Edit Team" />

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
      ></div>
    </div>
  {:else}
    <div class="space-y-6">
      <SectionLayout
        title="Team Details"
        description="Update the basic information for this team."
      >
        <form
          onsubmit={e => {
            e.preventDefault();
            handleUpdateTeam();
          }}
          class="space-y-6 max-w-2xl"
        >
          <div class="grid w-full gap-1.5">
            <Label for="name">Name *</Label>
            <Input
              type="text"
              id="name"
              bind:value={name}
              placeholder="Sales, Support, etc."
              required
            />
          </div>

          <div class="grid w-full gap-1.5">
            <Label for="description">Description (Optional)</Label>
            <Textarea
              id="description"
              bind:value={description}
              placeholder="Handles customer inquiries..."
            />
          </div>

          <div class="flex items-center space-x-2">
            <Checkbox id="allow_auto_assign" bind:checked={allowAutoAssign} />
            <div class="space-y-1 leading-none">
              <Label
                for="allow_auto_assign"
                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
              >
                Allow auto assignment for this team
                <p class="text-xs text-muted-foreground mt-1 font-normal">
                  If enabled, new conversations will be automatically assigned
                  to agents in this team based on their availability and
                  capacity.
                </p>
              </Label>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <Button
              variant="outline"
              type="button"
              onclick={() => goto(`/app/accounts/${accountId}/settings/teams`)}
            >
              Cancel
            </Button>
            <Button type="submit" disabled={isUpdatingTeam || !name.trim()}>
              {isUpdatingTeam ? 'Saving...' : 'Update Team Info'}
            </Button>
          </div>
        </form>
      </SectionLayout>

      <SectionLayout
        title="Team Members"
        description="Manage the agents assigned to this team."
      >
        <div
          class="rounded-md border bg-card mb-4 min-h-[300px] max-h-[500px] overflow-y-auto w-full"
        >
          <Table.Root>
            <Table.Header>
              <Table.Row class="hover:bg-transparent">
                <Table.Head class="w-12 pl-4">
                  <Checkbox
                    checked={allSelected}
                    onCheckedChange={v => toggleAllAgents(v === true)}
                    aria-label="Select all"
                  />
                </Table.Head>
                <Table.Head>Agent</Table.Head>
                <Table.Head>Email</Table.Head>
              </Table.Row>
            </Table.Header>
            <Table.Body>
              {#if agents.length === 0}
                <Table.Row class="hover:bg-transparent">
                  <Table.Cell
                    colspan={3}
                    class="h-24 text-center text-muted-foreground"
                  >
                    No agents available to add.
                  </Table.Cell>
                </Table.Row>
              {:else}
                {#each agents as agent}
                  <Table.Row
                    class={selectedAgents.includes(agent.id)
                      ? 'bg-muted/50'
                      : 'hover:bg-muted/30'}
                  >
                    <Table.Cell class="pl-4">
                      <Checkbox
                        checked={selectedAgents.includes(agent.id)}
                        onCheckedChange={v => toggleAgent(agent.id, v === true)}
                        aria-label={`Select ${agent.name}`}
                      />
                    </Table.Cell>
                    <Table.Cell>
                      <div class="flex items-center gap-3">
                        <Avatar.Root class="h-8 w-8">
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
                        <span class="font-medium capitalize text-foreground"
                          >{agent.name}</span
                        >
                      </div>
                    </Table.Cell>
                    <Table.Cell class="text-muted-foreground"
                      >{agent.email || '---'}</Table.Cell
                    >
                  </Table.Row>
                {/each}
              {/if}
            </Table.Body>
          </Table.Root>
        </div>

        <div class="flex items-center justify-between mt-6">
          <p class="text-sm font-medium text-muted-foreground">
            {selectedAgents.length} out of {agents.length} agents selected
          </p>
          <Button onclick={handleUpdateMembers} disabled={isUpdatingMembers}>
            {isUpdatingMembers ? 'Saving Members...' : 'Update Team Members'}
          </Button>
        </div>
      </SectionLayout>

      <SectionLayout
        title="Danger Zone"
        description="Irreversible actions for this team."
      >
        <div
          class="flex items-center justify-between p-4 border rounded-md border-destructive/20 bg-destructive/5"
        >
          <div>
            <h4 class="font-medium text-destructive mb-1">Delete this team</h4>
            <p class="text-sm text-destructive-foreground/75">
              Once you delete a team, there is no going back. Please be certain.
            </p>
          </div>
          <Button
            variant="destructive"
            onclick={() => (showDeleteDialog = true)}
          >
            Delete Team
          </Button>
        </div>
      </SectionLayout>
    </div>
  {/if}
</div>

<!-- Delete Confirm Dialog -->
<AlertDialog.Root bind:open={showDeleteDialog}>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Team</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete <strong>{name}</strong>? This action
        cannot be undone.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel
        onclick={() => (showDeleteDialog = false)}
        disabled={isDeleting}
      >
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
