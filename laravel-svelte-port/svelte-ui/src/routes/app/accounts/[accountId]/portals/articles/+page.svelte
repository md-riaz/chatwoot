<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalArticlesStore } from '$lib/portal/stores/articles.svelte';
  import SectionLayout from '../../settings/account/components/SectionLayout.svelte';
  import DataTable from '$lib/components/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { Article } from '$lib/portal/api/types';

  const accountId = $derived(Number($page.params.accountId));
  const articles = $derived(portalArticlesStore.allArticles);
  const loading = $derived(portalArticlesStore.loading);

  // Need to get portal slug/id. Assuming single portal for now or passed in route.
  // The route is /app/accounts/[accountId]/portals/articles
  // We might need to fetch the portal first or assume a default.
  // For parity, let's assume we list articles for the "default" portal or all.
  const portalSlug = 'default'; // Placeholder: should be dynamic

  onMount(() => {
    portalArticlesStore.fetchArticlesByCategory(portalSlug);
  });

  function handleAdd() {
    goto(`/app/accounts/${accountId}/portals/articles/new`);
  }

  function handleEdit(article: Article) {
    goto(`/app/accounts/${accountId}/portals/articles/${article.id}/edit`);
  }

  const columns = [
    { key: 'title', label: 'Title' },
    {
      key: 'category',
      label: 'Category',
      formatter: (val: any) => val?.name || '-',
    },
    { key: 'status', label: 'Status' },
    { key: 'views', label: 'Views' },
  ];
</script>

<SectionLayout title="Articles" description="Manage your help center articles">
  {#snippet headerActions()}
    <Button onclick={handleAdd}>
      <Plus class="mr-2 h-4 w-4" />
      Add Article
    </Button>
  {/snippet}

  <DataTable
    {columns}
    data={articles}
    {loading}
    onRowClick={handleEdit}
    emptyMessage="No articles found"
  />
</SectionLayout>
