<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalCategoriesStore } from '$lib/stores/portalCategories.svelte';
  import { portalsStore } from '$lib/stores/portals.svelte';
  import SectionLayout from '../../settings/account/components/SectionLayout.svelte';
  import DataTable from '$lib/components/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { PortalCategory } from '$lib/api/portalCategories';

  const accountId = $derived(Number($page.params.accountId));
  const categories = $derived(portalCategoriesStore.allCategories);
  const loading = $derived(portalCategoriesStore.uiFlags.isFetching);

  // Dynamic portal selection
  const portalSlug = $derived(
    portalsStore.selectedPortalSlug ||
      (portalsStore.allPortals.length > 0
        ? portalsStore.allPortals[0].slug
        : null)
  );

  $effect(() => {
    if (portalsStore.allPortals.length === 0) {
      portalsStore.fetchPortals();
    }
  });

  $effect(() => {
    if (portalSlug) {
      portalCategoriesStore.fetchCategories(portalSlug);
    }
  });

  function handleAdd() {
    if (portalSlug) {
      goto(
        `/app/accounts/${accountId}/portals/categories/new?portal_slug=${portalSlug}`
      );
    }
  }

  function handleEdit(category: PortalCategory) {
    if (portalSlug) {
      goto(
        `/app/accounts/${accountId}/portals/categories/${category.id}/edit?portal_slug=${portalSlug}`
      );
    }
  }

  const columns = [
    { key: 'name', label: 'Name' },
    { key: 'slug', label: 'Slug' },
    { key: 'locale', label: 'Locale' },
  ];
</script>

<SectionLayout
  title="Categories"
  description="Organize articles into categories"
>
  {#snippet headerActions()}
    <div class="flex items-center gap-2">
      <!-- TODO: Add Portal Selector if multiple portals exist -->
      <Button onclick={handleAdd} disabled={!portalSlug}>
        <Plus class="mr-2 h-4 w-4" />
        Add Category
      </Button>
    </div>
  {/snippet}

  {#if !portalSlug && !portalsStore.uiFlags.isFetching}
    <div class="p-4 text-center text-muted-foreground">
      No portals found. Please create a portal first.
    </div>
  {:else}
    <DataTable
      {columns}
      data={categories}
      {loading}
      onRowClick={handleEdit}
      emptyMessage="No categories found"
    />
  {/if}
</SectionLayout>
