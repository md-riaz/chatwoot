<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalCategoriesStore } from '$lib/portal/stores/categories.svelte';
  import SectionLayout from '../../settings/account/components/SectionLayout.svelte';
  import DataTable from '$lib/components/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { Category } from '$lib/portal/api/types';

  const accountId = $derived(Number($page.params.accountId));
  const categories = $derived(portalCategoriesStore.allCategories);
  const loading = $derived(portalCategoriesStore.errorMessage !== null); // Temporary loading check
  const portalSlug = 'default'; // Placeholder

  onMount(() => {
    portalCategoriesStore.fetchCategories(portalSlug);
  });

  function handleAdd() {
    goto(`/app/accounts/${accountId}/portals/categories/new`);
  }

  function handleEdit(category: Category) {
    goto(`/app/accounts/${accountId}/portals/categories/${category.id}/edit`);
  }

  const columns = [
    { key: 'name', label: 'Name' },
    { key: 'slug', label: 'Slug' },
    { key: 'articles_count', label: 'Articles' },
  ];
</script>

<SectionLayout
  title="Categories"
  description="Organize articles into categories"
>
  {#snippet headerActions()}
    <Button onclick={handleAdd}>
      <Plus class="mr-2 h-4 w-4" />
      Add Category
    </Button>
  {/snippet}

  <DataTable
    {columns}
    data={categories}
    {loading}
    onRowClick={handleEdit}
    emptyMessage="No categories found"
  />
</SectionLayout>
