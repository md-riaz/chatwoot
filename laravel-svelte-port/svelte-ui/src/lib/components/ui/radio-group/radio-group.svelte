<script lang="ts">
  import { cn } from '$lib/utils';

  interface RadioOption {
    value: string;
    label: string;
    description?: string;
    disabled?: boolean;
  }

  let {
    options = [],
    value = $bindable(''),
    name = 'radio-group',
    orientation = 'vertical',
    class: className = '',
    ...restProps
  }: {
    options?: RadioOption[];
    value?: string;
    name?: string;
    orientation?: 'horizontal' | 'vertical';
    class?: string;
  } = $props();
</script>

<div
  class={cn(
    'flex gap-3',
    orientation === 'vertical' ? 'flex-col' : 'flex-row flex-wrap',
    className
  )}
  role="radiogroup"
  {...restProps}
>
  {#each options as option}
    <label
      class={cn(
        'flex items-start gap-3 cursor-pointer',
        option.disabled && 'opacity-50 cursor-not-allowed'
      )}
    >
      <input
        type="radio"
        {name}
        value={option.value}
        checked={value === option.value}
        disabled={option.disabled}
        onchange={() => (value = option.value)}
        class={cn(
          'mt-1 w-4 h-4 border rounded-full',
          'checked:bg-primary checked:border-primary',
          'focus:ring-2 focus:ring-primary focus:ring-offset-2'
        )}
      />
      <div>
        <span class="text-sm font-medium">{option.label}</span>
        {#if option.description}
          <p class="text-xs text-muted-foreground mt-0.5">{option.description}</p>
        {/if}
      </div>
    </label>
  {/each}
</div>
