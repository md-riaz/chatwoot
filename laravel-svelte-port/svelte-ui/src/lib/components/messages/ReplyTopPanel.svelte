<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { cn } from '$lib/utils';
  import { MessageSquare, Lock } from 'lucide-svelte';

  let { mode = 'reply', onModeChange } = $props<{
    mode: 'reply' | 'private';
    onModeChange: (mode: 'reply' | 'private') => void;
  }>();
</script>

<div
  class="flex items-center gap-0.5 p-0.5 bg-slate-100 dark:bg-slate-800 rounded-lg w-fit"
>
  <button
    class={cn(
      'flex items-center gap-1.5 px-2.5 py-1 text-[12px] font-semibold rounded-md transition-all duration-150',
      mode === 'reply'
        ? 'bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 shadow-sm'
        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
    )}
    onclick={() => onModeChange('reply')}
  >
    <MessageSquare class="h-3.5 w-3.5" />
    Reply
  </button>
  <button
    class={cn(
      'flex items-center gap-1.5 px-2.5 py-1 text-[12px] font-semibold rounded-md transition-all duration-150',
      mode === 'private'
        ? 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 shadow-sm'
        : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
    )}
    onclick={() => onModeChange('private')}
  >
    <Lock class="h-3.5 w-3.5" />
    Private Note
  </button>
</div>
