<script lang="ts">
  import { cn } from '$lib/utils';
  import type { Snippet } from 'svelte';

  type Props = {
    class?: string;
    children?: Snippet;
  };

  let { class: className, children, ...restProps }: Props = $props();
</script>

<div
  class={cn('py-1.5 pl-8 pr-2 text-sm font-semibold', className)}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
