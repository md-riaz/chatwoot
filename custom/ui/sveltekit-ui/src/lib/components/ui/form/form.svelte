<script lang="ts" context="module">
  export type FormProps = {
    class?: string;
  };
</script>

<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';
  
  let {
    class: className,
    children,
    ...restProps
  }: FormProps & { children?: Snippet } = $props();
</script>

<form class={cn('space-y-6', className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</form>
