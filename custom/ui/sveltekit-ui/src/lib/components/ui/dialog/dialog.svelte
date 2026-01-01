<script lang="ts">
  import { Dialog as DialogPrimitive } from 'bits-ui';
  import type { Snippet } from 'svelte';

  type Props = DialogPrimitive.RootProps & {
    children?: Snippet;
  };

  let { children, ...restProps }: Props = $props();
</script>

<DialogPrimitive.Root {...restProps}>
  {#if children}
    {@render children()}
  {/if}
</DialogPrimitive.Root>
