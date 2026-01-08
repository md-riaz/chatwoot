<script lang="ts">
  import { cn } from '$lib/utils';
  import { Button } from '$lib/components/ui/button';

  interface QuickReplyButton {
    id: string;
    title: string;
    imageUrl?: string;
  }

  interface Props {
    text: string;
    buttons: QuickReplyButton[];
    class?: string;
  }

  let { text, buttons = [], class: className }: Props = $props();
</script>

<div class={cn('max-w-sm', className)}>
  <div class="rounded-lg bg-woot-50 dark:bg-woot-900 p-3 text-sm text-slate-700 dark:text-slate-300">
    {text}
  </div>
  {#if buttons.length > 0}
    <div class="mt-2 flex flex-wrap gap-2">
      {#each buttons as button}
        <Button variant="outline" size="sm" class="rounded-full gap-1">
          {#if button.imageUrl}
            <img src={button.imageUrl} alt="" class="h-4 w-4 rounded-full" />
          {/if}
          {button.title}
        </Button>
      {/each}
    </div>
  {/if}
</div>
