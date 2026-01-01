<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';

  interface Props {
    name: string;
    type: 'round_robin' | 'load_balanced' | 'manual';
    inboxes?: string[];
    isActive?: boolean;
    onEdit?: () => void;
    onDelete?: () => void;
    class?: string;
  }

  let { name, type, inboxes = [], isActive = true, onEdit, onDelete, class: className }: Props = $props();

  const typeLabels = {
    round_robin: 'Round Robin',
    load_balanced: 'Load Balanced',
    manual: 'Manual'
  };
</script>

<div class={cn(
  'p-4 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
  className
)}>
  <div class="flex items-start justify-between gap-3 mb-3">
    <div>
      <div class="flex items-center gap-2 mb-1">
        <h4 class="font-medium text-slate-900 dark:text-slate-100">{name}</h4>
        <Badge variant={isActive ? 'success' : 'secondary'}>{isActive ? 'Active' : 'Inactive'}</Badge>
      </div>
      <Badge variant="outline">{typeLabels[type]}</Badge>
    </div>
    <div class="flex items-center gap-1">
      <Button variant="ghost" size="sm" onclick={onEdit}>Edit</Button>
      <Button variant="ghost" size="sm" class="text-destructive" onclick={onDelete}>Delete</Button>
    </div>
  </div>
  
  {#if inboxes.length > 0}
    <div class="text-sm text-slate-500 dark:text-slate-400">
      Applied to: {inboxes.join(', ')}
    </div>
  {/if}
</div>
