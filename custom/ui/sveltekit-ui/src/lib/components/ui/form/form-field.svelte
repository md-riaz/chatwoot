<script lang="ts" context="module">
  export type FormFieldProps = {
    name: string;
    class?: string;
  };
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';
  
  let {
    name,
    class: className,
    children,
    ...restProps
  }: FormFieldProps & { children?: Snippet } = $props();
</script>

<div class={cn('space-y-2', className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</div>
