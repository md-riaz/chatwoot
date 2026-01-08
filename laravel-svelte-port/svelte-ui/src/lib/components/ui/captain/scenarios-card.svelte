<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';

  interface Scenario {
    id: string;
    trigger: string;
    response: string;
  }

  interface Props {
    title: string;
    description: string;
    scenarios: Scenario[];
    onAddScenario?: () => void;
    class?: string;
  }

  let { title, description, scenarios = [], onAddScenario, class: className }: Props = $props();
</script>

<div class={cn(
  'p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
  className
)}>
  <div class="flex items-start justify-between gap-2 mb-3">
    <div>
      <h4 class="font-medium text-slate-900 dark:text-slate-100">{title}</h4>
      <p class="text-sm text-slate-500 dark:text-slate-400">{description}</p>
    </div>
    <Badge variant="secondary">{scenarios.length} scenarios</Badge>
  </div>
  
  <div class="space-y-2 mb-3">
    {#each scenarios as scenario}
      <div class="p-2 rounded bg-slate-50 dark:bg-slate-900 text-sm">
        <div class="text-slate-500 dark:text-slate-400">When: {scenario.trigger}</div>
        <div class="text-slate-700 dark:text-slate-300">Then: {scenario.response}</div>
      </div>
    {/each}
  </div>
  
  <Button variant="outline" size="sm" onclick={onAddScenario}>
    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
    Add Scenario
  </Button>
</div>
