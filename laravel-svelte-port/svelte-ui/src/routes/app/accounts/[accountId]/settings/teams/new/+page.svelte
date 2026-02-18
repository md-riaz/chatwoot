<script lang="ts">
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import SectionLayout from '../../components/SectionLayout.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { toast } from 'svelte-sonner';

  const accountId = $derived(Number($page.params.accountId));

  let name = $state('');
  let description = $state('');
  let allowAutoAssign = $state(true);
  let isSubmitting = $derived(teamsStore.uiFlags.isCreating);

  async function handleSubmit() {
    if (!name) {
      toast.error('Team name is required');
      return;
    }

    const data = {
      name,
      description,
      allow_auto_assign: allowAutoAssign,
    };

    const result = await teamsStore.createTeam(data);
    if (result) {
      toast.success('Team created successfully');
      goto(`/app/accounts/${accountId}/settings/teams`);
    }
  }

  function handleCancel() {
    goto(`/app/accounts/${accountId}/settings/teams`);
  }
</script>

<SectionLayout title="Create Team" description="Create a new team of agents">
  <form on:submit|preventDefault={handleSubmit} class="space-y-6 max-w-2xl">
    <div class="grid w-full gap-1.5">
      <Label for="name">Name *</Label>
      <Input
        type="text"
        id="name"
        bind:value={name}
        placeholder="Sales Team"
        required
      />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="description">Description</Label>
      <Textarea
        id="description"
        bind:value={description}
        placeholder="Handles sales inquiries..."
      />
    </div>

    <div class="flex items-center space-x-2">
      <Checkbox id="allow_auto_assign" bind:checked={allowAutoAssign} />
      <Label
        for="allow_auto_assign"
        class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
      >
        Allow auto assignment for this team
      </Label>
    </div>

    <div class="flex justify-end gap-2 pt-4">
      <Button variant="outline" type="button" on:click={handleCancel}
        >Cancel</Button
      >
      <Button type="submit" disabled={isSubmitting}>
        {isSubmitting ? 'Creating...' : 'Create Team'}
      </Button>
    </div>
  </form>
</SectionLayout>
