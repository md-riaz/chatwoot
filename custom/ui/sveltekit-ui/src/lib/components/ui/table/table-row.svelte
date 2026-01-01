<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<tr
  class={cn(
    'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted',
    className
  )}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</tr>
