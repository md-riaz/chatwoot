<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalsStore } from '$lib/stores/portals.svelte';
  // Correct import path for SectionLayout based on previous file content
  import SectionLayout from '../../settings/account/components/SectionLayout.svelte';
  import DataTable from '$lib/components/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { Portal } from '$lib/api/portals';

  const accountId = $derived(Number($page.params.accountId));
  const portals = $derived(portalsStore.allPortals);
  const loading = $derived(portalsStore.uiFlags.isFetching);

  onMount(() => {
    portalsStore.fetchPortals();
  });

  function handleAdd() {
    goto(`/app/accounts/${accountId}/portals/settings/new`);
  }

  function handleEdit(portal: Portal) {
    goto(`/app/accounts/${accountId}/portals/settings/${portal.slug}`);
  }

  async function handleDelete(portal: Portal) {
    if (
      confirm(`Are you sure you want to delete the portal "${portal.name}"?`)
    ) {
      await portalsStore.deletePortal(portal.slug);
    }
  }

  const columns = [
    { key: 'name', label: 'Name' },
    { key: 'slug', label: 'Slug' },
    { key: 'custom_domain', label: 'Domain' },
    {
      key: 'archived',
      label: 'Status',
      formatter: (val: boolean) => (val ? 'Archived' : 'Active'),
    },
  ];
</script>

<SectionLayout
  title="Help Center Portals"
  description="Manage your help center portals"
>
  {#snippet headerActions()}
    <Button onclick={handleAdd}>
      <Plus class="mr-2 h-4 w-4" />
      Add Portal
    </Button>
  {/snippet}

  <DataTable
    {columns}
    data={portals}
    {loading}
    onRowClick={handleEdit}
    emptyMessage="No portals found"
  />
</SectionLayout>
