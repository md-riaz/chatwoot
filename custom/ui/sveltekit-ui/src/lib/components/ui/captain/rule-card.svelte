<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Switch } from '$lib/components/ui/switch';

  interface Props {
    name: string;
    description: string;
    conditions: string[];
    actions: string[];
    isEnabled?: boolean;
    onToggle?: (enabled: boolean) => void;
    class?: string;
  }

  let { name, description, conditions = [], actions = [], isEnabled = true, onToggle, class: className }: Props = $props();
</script>

<div class={cn(
  'p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
  className
)}>
  <div class="flex items-start justify-between gap-3 mb-3">
    <div>
      <h4 class="font-medium text-slate-900 dark:text-slate-100">{name}</h4>
      <p class="text-sm text-slate-500 dark:text-slate-400">{description}</p>
    </div>
    <Switch checked={isEnabled} onCheckedChange={onToggle} />
  </div>
  
  <div class="space-y-2">
    <div>
      <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Conditions</div>
      <div class="flex flex-wrap gap-1">
        {#each conditions as condition}
          <Badge variant="secondary" class="text-xs">{condition}</Badge>
        {/each}
      </div>
    </div>
    <div>
      <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Actions</div>
      <div class="flex flex-wrap gap-1">
        {#each actions as action}
          <Badge variant="outline" class="text-xs">{action}</Badge>
        {/each}
      </div>
    </div>
  </div>
</div>
