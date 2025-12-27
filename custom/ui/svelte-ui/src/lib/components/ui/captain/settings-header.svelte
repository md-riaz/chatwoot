<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';

  interface Props {
    title: string;
    description?: string;
    status?: 'active' | 'inactive' | 'training';
    onSettings?: () => void;
    class?: string;
  }

  let { title, description, status = 'active', onSettings, class: className }: Props = $props();

  const statusColors = {
    active: 'success',
    inactive: 'secondary',
    training: 'warning'
  } as const;
</script>

<div class={cn('flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700', className)}>
  <div class="flex items-center gap-3">
    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-woot-400 to-woot-600 flex items-center justify-center text-white font-bold">
      AI
    </div>
    <div>
      <div class="flex items-center gap-2">
        <h2 class="font-semibold text-slate-900 dark:text-slate-100">{title}</h2>
        <Badge variant={statusColors[status]}>{status}</Badge>
      </div>
      {#if description}
        <p class="text-sm text-slate-500 dark:text-slate-400">{description}</p>
      {/if}
    </div>
  </div>
  <Button variant="outline" onclick={onSettings}>
    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
    Settings
  </Button>
</div>
