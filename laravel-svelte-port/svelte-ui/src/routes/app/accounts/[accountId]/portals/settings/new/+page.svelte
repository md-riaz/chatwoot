<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { portalsStore } from '$lib/stores/portals.svelte';
  import SectionLayout from '../../../settings/account/components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { toast } from 'svelte-sonner';

  const accountId = $derived(Number($page.params.accountId));

  let name = $state('');
  let slug = $state('');
  let domain = $state('');
  let isSubmitting = $derived(portalsStore.uiFlags.isCreating);

  async function handleSubmit() {
    if (!name || !slug) {
      toast.error('Name and Slug are required');
      return;
    }

    const data = {
      name,
      slug,
      custom_domain: domain,
    };

    const result = await portalsStore.createPortal(data);
    if (result) {
      toast.success('Portal created successfully');
      goto(`/app/accounts/${accountId}/portals/settings`);
    }
  }

  function handleCancel() {
    goto(`/app/accounts/${accountId}/portals/settings`);
  }
</script>

<SectionLayout
  title="Create Portal"
  description="Create a new help center portal"
>
  <form onsubmit={handleSubmit} class="space-y-6 max-w-2xl">
    <div class="grid w-full gap-1.5">
      <Label for="name">Name *</Label>
      <Input
        type="text"
        id="name"
        bind:value={name}
        placeholder="My Help Center"
        required
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="slug">Slug *</Label>
      <Input
        type="text"
        id="slug"
        bind:value={slug}
        placeholder="help"
        required
      />
      <p class="text-xs text-muted-foreground">
        URL friendly name for your portal.
      </p>
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="domain">Custom Domain</Label>
      <Input
        type="text"
        id="domain"
        bind:value={domain}
        placeholder="help.example.com"
      />
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <Button variant="outline" type="button" onclick={handleCancel}
        >Cancel</Button
      >
      <Button type="submit" disabled={isSubmitting}>
        {isSubmitting ? 'Creating...' : 'Create Portal'}
      </Button>
    </div>
  </form>
</SectionLayout>
