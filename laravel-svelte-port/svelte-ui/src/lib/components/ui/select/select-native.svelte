<script lang="ts">
  import { cn } from '$lib/utils';
  
  interface Props {
    id?: string;
    name?: string;
    value?: string;
    disabled?: boolean;
    required?: boolean;
    class?: string;
    onchange?: (e: Event & { currentTarget: HTMLSelectElement }) => void;
    children?: any;
  }
  
  let {
    id,
    name,
    value = $bindable(),
    disabled = false,
    required = false,
    class: className,
    onchange,
    children
  }: Props = $props();
</script>

<select
  {id}
  {name}
  bind:value
  {disabled}
  {required}
  {onchange}
  class={cn(
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background',
    'focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
    'disabled:cursor-not-allowed disabled:opacity-50',
    className
  )}
>
  {@render children?.()}
</select>
