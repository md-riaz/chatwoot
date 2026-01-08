<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface Props {
    text: string;
    buttons: Array<{ id: string; title: string; url?: string; type: 'url' | 'call' }>;
    class?: string;
  }

  let { text, buttons = [], class: className }: Props = $props();
</script>

<div class={cn('max-w-sm rounded-lg overflow-hidden bg-woot-50 dark:bg-woot-900', className)}>
  <div class="p-3 text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{text}</div>
  {#if buttons.length > 0}
    <div class="border-t border-woot-100 dark:border-woot-800 divide-y divide-woot-100 dark:divide-woot-800">
      {#each buttons as button}
        <Button variant="ghost" class="w-full rounded-none justify-center text-woot-600 gap-2">
          {#if button.type === 'url'}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
          {:else}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
          {/if}
          {button.title}
        </Button>
      {/each}
    </div>
  {/if}
</div>
