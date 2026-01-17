<script lang="ts">
  import { cn } from '$lib/utils';
  import { Label } from '$lib/components/ui/label';

  let {
    id = '',
    label = '',
    value = $bindable(''),
    placeholder = '',
    type = 'text',
    disabled = false,
    customInputClass = '',
    customLabelClass = '',
    class: className = '',
    ...restProps
  }: {
    id?: string;
    label?: string;
    value?: string;
    placeholder?: string;
    type?: string;
    disabled?: boolean;
    customInputClass?: string;
    customLabelClass?: string;
    class?: string;
  } = $props();
</script>

<div class={cn('flex flex-col gap-1.5', className)} {...restProps}>
  {#if label}
    <Label for={id} class={cn('text-sm font-medium', customLabelClass)}>
      {label}
    </Label>
  {/if}
  
  <input
    {id}
    {type}
    bind:value
    {placeholder}
    {disabled}
    class={cn(
      'w-full bg-transparent text-sm',
      'border-0 border-b border-input focus:border-primary',
      'outline-hidden transition-colors',
      'py-1.5 px-0',
      'placeholder:text-muted-foreground',
      'disabled:cursor-not-allowed disabled:opacity-50',
      customInputClass
    )}
  />
</div>
