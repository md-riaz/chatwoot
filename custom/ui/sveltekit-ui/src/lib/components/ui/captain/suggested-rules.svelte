<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';

  interface Rule {
    id: string;
    name: string;
    description: string;
    confidence: number;
  }

  interface Props {
    rules: Rule[];
    onAccept?: (ruleId: string) => void;
    onDismiss?: (ruleId: string) => void;
    class?: string;
  }

  let { rules = [], onAccept, onDismiss, class: className }: Props = $props();
</script>

<div class={cn('space-y-3', className)}>
  <div class="flex items-center gap-2 text-sm font-medium text-slate-900 dark:text-slate-100">
    <svg class="h-5 w-5 text-woot-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
    </svg>
    Suggested Rules
  </div>
  
  {#each rules as rule}
    <div class="p-3 rounded-lg border border-woot-200 dark:border-woot-800 bg-woot-50 dark:bg-woot-900/50">
      <div class="flex items-start justify-between gap-2 mb-2">
        <h5 class="font-medium text-slate-900 dark:text-slate-100">{rule.name}</h5>
        <Badge variant="secondary">{rule.confidence}% match</Badge>
      </div>
      <p class="text-sm text-slate-600 dark:text-slate-400 mb-3">{rule.description}</p>
      <div class="flex items-center gap-2">
        <Button size="sm" onclick={() => onAccept?.(rule.id)}>Accept</Button>
        <Button variant="ghost" size="sm" onclick={() => onDismiss?.(rule.id)}>Dismiss</Button>
      </div>
    </div>
  {/each}
  
  {#if rules.length === 0}
    <div class="text-center text-sm text-slate-500 dark:text-slate-400 py-4">
      No suggested rules at this time
    </div>
  {/if}
</div>
