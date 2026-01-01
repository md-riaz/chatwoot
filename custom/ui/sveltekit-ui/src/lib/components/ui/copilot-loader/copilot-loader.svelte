<script lang="ts">
  import { cn } from '$lib/utils';

  interface Props {
    message?: string;
    class?: string;
  }

  let { message = 'Thinking...', class: className }: Props = $props();
</script>

<div class={cn('flex items-center gap-3 p-3', className)}>
  <div class="flex gap-1">
    <span class="h-2 w-2 bg-woot-500 rounded-full animate-bounce" style="animation-delay: 0ms" />
    <span class="h-2 w-2 bg-woot-500 rounded-full animate-bounce" style="animation-delay: 150ms" />
    <span class="h-2 w-2 bg-woot-500 rounded-full animate-bounce" style="animation-delay: 300ms" />
  </div>
  <span class="text-sm text-slate-500 dark:text-slate-400">{message}</span>
</div>
