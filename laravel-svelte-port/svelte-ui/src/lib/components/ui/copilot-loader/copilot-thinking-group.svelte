<script lang="ts">
  import { cn } from '$lib/utils';

  interface ThinkingStep {
    id: string;
    label: string;
    status: 'pending' | 'active' | 'complete';
  }

  interface Props {
    steps?: ThinkingStep[];
    class?: string;
  }

  let { 
    steps = [
      { id: '1', label: 'Analyzing context', status: 'complete' },
      { id: '2', label: 'Searching knowledge base', status: 'active' },
      { id: '3', label: 'Formulating response', status: 'pending' }
    ],
    class: className 
  }: Props = $props();
</script>

<div class={cn('space-y-2 p-3', className)}>
  {#each steps as step}
    <div class="flex items-center gap-2">
      {#if step.status === 'complete'}
        <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
      {:else if step.status === 'active'}
        <div class="h-4 w-4 border-2 border-woot-500 border-t-transparent rounded-full animate-spin" />
      {:else}
        <div class="h-4 w-4 border-2 border-slate-300 dark:border-slate-600 rounded-full" />
      {/if}
      <span class={cn(
        'text-sm',
        step.status === 'complete' && 'text-slate-500 dark:text-slate-400',
        step.status === 'active' && 'text-woot-600 dark:text-woot-400 font-medium',
        step.status === 'pending' && 'text-slate-400 dark:text-slate-500'
      )}>
        {step.label}
      </span>
    </div>
  {/each}
</div>
