<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface Action {
    id: string;
    icon: string;
    label: string;
    variant?: 'default' | 'ghost' | 'destructive';
  }

  interface Props {
    title: string;
    subtitle?: string;
    actions?: Action[];
    onAction?: (actionId: string) => void;
    onClose?: () => void;
    class?: string;
  }

  let { title, subtitle, actions = [], onAction, onClose, class: className }: Props = $props();
</script>

<div class={cn('flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700', className)}>
  <div class="flex-1 min-w-0">
    <h3 class="font-semibold text-slate-900 dark:text-slate-100 truncate">{title}</h3>
    {#if subtitle}
      <p class="text-sm text-slate-500 dark:text-slate-400 truncate">{subtitle}</p>
    {/if}
  </div>
  
  <div class="flex items-center gap-1">
    {#each actions as action}
      <Button 
        variant={action.variant || 'ghost'} 
        size="sm"
        onclick={() => onAction?.(action.id)}
        title={action.label}
      >
        {action.icon}
      </Button>
    {/each}
    
    {#if onClose}
      <Button variant="ghost" size="sm" onclick={onClose}>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </Button>
    {/if}
  </div>
</div>
