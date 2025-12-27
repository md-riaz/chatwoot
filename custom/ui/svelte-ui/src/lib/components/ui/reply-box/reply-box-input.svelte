<script lang="ts">
  import { cn } from '$lib/utils';

  type Props = {
    class?: string;
    value?: string;
    placeholder?: string;
    rows?: number;
    disabled?: boolean;
  };

  let {
    class: className,
    value = $bindable(''),
    placeholder = 'Type your message...',
    rows = 3,
    disabled = false,
    ...restProps
  }: Props = $props();
</script>

<textarea
  bind:value
  {placeholder}
  {rows}
  {disabled}
  class={cn(
    'w-full resize-none border-0 bg-transparent text-sm focus:outline-none focus:ring-0 placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50',
    className
  )}
  {...restProps}
/>
