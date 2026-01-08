<script lang="ts">
  import { Select as SelectPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  type Props = {
    children?: Snippet;
  };

  let { children, ...restProps }: Props = $props();
</script>

<SelectPrimitive.Group {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</SelectPrimitive.Group>
