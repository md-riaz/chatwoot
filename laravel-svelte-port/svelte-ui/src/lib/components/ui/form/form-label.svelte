<script lang="ts" context="module">
  export type FormLabelProps = {
    for?: string;
    required?: boolean;
    class?: string;
  };
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';
  
  let {
    for: htmlFor,
    required = false,
    class: className,
    children,
    ...restProps
  }: FormLabelProps & { children?: Snippet } = $props();
</script>

<label
  for={htmlFor}
  class={cn(
    'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
  {#if required}
    <span class="text-destructive ml-1">*</span>
  {/if}
</label>
