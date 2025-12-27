<script lang="ts">
  import { cn } from '$lib/utils';
  import { Input } from '$lib/components/ui/input';

  interface CommandItem {
    id: string;
    label: string;
    description?: string;
    icon?: string;
    shortcut?: string;
    group?: string;
  }

  let {
    items = [],
    placeholder = 'Type a command or search...',
    open = $bindable(false),
    class: className = '',
    onSelect = (_item: CommandItem) => {},
    ...restProps
  }: {
    items?: CommandItem[];
    placeholder?: string;
    open?: boolean;
    class?: string;
    onSelect?: (item: CommandItem) => void;
  } = $props();

  let searchQuery = $state('');
  let selectedIndex = $state(0);

  const filteredItems = $derived(
    items.filter(
      (item) =>
        item.label.toLowerCase().includes(searchQuery.toLowerCase()) ||
        item.description?.toLowerCase().includes(searchQuery.toLowerCase())
    )
  );

  const groupedItems = $derived(() => {
    const groups: Record<string, CommandItem[]> = {};
    filteredItems.forEach((item) => {
      const group = item.group || 'Other';
      if (!groups[group]) groups[group] = [];
      groups[group].push(item);
    });
    return groups;
  });

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      selectedIndex = Math.min(selectedIndex + 1, filteredItems.length - 1);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      selectedIndex = Math.max(selectedIndex - 1, 0);
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (filteredItems[selectedIndex]) {
        handleSelect(filteredItems[selectedIndex]);
      }
    } else if (e.key === 'Escape') {
      open = false;
    }
  }

  function handleSelect(item: CommandItem) {
    onSelect(item);
    open = false;
    searchQuery = '';
  }

  $effect(() => {
    selectedIndex = 0;
  });
</script>

{#if open}
  <!-- Backdrop -->
  <div
    class="fixed inset-0 z-50 bg-black/50"
    onclick={() => (open = false)}
    role="button"
    tabindex="-1"
  ></div>

  <!-- Command Palette -->
  <div
    class={cn(
      'fixed top-1/4 left-1/2 -translate-x-1/2 z-50',
      'w-full max-w-lg bg-popover border rounded-lg shadow-lg',
      'animate-in fade-in-0 zoom-in-95',
      className
    )}
    role="dialog"
    {...restProps}
  >
    <div class="border-b p-3">
      <Input
        type="text"
        {placeholder}
        bind:value={searchQuery}
        onkeydown={handleKeydown}
        class="border-0 focus-visible:ring-0"
        autofocus
      />
    </div>

    <div class="max-h-80 overflow-auto p-2">
      {#if filteredItems.length === 0}
        <p class="py-6 text-center text-sm text-muted-foreground">
          No results found.
        </p>
      {:else}
        {#each Object.entries(groupedItems()) as [group, groupItems]}
          <div class="mb-2">
            <p class="px-2 py-1.5 text-xs font-medium text-muted-foreground">
              {group}
            </p>
            {#each groupItems as item, index}
              {@const globalIndex = filteredItems.indexOf(item)}
              <button
                type="button"
                class={cn(
                  'w-full flex items-center gap-3 px-2 py-2 rounded-md text-sm',
                  'hover:bg-accent cursor-pointer',
                  globalIndex === selectedIndex && 'bg-accent'
                )}
                onclick={() => handleSelect(item)}
              >
                {#if item.icon}
                  <span class="text-lg">{item.icon}</span>
                {/if}
                <div class="flex-1 text-left">
                  <p class="font-medium">{item.label}</p>
                  {#if item.description}
                    <p class="text-xs text-muted-foreground">{item.description}</p>
                  {/if}
                </div>
                {#if item.shortcut}
                  <kbd class="px-1.5 py-0.5 text-xs bg-muted rounded border">
                    {item.shortcut}
                  </kbd>
                {/if}
              </button>
            {/each}
          </div>
        {/each}
      {/if}
    </div>
  </div>
{/if}
