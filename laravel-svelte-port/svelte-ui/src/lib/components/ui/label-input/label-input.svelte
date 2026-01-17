<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';

  interface Label {
    id: string;
    title: string;
    color: string;
    description?: string;
  }

  let {
    labels = [],
    selectedLabels = $bindable<string[]>([]),
    class: className = '',
    onAdd = (_label: Label) => {},
    onRemove = (_labelId: string) => {},
    ...restProps
  }: {
    labels?: Label[];
    selectedLabels?: string[];
    class?: string;
    onAdd?: (label: Label) => void;
    onRemove?: (labelId: string) => void;
  } = $props();

  let isOpen = $state(false);
  let searchQuery = $state('');

  const filteredLabels = $derived(
    labels.filter(
      (label) =>
        label.title.toLowerCase().includes(searchQuery.toLowerCase()) &&
        !selectedLabels.includes(label.id)
    )
  );

  const displayedLabels = $derived(
    labels.filter((label) => selectedLabels.includes(label.id))
  );

  function handleAddLabel(label: Label) {
    selectedLabels = [...selectedLabels, label.id];
    onAdd(label);
  }

  function handleRemoveLabel(labelId: string) {
    selectedLabels = selectedLabels.filter((id) => id !== labelId);
    onRemove(labelId);
  }
</script>

<div class={cn('space-y-2', className)} {...restProps}>
  <!-- Selected Labels -->
  <div class="flex flex-wrap gap-2">
    {#each displayedLabels as label}
      <Badge
        variant="secondary"
        class="flex items-center gap-1.5"
        style="background-color: {label.color}20; border-color: {label.color}; color: {label.color}"
      >
        <span
          class="w-2 h-2 rounded-full"
          style="background-color: {label.color}"
        ></span>
        {label.title}
        <button
          type="button"
          class="ml-1 hover:opacity-70"
          onclick={() => handleRemoveLabel(label.id)}
        >
          ×
        </button>
      </Badge>
    {/each}
  </div>

  <!-- Add Label Dropdown -->
  <div class="relative">
    <Button
      variant="outline"
      size="sm"
      onclick={() => (isOpen = !isOpen)}
    >
      + Add Label
    </Button>

    {#if isOpen}
      <div class="absolute z-50 top-full left-0 mt-1 w-64 bg-popover border rounded-md shadow-lg">
        <input
          type="text"
          class="w-full px-3 py-2 text-sm border-b bg-transparent outline-hidden"
          placeholder="Search labels..."
          bind:value={searchQuery}
        />
        <div class="max-h-48 overflow-auto p-1">
          {#if filteredLabels.length > 0}
            {#each filteredLabels as label}
              <button
                type="button"
                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left hover:bg-accent rounded"
                onclick={() => {
                  handleAddLabel(label);
                  isOpen = false;
                }}
              >
                <span
                  class="w-3 h-3 rounded-full"
                  style="background-color: {label.color}"
                ></span>
                <span>{label.title}</span>
                {#if label.description}
                  <span class="text-xs text-muted-foreground ml-auto truncate max-w-[100px]">
                    {label.description}
                  </span>
                {/if}
              </button>
            {/each}
          {:else}
            <p class="px-3 py-2 text-sm text-muted-foreground">
              No labels found
            </p>
          {/if}
        </div>
      </div>
    {/if}
  </div>
</div>
