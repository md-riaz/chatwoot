<script lang="ts">
  import * as Dialog from '$lib/components/ui/dialog';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { segmentsStore } from '$lib/stores/segments.svelte';
  import { Loader2 } from 'lucide-svelte';
  import * as Alert from '$lib/components/ui/alert';

  interface Props {
    open: boolean;
    query: Record<string, any>;
  }

  let { open = $bindable(false), query }: Props = $props();

  let name = $state('');
  let isCreating = $state(false);
  let error = $state<string | null>(null);

  async function handleCreate() {
    if (!name.trim()) return;

    try {
      isCreating = true;
      error = null;
      const result = await segmentsStore.createSegment(name, query);

      if (result) {
        open = false;
        name = ''; // Reset name
      } else {
        error = segmentsStore.error || 'Failed to create segment';
      }
    } catch (e) {
      error = 'An unexpected error occurred';
    } finally {
      isCreating = false;
    }
  }

  $effect(() => {
    if (!open) {
      error = null;
    }
  });
</script>

<Dialog.Root bind:open>
  <Dialog.Content class="sm:max-w-[425px]">
    <Dialog.Header>
      <Dialog.Title>Save as Segment</Dialog.Title>
      <Dialog.Description>
        Save your current filters as a segment to access them quickly later.
      </Dialog.Description>
    </Dialog.Header>

    <div class="space-y-4 py-4">
      {#if error}
        <Alert.Root variant="destructive">
          <Alert.Title>Error</Alert.Title>
          <Alert.Description>{error}</Alert.Description>
        </Alert.Root>
      {/if}

      <div class="space-y-2">
        <label for="segment-name" class="text-sm font-medium"
          >Segment Name</label
        >
        <Input
          id="segment-name"
          bind:value={name}
          placeholder="e.g. VIP Customers"
          disabled={isCreating}
        />
      </div>
    </div>

    <Dialog.Footer>
      <Button
        variant="outline"
        onclick={() => (open = false)}
        disabled={isCreating}>Cancel</Button
      >
      <Button onclick={handleCreate} disabled={!name.trim() || isCreating}>
        {#if isCreating}
          <Loader2 class="mr-2 h-4 w-4 animate-spin" />
          Saving...
        {:else}
          Save Segment
        {/if}
      </Button>
    </Dialog.Footer>
  </Dialog.Content>
</Dialog.Root>
