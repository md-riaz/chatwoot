<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface Tool {
    id: string;
    name: string;
    icon: string;
    description: string;
  }

  interface Props {
    tools?: Tool[];
    onSelect?: (toolId: string) => void;
    class?: string;
  }

  let { 
    tools = [
      { id: 'knowledge', name: 'Knowledge Base', icon: '📚', description: 'Search documentation' },
      { id: 'calendar', name: 'Calendar', icon: '📅', description: 'Schedule meetings' },
      { id: 'crm', name: 'CRM', icon: '👥', description: 'Access customer data' },
      { id: 'orders', name: 'Orders', icon: '📦', description: 'Check order status' }
    ],
    onSelect,
    class: className 
  }: Props = $props();
  
  let isOpen = $state(false);
</script>

<div class={cn('relative', className)}>
  <Button variant="outline" onclick={() => isOpen = !isOpen}>
    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
    Tools
    <svg class={cn('h-4 w-4 ml-2 transition-transform', isOpen && 'rotate-180')} fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </Button>
  
  {#if isOpen}
    <div class="absolute top-full left-0 mt-1 w-64 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg z-10">
      {#each tools as tool}
        <button
          class="w-full flex items-start gap-3 p-3 hover:bg-slate-50 dark:hover:bg-slate-700 first:rounded-t-lg last:rounded-b-lg"
          onclick={() => {
            onSelect?.(tool.id);
            isOpen = false;
          }}
        >
          <span class="text-xl">{tool.icon}</span>
          <div class="text-left">
            <div class="font-medium text-slate-900 dark:text-slate-100">{tool.name}</div>
            <div class="text-xs text-slate-500">{tool.description}</div>
          </div>
        </button>
      {/each}
    </div>
  {/if}
</div>
