<script lang="ts">
  /**
   * FilterSelect Component (Vue Parity)
   * Source: c:\projects\chatwoot\app\javascript\dashboard\components-next\filter\inputs\FilterSelect.vue
   *
   * A dropdown select for filter attributes and operators
   */
  import { ChevronDown } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';

  // Props matching Vue defineProps
  interface SelectOption {
    value: string | number;
    label: string;
    icon?: string;
  }

  interface Props {
    options: SelectOption[];
    hideLabel?: boolean;
    hideIcon?: boolean;
    variant?:
      | 'default'
      | 'ghost'
      | 'outline'
      | 'secondary'
      | 'destructive'
      | 'link';
    label?: string | null;
    value?: string | number;
    class?: string;
  }

  let {
    options,
    hideLabel = false,
    hideIcon = false,
    variant = 'secondary',
    label = null,
    value = $bindable(),
    class: className = '',
  }: Props = $props();

  // State
  let open = $state(false);

  // Derived: selected option
  const selectedOption = $derived(
    options.find(o => o.value === value) ||
      options[0] || { value: '', label: '', icon: undefined }
  );

  // Derived: icon to render
  const iconToRender = $derived(() => {
    if (hideIcon) return null;
    return selectedOption?.icon || null;
  });

  // Update selected value
  function updateSelected(newValue: string | number) {
    value = newValue;
    open = false;
  }
</script>

<DropdownMenu.Root bind:open>
  <DropdownMenu.Trigger asChild let:builder>
    <Button
      builders={[builder]}
      {variant}
      size="sm"
      class="h-8 gap-1 text-sm font-normal {className}"
    >
      {#if iconToRender()}
        <span class={iconToRender()}></span>
      {/if}
      {#if !hideLabel}
        <span class="truncate max-w-[120px]">
          {label || selectedOption?.label || ''}
        </span>
      {/if}
      {#if !hideIcon && !selectedOption?.icon}
        <ChevronDown class="h-4 w-4 shrink-0 opacity-50" />
      {/if}
    </Button>
  </DropdownMenu.Trigger>
  <DropdownMenu.Content
    class="min-w-48 max-h-80 overflow-y-auto z-50"
    align="start"
  >
    {#each options as option (option.value)}
      <DropdownMenu.Item
        class="cursor-pointer gap-2"
        on:click={() => updateSelected(option.value)}
      >
        {#if option.icon}
          <span class={option.icon}></span>
        {/if}
        <span>{option.label}</span>
        {#if option.value === value}
          <span class="ml-auto i-lucide-check h-4 w-4"></span>
        {/if}
      </DropdownMenu.Item>
    {/each}
  </DropdownMenu.Content>
</DropdownMenu.Root>
