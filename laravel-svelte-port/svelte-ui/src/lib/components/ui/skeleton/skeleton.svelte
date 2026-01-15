<script lang="ts">
  import { cn } from '$lib/utils';

  type Props = {
    class?: string;
  };

  let { class: className, ...restProps }: Props = $props();
</script>

<div
  class={cn('animate-pulse rounded-md bg-muted', className)}
  {...restProps}
></div>
