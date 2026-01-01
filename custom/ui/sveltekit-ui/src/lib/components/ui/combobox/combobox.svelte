<script lang="ts">
  import { cn } from '$lib/utils';
  import { Input } from '$lib/components/ui/input';

  interface Option {
    value: string | number;
    label: string;
  }

  let {
    options = [],
    value = $bindable(''),
    placeholder = 'Search...',
    disabled = false,
    class: className = '',
    ...restProps
  }: {
    options: Option[];
    value?: string | number;
    placeholder?: string;
    disabled?: boolean;
    class?: string;
  } = $props();

  let isOpen = $state(false);
  let searchQuery = $state('');
  let inputRef: HTMLInputElement | null = $state(null);

  const filteredOptions = $derived(
    options.filter((opt) =>
      opt.label.toLowerCase().includes(searchQuery.toLowerCase())
    )
  );

  const selectedLabel = $derived(
    options.find((opt) => opt.value === value)?.label || ''
  );

  function handleSelect(option: Option) {
    value = option.value;
    searchQuery = option.label;
    isOpen = false;
  }

  function handleInputFocus() {
    isOpen = true;
  }

  function handleInputBlur() {
    // Delay to allow click on options
    setTimeout(() => {
      isOpen = false;
    }, 150);
  }
</script>

<div class={cn('relative w-full', className)} {...restProps}>
  <Input
    bind:ref={inputRef}
    type="text"
    value={selectedLabel || searchQuery}
    {placeholder}
    {disabled}
    onfocus={handleInputFocus}
    onblur={handleInputBlur}
    oninput={(e) => {
      searchQuery = e.currentTarget.value;
      isOpen = true;
    }}
  />
  
  {#if isOpen && filteredOptions.length > 0}
    <div class="absolute z-50 w-full mt-1 bg-popover border rounded-md shadow-lg max-h-60 overflow-auto">
      {#each filteredOptions as option}
        <button
          type="button"
          class={cn(
            'w-full px-3 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground',
            option.value === value && 'bg-accent text-accent-foreground'
          )}
          onclick={() => handleSelect(option)}
        >
          {option.label}
        </button>
      {/each}
    </div>
  {/if}
</div>
