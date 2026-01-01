<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface Option {
    value: string;
    label: string;
    icon?: string;
    description?: string;
    disabled?: boolean;
  }

  interface Props {
    options: Option[];
    value?: string;
    placeholder?: string;
    searchable?: boolean;
    onSelect?: (value: string) => void;
    class?: string;
  }

  let { options = [], value, placeholder = 'Select...', searchable = false, onSelect, class: className }: Props = $props();
  
  let isOpen = $state(false);
  let searchTerm = $state('');
  
  const selectedOption = $derived(options.find(o => o.value === value));
  const filteredOptions = $derived(
    searchable && searchTerm
      ? options.filter(o => o.label.toLowerCase().includes(searchTerm.toLowerCase()))
      : options
  );
</script>

<div class={cn('relative', className)}>
  <Button 
    variant="outline" 
    class="w-full justify-between"
    onclick={() => isOpen = !isOpen}
  >
    <span class={selectedOption ? 'text-slate-900 dark:text-slate-100' : 'text-slate-500'}>
      {#if selectedOption}
        {#if selectedOption.icon}
          <span class="mr-2">{selectedOption.icon}</span>
        {/if}
        {selectedOption.label}
      {:else}
        {placeholder}
      {/if}
    </span>
    <svg class={cn('h-4 w-4 transition-transform', isOpen && 'rotate-180')} fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </Button>
  
  {#if isOpen}
    <div class="absolute top-full left-0 right-0 mt-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg z-10 overflow-hidden">
      {#if searchable}
        <div class="p-2 border-b border-slate-200 dark:border-slate-700">
          <input
            type="text"
            bind:value={searchTerm}
            placeholder="Search..."
            class="w-full px-3 py-2 text-sm border border-slate-200 dark:border-slate-600 rounded-md bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100"
          />
        </div>
      {/if}
      <div class="max-h-60 overflow-auto py-1">
        {#each filteredOptions as option}
          <button
            class={cn(
              'w-full flex items-start gap-2 px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-700',
              option.value === value && 'bg-woot-50 dark:bg-woot-900/20',
              option.disabled && 'opacity-50 cursor-not-allowed'
            )}
            disabled={option.disabled}
            onclick={() => {
              if (!option.disabled) {
                onSelect?.(option.value);
                isOpen = false;
                searchTerm = '';
              }
            }}
          >
            {#if option.icon}
              <span class="text-lg">{option.icon}</span>
            {/if}
            <div>
              <div class="font-medium text-slate-900 dark:text-slate-100">{option.label}</div>
              {#if option.description}
                <div class="text-xs text-slate-500">{option.description}</div>
              {/if}
            </div>
            {#if option.value === value}
              <svg class="ml-auto h-4 w-4 text-woot-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            {/if}
          </button>
        {/each}
        {#if filteredOptions.length === 0}
          <div class="px-3 py-4 text-center text-sm text-slate-500">No options found</div>
        {/if}
      </div>
    </div>
  {/if}
</div>
