<script lang="ts">
  import { cn } from '$lib/utils';

  type Props = {
    class?: string;
    status?: 'sent' | 'delivered' | 'read' | 'failed';
  };

  let { class: className, status = 'sent', ...restProps }: Props = $props();
</script>

<span class={cn('inline-flex items-center gap-1 text-xs', className)} {...restProps}>
  {#if status === 'sent'}
    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><polyline points="20 6 9 17 4 12"/></svg>
  {:else if status === 'delivered'}
    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-foreground"><polyline points="18 6 7 17 2 12"/><polyline points="22 6 11 17 8 14"/></svg>
  {:else if status === 'read'}
    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary"><polyline points="18 6 7 17 2 12"/><polyline points="22 6 11 17 8 14"/></svg>
  {:else if status === 'failed'}
    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-destructive"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
  {/if}
</span>
