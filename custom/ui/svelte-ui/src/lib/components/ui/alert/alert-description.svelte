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
  class={cn('text-sm [&_p]:leading-relaxed', className)}
  {...restProps}
>
  {#if children}
    {@render children()}
  {/if}
</div>
