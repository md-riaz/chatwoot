<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface ListItem {
    id: string;
    title: string;
    description?: string;
  }

  interface ListSection {
    title: string;
    items: ListItem[];
  }

  interface Props {
    headerText?: string;
    bodyText: string;
    buttonText: string;
    sections: ListSection[];
    class?: string;
  }

  let { headerText, bodyText, buttonText, sections = [], class: className }: Props = $props();
  let showList = $state(false);
</script>

<div class={cn('max-w-sm', className)}>
  <div class="rounded-lg bg-woot-50 dark:bg-woot-900 overflow-hidden">
    {#if headerText}
      <div class="p-3 font-semibold text-slate-900 dark:text-slate-100 border-b border-woot-100 dark:border-woot-800">
        {headerText}
      </div>
    {/if}
    <div class="p-3 text-sm text-slate-700 dark:text-slate-300">{bodyText}</div>
    <div class="border-t border-woot-100 dark:border-woot-800">
      <Button 
        variant="ghost" 
        class="w-full rounded-none justify-center text-woot-600 gap-2"
        onclick={() => showList = !showList}
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
        {buttonText}
      </Button>
    </div>
  </div>
  
  {#if showList}
    <div class="mt-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden">
      {#each sections as section}
        <div class="border-b border-slate-200 dark:border-slate-700 last:border-b-0">
          <div class="px-3 py-2 bg-slate-50 dark:bg-slate-900 font-medium text-sm text-slate-600 dark:text-slate-400">
            {section.title}
          </div>
          {#each section.items as item}
            <button class="w-full px-3 py-2 text-left hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
              <div class="font-medium text-slate-900 dark:text-slate-100">{item.title}</div>
              {#if item.description}
                <div class="text-xs text-slate-500">{item.description}</div>
              {/if}
            </button>
          {/each}
        </div>
      {/each}
    </div>
  {/if}
</div>
