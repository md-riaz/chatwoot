<script lang="ts">
  import { page } from '$app/stores';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import SectionLayout from '../../settings/components/SectionLayout.svelte';

  const accountId = $derived(Number($page.params.accountId));

  // Placeholder state for portal settings
  let name = $state('Help Center');
  let slug = $state('help');
  let domain = $state('');

  function handleSave() {
    // Save logic
    alert('Settings saved (placeholder)');
  }
</script>

<SectionLayout
  title="General Settings"
  description="Configure your help center basics"
>
  <div class="space-y-6 max-w-2xl">
    <div class="grid w-full gap-1.5">
      <Label for="name">Portal Name</Label>
      <Input type="text" id="name" bind:value={name} />
    </div>

    <div class="grid w-full gap-1.5">
      <Label for="slug">Slug</Label>
      <Input type="text" id="slug" bind:value={slug} />
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

    <div class="pt-4">
      <Button on:click={handleSave}>Update Settings</Button>
    </div>
  </div>
</SectionLayout>
