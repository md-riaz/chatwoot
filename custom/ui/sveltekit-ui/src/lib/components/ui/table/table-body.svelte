<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<tbody class={cn('[&_tr:last-child]:border-0', className)} {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</tbody>
