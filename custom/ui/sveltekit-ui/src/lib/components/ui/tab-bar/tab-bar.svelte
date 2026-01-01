<script lang="ts">
  import { cn } from '$lib/utils';

  interface TabItem {
    label: string;
    count?: number;
  }

  let {
    tabs = [],
    initialActiveTab = 0,
    class: className = '',
    onTabChange = (_tab: TabItem, _index: number) => {},
    ...restProps
  }: {
    tabs: TabItem[];
    initialActiveTab?: number;
    class?: string;
    onTabChange?: (tab: TabItem, index: number) => void;
  } = $props();

  let activeTab = $state(initialActiveTab);

  function handleTabClick(tab: TabItem, index: number) {
    activeTab = index;
    onTabChange(tab, index);
  }
</script>

<div class={cn('flex items-center gap-1 border-b', className)} {...restProps}>
  {#each tabs as tab, index}
    <button
      type="button"
      class={cn(
        'px-4 py-2 text-sm font-medium transition-colors relative',
        'hover:text-foreground',
        activeTab === index
          ? 'text-foreground border-b-2 border-primary -mb-px'
          : 'text-muted-foreground'
      )}
      onclick={() => handleTabClick(tab, index)}
    >
      {tab.label}
      {#if tab.count !== undefined}
        <span
          class={cn(
            'ml-2 px-1.5 py-0.5 text-xs rounded-full',
            activeTab === index
              ? 'bg-primary text-primary-foreground'
              : 'bg-muted text-muted-foreground'
          )}
        >
          {tab.count}
        </span>
      {/if}
    </button>
  {/each}
</div>
