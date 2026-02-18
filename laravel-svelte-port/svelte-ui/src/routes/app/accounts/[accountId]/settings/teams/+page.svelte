<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import SectionLayout from '../account/components/SectionLayout.svelte';
  import DataTable from '$lib/components/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { Team } from '$lib/api/teams';

  const accountId = $derived(Number($page.params.accountId));
  const teams = $derived(teamsStore.allTeams);
  const loading = $derived(teamsStore.uiFlags.isFetching);

  onMount(() => {
    teamsStore.fetchTeams();
  });

  function handleAdd() {
    goto(`/app/accounts/${accountId}/settings/teams/new`);
  }

  function handleEdit(team: Team) {
    goto(`/app/accounts/${accountId}/settings/teams/${team.id}/edit`);
  }

  async function handleDelete(team: Team) {
    if (confirm(`Are you sure you want to delete the team "${team.name}"?`)) {
      await teamsStore.deleteTeam(team.id);
    }
  }

  const columns = [
    { key: 'name', label: 'Name' },
    { key: 'description', label: 'Description' },
    {
      key: 'allow_auto_assign',
      label: 'Auto Assign',
      formatter: (val: boolean) => (val ? 'Yes' : 'No'),
    },
  ];
</script>

<SectionLayout title="Teams" description="Organize your agents into teams">
  <div slot="actions">
    <Button onclick={handleAdd}>
      <Plus class="mr-2 h-4 w-4" />
      Create New Team
    </Button>
  </div>

  <DataTable
    {columns}
    data={teams}
    {loading}
    onRowClick={handleEdit}
    emptyMessage="No teams found"
  />
</SectionLayout>
