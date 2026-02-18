<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { agentsStore } from '$lib/stores/agents.svelte';
  import SectionLayout from '../../../components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { toast } from 'svelte-sonner';
  import * as Card from '$lib/components/ui/card';
  import { Trash2, UserPlus } from 'lucide-svelte';

  const accountId = $derived(Number($page.params.accountId));
  const teamId = $derived(Number($page.params.teamId)); // Note: folder name is [teamId] but param might be accessible via specific load or derived page
  // Adjusting to match probable folder structure `[teamId]/edit`

  let name = $state('');
  let description = $state('');
  let allowAutoAssign = $state(true);
  let isSubmitting = $derived(teamsStore.uiFlags.isUpdating);
  let isLoading = $derived(teamsStore.uiFlags.isFetchingItem);
  
  const teamMembers = $derived(teamsStore.teamMembers);
  const allAgents = $derived(agentsStore.allAgents);
  const availableAgents = $derived(
    allAgents.filter(agent => !teamMembers.some(member => member.id === agent.id))
  );

  onMount(async () => {
    if (teamId) {
      // Check if team is in store, else fetch
      // let team = teamsStore.allTeams.find(t => t.id === teamId);
      // if (!team) {
      //   await teamsStore.fetchTeam(teamId);
      //   team = teamsStore.selectedTeam; // Assumes fetchTeam sets selectedTeam or we find it again
      //   // Actually fetchTeam just updates the store list/map
      //   team = teamsStore.allTeams.find(t => t.id === teamId);
      // }
       await Promise.all([
         teamsStore.fetchTeam(teamId),
         teamsStore.fetchTeamMembers(teamId),
         agentsStore.fetchAgents()
       ]);

       const team = teamsStore.allTeams.find(t => t.id === teamId);
       if (team) {
         name = team.name;
         description = team.description;
         allowAutoAssign = team.allowAutoAssign;
       }
    }
  });

  async function handleSubmit() {
    if (!name) {
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
      // goto(`/app/accounts/${accountId}/settings/teams`);
    }
  }

  // function handleCancel() {
  //   goto(`/app/accounts/${accountId}/settings/teams`);
  // }

  async function handleAddMember(agentId: number) {
    const success = await teamsStore.addTeamMember(teamId, [agentId]);
    if (success) toast.success('Member added to team');
  }

  async function handleRemoveMember(userId: number) {
    if (confirm('Are you sure you want to remove this member?')) {
      const success = await teamsStore.removeTeamMember(teamId, userId);
      if (success) toast.success('Member removed from team');
    }
  }
</script>

<div class="space-y-8">
  <SectionLayout
    title="Edit Team"
    description="Update team details"
  >
    {#if isLoading}
      <div>Loading...</div>
    {:else}
      <form on:submit|preventDefault={handleSubmit} class="space-y-6 max-w-2xl">
        <div class="grid w-full gap-1.5">
          <Label for="name">Name *</Label>
          <Input
            type="text"
            id="name"
            bind:value={name}
            placeholder="Sales Team"
            required
          />
        </div>

        <div class="grid w-full gap-1.5">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            bind:value={description}
            placeholder="Handles sales inquiries..."
          />
        </div>

        <div class="flex items-center space-x-2">
          <Checkbox id="allow_auto_assign" bind:checked={allowAutoAssign} />
          <Label
            for="allow_auto_assign"
            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
          >
            Allow auto assignment for this team
          </Label>
        </div>

        <div class="flex justify-end gap-2 pt-4">
          <Button variant="outline" type="button" on:click={() => goto(`/app/accounts/${accountId}/settings/teams`)}>Back to List</Button>
          <Button type="submit" disabled={isSubmitting}>
            {isSubmitting ? 'Saving...' : 'Update Team'}
          </Button>
        </div>
      </form>
    {/if}
  </SectionLayout>

  <SectionLayout
    title="Team Members"
    description="Manage who belongs to this team"
  >
    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Current Members -->
      <Card.Root>
        <Card.Header>
          <Card.Title>Current Members ({teamMembers.length})</Card.Title>
        </Card.Header>
        <Card.Content class="space-y-4">
          {#each teamMembers as member}
            <div class="flex items-center justify-between border-b pb-2 last:border-0">
              <div>
                <p class="font-medium text-sm">{member.name}</p>
                <p class="text-xs text-muted-foreground">{member.email}</p>
              </div>
              <Button variant="ghost" size="sm" on:click={() => handleRemoveMember(member.id)}>
                <Trash2 class="h-4 w-4 text-destructive" />
              </Button>
            </div>
          {/each}
          {#if teamMembers.length === 0}
            <p class="text-sm text-muted-foreground italic">No members in this team.</p>
          {/if}
        </Card.Content>
      </Card.Root>

      <!-- Add Members -->
      <Card.Root>
        <Card.Header>
          <Card.Title>Add Members</Card.Title>
        </Card.Header>
        <Card.Content class="space-y-4">
          <div class="max-h-[300px] overflow-y-auto space-y-2">
            {#each availableAgents as agent}
              <div class="flex items-center justify-between rounded-lg border p-2">
                <div>
                  <p class="font-medium text-sm">{agent.name}</p>
                  <p class="text-xs text-muted-foreground">{agent.email}</p>
                </div>
                <Button variant="outline" size="sm" on:click={() => handleAddMember(agent.id)}>
                  <UserPlus class="h-4 w-4 mr-1" />
                  Add
                </Button>
              </div>
            {/each}
            {#if availableAgents.length === 0}
              <p class="text-sm text-muted-foreground italic">No more agents to add.</p>
            {/if}
          </div>
        </Card.Content>
      </Card.Root>
    </div>
  </SectionLayout>
</div>
