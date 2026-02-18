<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { categoriesStore } from '$lib/portal/stores/categories.svelte';
  import SectionLayout from '../../settings/components/SectionLayout.svelte';
  import DataTable from '$lib/components/ui/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { Category } from '$lib/api/portal/categories';

  const accountId = $derived(Number($page.params.accountId));
  const categories = $derived(categoriesStore.allCategories);
  const loading = $derived(categoriesStore.uiFlags.isFetching);
  const portalSlug = 'default'; // Placeholder

  onMount(() => {
    categoriesStore.fetchCategories(portalSlug);
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
  <div slot="actions">
    <Button on:click={handleAdd}>
      <Plus class="mr-2 h-4 w-4" />
      Add Category
    </Button>
  </div>

  <DataTable
    {columns}
    data={categories}
    {loading}
    on:edit={e => handleEdit(e.detail)}
    emptyMessage="No categories found"
  />
</SectionLayout>
