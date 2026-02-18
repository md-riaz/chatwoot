<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalArticlesStore } from '$lib/stores/portalArticles.svelte';
  import { portalsStore } from '$lib/stores/portals.svelte';
  import SectionLayout from '../../settings/account/components/SectionLayout.svelte';
  import DataTable from '$lib/components/DataTable.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from 'lucide-svelte';
  import type { PortalArticle } from '$lib/api/portalArticles';

  const accountId = $derived(Number($page.params.accountId));
  const articles = $derived(portalArticlesStore.allArticles);
  const loading = $derived(portalArticlesStore.uiFlags.isFetching);

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
      portalArticlesStore.fetchArticles(portalSlug);
    }
  });

  function handleAdd() {
    if (portalSlug) {
      goto(
        `/app/accounts/${accountId}/portals/articles/new?portal_slug=${portalSlug}`
      );
    }
  }

  function handleEdit(article: PortalArticle) {
    if (portalSlug) {
      goto(
        `/app/accounts/${accountId}/portals/articles/${article.id}/edit?portal_slug=${portalSlug}`
      );
    }
  }

  const columns = [
    { key: 'title', label: 'Title' },
    { key: 'status', label: 'Status' },
    { key: 'views', label: 'Views' },
  ];
</script>

<SectionLayout title="Articles" description="Manage your help center articles">
  {#snippet headerActions()}
    <div class="flex items-center gap-2">
      <!-- TODO: Add Portal Selector -->
      <Button onclick={handleAdd} disabled={!portalSlug}>
        <Plus class="mr-2 h-4 w-4" />
        Add Article
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
      data={articles}
      {loading}
      onRowClick={handleEdit}
      emptyMessage="No articles found"
    />
  {/if}
</SectionLayout>
