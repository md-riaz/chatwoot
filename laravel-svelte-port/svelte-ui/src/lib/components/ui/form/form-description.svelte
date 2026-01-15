<script lang="ts" module>
  export type FormDescriptionProps = {
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
  }: FormDescriptionProps & { children?: Snippet } = $props();
</script>

<p class={cn('text-sm text-muted-foreground', className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</p>
