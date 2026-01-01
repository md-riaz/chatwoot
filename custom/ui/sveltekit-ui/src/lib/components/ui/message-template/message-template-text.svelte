<script lang="ts">
  import { cn } from '$lib/utils';

  interface Props {
    header?: string;
    body: string;
    footer?: string;
    class?: string;
  }

  let { header, body, footer, class: className }: Props = $props();
</script>

<div class={cn('max-w-sm rounded-lg bg-woot-50 p-3 dark:bg-woot-900', className)}>
  {#if header}
    <div class="mb-2 font-semibold text-slate-900 dark:text-slate-100">{header}</div>
  {/if}
  <div class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">{body}</div>
  {#if footer}
    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">{footer}</div>
  {/if}
</div>
