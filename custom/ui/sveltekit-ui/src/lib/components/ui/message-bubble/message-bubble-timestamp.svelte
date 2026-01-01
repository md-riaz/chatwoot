<script lang="ts">
  import { cn } from '$lib/utils';

  type Props = {
    class?: string;
    time?: string;
  };

  let { class: className, time = '', ...restProps }: Props = $props();
</script>

<span
  class={cn('text-xs text-muted-foreground mt-1 block', className)}
  {...restProps}
>
  {time}
</span>
