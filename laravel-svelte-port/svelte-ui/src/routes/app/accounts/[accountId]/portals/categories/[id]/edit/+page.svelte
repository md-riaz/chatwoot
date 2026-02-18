<script lang="ts">
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalCategoriesStore } from '$lib/stores/portalCategories.svelte';
  import { portalsStore } from '$lib/stores/portals.svelte';
  import SectionLayout from '../../../../settings/account/components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { toast } from 'svelte-sonner';

  const accountId = $derived(Number($page.params.accountId));
  const categoryId = $derived(Number($page.params.id)); // Assuming structure [id]
  const portalSlugParam = $derived($page.url.searchParams.get('portal_slug'));

  let name = $state('');
  let slug = $state('');
  let description = $state('');
  let isSubmitting = $derived(portalCategoriesStore.uiFlags.isUpdating);
  let isLoading = $derived(portalCategoriesStore.uiFlags.isFetching); // Reusing fetch flag or need dedicated item fetch

  onMount(async () => {
    if (!portalSlugParam) return;

    if (portalCategoriesStore.allCategories.length === 0) {
      await portalCategoriesStore.fetchCategories(portalSlugParam);
    }
    const category = portalCategoriesStore.allCategories.find(
      c => c.id === categoryId
    );
    if (category) {
      name = category.name;
      slug = category.slug;
      description = category.description;
    }
  });

  async function handleSubmit() {
    if (!name) {
      toast.error('Name is required');
      return;
    }

    if (!portalSlugParam) {
      toast.error('Portal context missing');
      return;
    }

    const data = {
      name,
      slug,
      description,
    };

    const result = await portalCategoriesStore.updateCategory(
      portalSlugParam,
      categoryId,
      data
    );
    if (result) {
      toast.success('Category updated successfully');
      goto(`/app/accounts/${accountId}/portals/categories`);
    }
  }

  function handleCancel() {
    goto(`/app/accounts/${accountId}/portals/categories`);
  }
</script>

<SectionLayout title="Edit Category" description="Update category details">
  <form onsubmit={handleSubmit} class="space-y-6 max-w-2xl">
    <div class="grid w-full gap-1.5">
      <Label for="name">Name *</Label>
      <Input
        type="text"
        id="name"
        bind:value={name}
        placeholder="Getting Started"
        required
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="slug">Slug</Label>
      <Input
        type="text"
        id="slug"
        bind:value={slug}
        placeholder="getting-started"
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="description">Description</Label>
      <Textarea
        id="description"
        bind:value={description}
        placeholder="Category description..."
      />
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <Button variant="outline" type="button" onclick={handleCancel}
        >Cancel</Button
      >
      <Button type="submit" disabled={isSubmitting}>
        {isSubmitting ? 'Saving...' : 'Update Category'}
      </Button>
    </div>
  </form>
</SectionLayout>
