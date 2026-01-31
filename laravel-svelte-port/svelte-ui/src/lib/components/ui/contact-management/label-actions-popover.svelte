<script lang="ts">
  /**
   * LabelActions Component (Vue Parity)
   * Source: c:\projects\chatwoot\app\javascript\dashboard\components\widgets\conversation\conversationBulkActions\LabelActions.vue
   *
   * Popover for filtering and selecting labels for bulk assignment
   */
  import { Search, X } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Checkbox } from '$lib/components/ui/checkbox';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import type { Label } from '$lib/api/labels';

  interface Props {
    onClose: () => void;
    onAssign: (labels: string[]) => void;
  }

  let { onClose, onAssign }: Props = $props();

  // State
  let query = $state('');
  let selectedLabels = $state<string[]>([]);
  let isAssigning = $state(false);

  // Derived: filtered labels
  const filteredLabels = $derived.by(() => {
    const allLabels = labelsStore.allLabels || [];
    if (!query.trim()) return allLabels;
    
    const term = query.toLowerCase();
    return allLabels.filter(label =>
      label.title?.toLowerCase().includes(term)
    );
  });

  const hasLabels = $derived((labelsStore.allLabels || []).length > 0);
  const hasFilteredLabels = $derived(filteredLabels.length > 0);

  // Computed: is label selected
  function isLabelSelected(title: string): boolean {
    return selectedLabels.includes(title);
  }

  // Handle assign
  function handleAssign() {
    if (selectedLabels.length > 0) {
      onAssign(selectedLabels);
    }
  }

  // Handle checkbox toggle
  function toggleLabel(title: string, checked: boolean) {
    if (checked) {
      if (!selectedLabels.includes(title)) {
        selectedLabels = [...selectedLabels, title];
      }
    } else {
      selectedLabels = selectedLabels.filter(l => l !== title);
    }
  }
</script>

<div
  class="min-w-[240px] bg-background border rounded-md shadow-md flex flex-col max-h-[300px]"
  role="dialog"
  aria-modal="true"
  aria-labelledby="label-dialog-title"
>
  <!-- Header -->
  <div class="flex items-center justify-between p-2 border-b">
    <span id="label-dialog-title" class="text-sm font-medium">Assign Labels</span>
    <Button variant="ghost" size="icon" class="h-6 w-6" onclick={onClose}>
      <X class="h-4 w-4" />
    </Button>
  </div>

  <!-- Search -->
  <div class="p-2 border-b">
    <div class="relative">
      <Search class="absolute left-2 top-1/2 -translate-y-1/2 h-3 w-3 text-muted-foreground" />
      <Input
        bind:value={query}
        type="search"
        placeholder="Search labels..."
        class="h-8 pl-7 text-xs"
      />
    </div>
  </div>

  <!-- Label List -->
  <div class="flex-1 overflow-y-auto p-1">
    {#if hasLabels}
      {#if !hasFilteredLabels}
        <div class="p-4 text-center text-xs text-muted-foreground">
          No labels found matching "{query}"
        </div>
      {:else}
        {#each filteredLabels as label (label.id)}
          <label
            class="flex items-center gap-2 p-2 hover:bg-muted/50 rounded-sm cursor-pointer"
          >
            <Checkbox
              checked={isLabelSelected(label.title)}
              onCheckedChange={(checked) => toggleLabel(label.title, checked as boolean)}
            />
            <span class="flex-1 text-sm truncate">{label.title}</span>
            <span
              class="h-2 w-2 rounded-full ring-1 ring-border"
              style="background-color: {label.color}"
            ></span>
          </label>
        {/each}
      {/if}
    {:else}
      <div class="p-4 text-center text-xs text-muted-foreground">
        No labels available
      </div>
    {/if}
  </div>

  <!-- Footer -->
  <div class="p-2 border-t">
    <Button
      class="w-full h-8 text-xs"
      disabled={selectedLabels.length === 0}
      onclick={handleAssign}
    >
      Assign selected labels
    </Button>
  </div>
</div>
