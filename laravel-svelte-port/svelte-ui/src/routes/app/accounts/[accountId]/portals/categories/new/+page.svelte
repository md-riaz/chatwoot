<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalCategoriesStore } from '$lib/stores/portalCategories.svelte';
  import { portalsStore } from '$lib/stores/portals.svelte';
  import SectionLayout from '../../../settings/account/components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { toast } from 'svelte-sonner';

  const accountId = $derived(Number($page.params.accountId));
  const portalSlugParam = $derived($page.url.searchParams.get('portal_slug'));

  let name = $state('');
  let slug = $state('');
  let description = $state('');
  let isSubmitting = $derived(portalCategoriesStore.uiFlags.isCreating);

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
      // locale: 'en' // Defaulting for now
    };

    const result = await portalCategoriesStore.createCategory(
      portalSlugParam,
      data
    );
    if (result) {
      toast.success('Category created successfully');
      goto(`/app/accounts/${accountId}/portals/categories`);
    }
  }

  function handleCancel() {
    goto(`/app/accounts/${accountId}/portals/categories`);
  }
</script>

<SectionLayout
  title="Create Category"
  description="Add a new category for articles"
>
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
      <p class="text-xs text-muted-foreground">
        Optional. Will be generated from name if left empty.
      </p>
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
        {isSubmitting ? 'Creating...' : 'Create Category'}
      </Button>
    </div>
  </form>
</SectionLayout>
