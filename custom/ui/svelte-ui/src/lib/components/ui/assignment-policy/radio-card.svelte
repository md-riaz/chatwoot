<script lang="ts">
  import { cn } from '$lib/utils';

  interface Props {
    value: string;
    title: string;
    description?: string;
    selected?: boolean;
    name?: string;
    onSelect?: (value: string) => void;
    class?: string;
  }

  let { value, title, description, selected = false, name = 'radio-card', onSelect, class: className }: Props = $props();
</script>

<button
  type="button"
  class={cn(
    'w-full p-4 rounded-lg border text-left transition-colors',
    selected
      ? 'border-woot-500 bg-woot-50 dark:bg-woot-900/20'
      : 'border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800',
    className
  )}
  onclick={() => onSelect?.(value)}
>
  <div class="flex items-start gap-3">
    <div class={cn(
      'mt-0.5 h-4 w-4 rounded-full border-2 flex items-center justify-center',
      selected ? 'border-woot-500' : 'border-slate-300 dark:border-slate-600'
    )}>
      {#if selected}
        <div class="h-2 w-2 rounded-full bg-woot-500" />
      {/if}
    </div>
    <div>
      <div class="font-medium text-slate-900 dark:text-slate-100">{title}</div>
      {#if description}
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{description}</p>
      {/if}
    </div>
  </div>
</button>
