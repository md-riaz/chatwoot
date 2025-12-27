<script lang="ts">
  import { cn } from '$lib/utils';

  type Props = {
    class?: string;
    src?: string;
    alt?: string;
  };

  let {
    class: className,
    src,
    alt = '',
    ...restProps
  }: Props = $props();

  let hasError = $state(false);

  function handleError() {
    hasError = true;
  }
</script>

{#if src && !hasError}
  <img
    {src}
    {alt}
    class={cn('aspect-square h-full w-full object-cover', className)}
    on:error={handleError}
    {...restProps}
  />
{/if}
