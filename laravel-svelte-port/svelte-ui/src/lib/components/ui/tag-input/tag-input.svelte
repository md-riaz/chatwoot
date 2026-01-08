<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Input } from '$lib/components/ui/input';

  interface Tag {
    id: string | number;
    label: string;
    color?: string;
  }

  let {
    tags = $bindable<Tag[]>([]),
    options = [],
    placeholder = 'Add tag...',
    disabled = false,
    class: className = '',
    ...restProps
  }: {
    tags?: Tag[];
    options?: Tag[];
    placeholder?: string;
    disabled?: boolean;
    class?: string;
  } = $props();

  let inputValue = $state('');
  let isOpen = $state(false);

  const filteredOptions = $derived(
    options.filter(
      (opt) =>
        opt.label.toLowerCase().includes(inputValue.toLowerCase()) &&
        !tags.some((tag) => tag.id === opt.id)
    )
  );

  function addTag(tag: Tag) {
    tags = [...tags, tag];
    inputValue = '';
    isOpen = false;
  }

  function removeTag(tagId: string | number) {
    tags = tags.filter((tag) => tag.id !== tagId);
  }

  function handleKeyDown(e: KeyboardEvent) {
    if (e.key === 'Backspace' && inputValue === '' && tags.length > 0) {
      removeTag(tags[tags.length - 1].id);
    }
  }
</script>

<div class={cn('relative w-full', className)} {...restProps}>
  <div
    class={cn(
      'flex flex-wrap items-center gap-1 min-h-10 p-2 border rounded-md bg-background',
      disabled && 'opacity-50 cursor-not-allowed'
    )}
  >
    {#each tags as tag}
      <Badge variant="secondary" class="flex items-center gap-1">
        {#if tag.color}
          <span
            class="w-2 h-2 rounded-full"
            style="background-color: {tag.color}"
          ></span>
        {/if}
        {tag.label}
        {#if !disabled}
          <button
            type="button"
            class="ml-1 hover:text-destructive"
            onclick={() => removeTag(tag.id)}
          >
            ×
          </button>
        {/if}
      </Badge>
    {/each}
    
    {#if !disabled}
      <Input
        type="text"
        bind:value={inputValue}
        {placeholder}
        class="flex-1 min-w-[100px] border-0 p-0 h-auto focus-visible:ring-0"
        onfocus={() => (isOpen = true)}
        onblur={() => setTimeout(() => (isOpen = false), 150)}
        onkeydown={handleKeyDown}
      />
    {/if}
  </div>

  {#if isOpen && filteredOptions.length > 0}
    <div class="absolute z-50 w-full mt-1 bg-popover border rounded-md shadow-lg max-h-40 overflow-auto">
      {#each filteredOptions as option}
        <button
          type="button"
          class="w-full px-3 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground flex items-center gap-2"
          onclick={() => addTag(option)}
        >
          {#if option.color}
            <span
              class="w-2 h-2 rounded-full"
              style="background-color: {option.color}"
            ></span>
          {/if}
          {option.label}
        </button>
      {/each}
    </div>
  {/if}
</div>
