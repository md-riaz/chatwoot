<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface Props {
    imageUrl?: string;
    title: string;
    subtitle?: string;
    buttons?: Array<{ id: string; title: string; type?: 'url' | 'postback' }>;
    class?: string;
  }

  let { imageUrl, title, subtitle, buttons = [], class: className }: Props = $props();
</script>

<div class={cn('max-w-sm rounded-lg overflow-hidden bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700', className)}>
  {#if imageUrl}
    <img src={imageUrl} alt={title} class="w-full h-40 object-cover" />
  {/if}
  <div class="p-3">
    <h4 class="font-semibold text-slate-900 dark:text-slate-100">{title}</h4>
    {#if subtitle}
      <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{subtitle}</p>
    {/if}
  </div>
  {#if buttons.length > 0}
    <div class="border-t border-slate-200 dark:border-slate-700">
      {#each buttons as button}
        <Button variant="ghost" class="w-full rounded-none justify-center text-woot-600">
          {button.title}
        </Button>
      {/each}
    </div>
  {/if}
</div>
