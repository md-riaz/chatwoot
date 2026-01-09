<script lang="ts">
  import { cn } from '$lib/utils';
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  import { Badge } from '$lib/components/ui/badge';
  import { Progress } from '$lib/components/ui/progress';

  interface Props {
    agent: {
      name: string;
      avatar?: string;
      email?: string;
    };
    capacity: number;
    current: number;
    status?: 'online' | 'busy' | 'offline';
    class?: string;
  }

  let { agent, capacity, current, status = 'online', class: className }: Props = $props();
  
  const percentage = $derived((current / capacity) * 100);
  const statusColors = {
    online: 'success',
    busy: 'warning',
    offline: 'secondary'
  } as const;
</script>

<div class={cn(
  'p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
  className
)}>
  <div class="flex items-center gap-3 mb-2">
    <Avatar size="sm">
      <AvatarImage src={agent.avatar} alt={agent.name} />
      <AvatarFallback>{agent.name.slice(0, 2).toUpperCase()}</AvatarFallback>
    </Avatar>
    <div class="flex-1 min-w-0">
      <div class="font-medium text-slate-900 dark:text-slate-100 truncate">{agent.name}</div>
      {#if agent.email}
        <div class="text-xs text-slate-500 truncate">{agent.email}</div>
      {/if}
    </div>
    <Badge variant={statusColors[status]}>{status}</Badge>
  </div>
  
  <div class="space-y-1">
    <div class="flex justify-between text-xs text-slate-500">
      <span>Capacity</span>
      <span>{current} / {capacity}</span>
    </div>
    <Progress value={percentage} class="h-2" />
  </div>
</div>
