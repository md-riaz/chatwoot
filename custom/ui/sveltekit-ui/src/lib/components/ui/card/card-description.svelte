<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<p class={cn('text-sm text-muted-foreground', className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</p>
