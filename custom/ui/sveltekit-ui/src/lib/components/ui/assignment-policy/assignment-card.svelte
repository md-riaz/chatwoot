<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Switch } from '$lib/components/ui/switch';

  interface Props {
    title: string;
    description?: string;
    isEnabled?: boolean;
    agentCount?: number;
    onToggle?: (enabled: boolean) => void;
    class?: string;
  }

  let { title, description, isEnabled = false, agentCount, onToggle, class: className }: Props = $props();
</script>

<div class={cn(
  'p-4 rounded-lg border bg-white dark:bg-slate-800',
  isEnabled ? 'border-woot-500' : 'border-slate-200 dark:border-slate-700',
  className
)}>
  <div class="flex items-start justify-between gap-3">
    <div class="flex-1">
      <div class="flex items-center gap-2 mb-1">
        <h4 class="font-medium text-slate-900 dark:text-slate-100">{title}</h4>
        {#if agentCount}
          <Badge variant="secondary">{agentCount} agents</Badge>
        {/if}
      </div>
      {#if description}
        <p class="text-sm text-slate-500 dark:text-slate-400">{description}</p>
      {/if}
    </div>
    <Switch checked={isEnabled} onCheckedChange={onToggle} />
  </div>
</div>
