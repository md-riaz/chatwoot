<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalArticlesStore } from '$lib/stores/portalArticles.svelte';
  import { portalCategoriesStore } from '$lib/stores/portalCategories.svelte';
  import SectionLayout from '../../../../settings/account/components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Select } from '$lib/components/ui/select';
  import { toast } from 'svelte-sonner';

  const accountId = $derived(Number($page.params.accountId));
  const portalSlugParam = $derived($page.url.searchParams.get('portal_slug'));
  const categories = $derived(portalCategoriesStore.allCategories);

  let title = $state('');
  let slug = $state('');
  let content = $state('');
  let description = $state('');
  let categoryId = $state<number | null>(null);
  let status = $state<'draft' | 'published'>('draft');

  let isSubmitting = $derived(portalArticlesStore.uiFlags.isCreating);

  onMount(() => {
    if (portalSlugParam && portalCategoriesStore.allCategories.length === 0) {
      portalCategoriesStore.fetchCategories(portalSlugParam);
    }
  });

  async function handleSubmit() {
    if (!title || !categoryId) {
      toast.error('Title and Category are required');
      return;
    }

    if (!portalSlugParam) {
      toast.error('Portal context missing');
      return;
    }

    const data = {
      title,
      slug,
      content,
      description,
      category_id: categoryId,
      status,
    };

    const result = await portalArticlesStore.createArticle(
      portalSlugParam,
      data
    );
    if (result) {
      toast.success('Article created successfully');
      goto(`/app/accounts/${accountId}/portals/articles`);
    }
  }

  function handleCancel() {
    goto(`/app/accounts/${accountId}/portals/articles`);
  }
</script>

<SectionLayout title="Create Article" description="Write a new help article">
  <form onsubmit={handleSubmit} class="space-y-6 max-w-4xl">
    <div class="grid w-full gap-1.5">
      <Label for="title">Title *</Label>
      <Input
        type="text"
        id="title"
        bind:value={title}
        placeholder="How to use feature X"
        required
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="slug">Slug</Label>
      <Input
        type="text"
        id="slug"
        bind:value={slug}
        placeholder="how-to-use-feature-x"
      />
      <p class="text-xs text-muted-foreground">
        Optional. Will be generated from title if left empty.
      </p>
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="category">Category *</Label>
      <select
        id="category"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        bind:value={categoryId}
        required
      >
        <option value={null} disabled>Select a category</option>
        {#each categories as category}
          <option value={category.id}>{category.name}</option>
        {/each}
      </select>
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="description">Short Description</Label>
      <Textarea
        id="description"
        bind:value={description}
        placeholder="Brief summary for search results..."
        rows={3}
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="content">Content</Label>
      <Textarea
        id="content"
        bind:value={content}
        placeholder="Article content (Markdown supported)..."
        rows={15}
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="status">Status</Label>
      <select
        id="status"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
        bind:value={status}
      >
        <option value="draft">Draft</option>
        <option value="published">Published</option>
      </select>
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <Button variant="outline" type="button" onclick={handleCancel}
        >Cancel</Button
      >
      <Button type="submit" disabled={isSubmitting}>
        {isSubmitting ? 'Creating...' : 'Create Article'}
      </Button>
    </div>
  </form>
</SectionLayout>
