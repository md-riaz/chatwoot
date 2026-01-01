<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface Portal {
    id: string;
    name: string;
    slug: string;
    articleCount?: number;
    isDefault?: boolean;
  }

  interface Props {
    portals: Portal[];
    selectedId?: string;
    onSelect?: (portalId: string) => void;
    class?: string;
  }

  let { portals = [], selectedId, onSelect, class: className }: Props = $props();
  let isOpen = $state(false);
  
  const selectedPortal = $derived(portals.find(p => p.id === selectedId) || portals[0]);
</script>

<div class={cn('relative', className)}>
  <Button 
    variant="outline" 
    class="w-full justify-between"
    onclick={() => isOpen = !isOpen}
  >
    <div class="flex items-center gap-2">
      <span class="text-lg">🌐</span>
      <span>{selectedPortal?.name || 'Select Portal'}</span>
    </div>
    <svg class={cn('h-4 w-4 transition-transform', isOpen && 'rotate-180')} fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
    </svg>
  </Button>
  
  {#if isOpen}
    <div class="absolute top-full left-0 right-0 mt-1 py-1 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg z-10">
      {#each portals as portal}
        <button
          class={cn(
            'w-full flex items-center justify-between px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-700',
            portal.id === selectedId && 'bg-woot-50 dark:bg-woot-900/20'
          )}
          onclick={() => {
            onSelect?.(portal.id);
            isOpen = false;
          }}
        >
          <div>
            <div class="font-medium text-slate-900 dark:text-slate-100">
              {portal.name}
              {#if portal.isDefault}
                <span class="ml-1 text-xs text-woot-500">(Default)</span>
              {/if}
            </div>
            <div class="text-xs text-slate-500">/{portal.slug}</div>
          </div>
          {#if portal.articleCount}
            <span class="text-xs text-slate-400">{portal.articleCount} articles</span>
          {/if}
        </button>
      {/each}
    </div>
  {/if}
</div>
