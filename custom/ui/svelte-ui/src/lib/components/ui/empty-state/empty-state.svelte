<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface Props {
    title: string;
    description?: string;
    icon?: string;
    actionLabel?: string;
    onAction?: () => void;
    class?: string;
  }

  let { title, description, icon = '📭', actionLabel, onAction, class: className }: Props = $props();
</script>

<div class={cn('flex flex-col items-center justify-center py-12 px-4 text-center', className)}>
  <div class="text-6xl mb-4">{icon}</div>
  <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-2">{title}</h3>
  {#if description}
    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mb-4">{description}</p>
  {/if}
  {#if actionLabel}
    <Button onclick={onAction}>{actionLabel}</Button>
  {/if}
</div>
