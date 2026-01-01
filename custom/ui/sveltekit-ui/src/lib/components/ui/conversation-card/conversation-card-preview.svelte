<script lang="ts">
  import { cn } from '$lib/utils';

  type Props = {
    class?: string;
    message?: string;
    unread?: boolean;
  };

  let { class: className, message = '', unread = false, ...restProps }: Props = $props();
</script>

<p
  class={cn(
    'text-sm truncate',
    unread ? 'text-foreground font-medium' : 'text-muted-foreground',
    className
  )}
  {...restProps}
>
  {message}
</p>
