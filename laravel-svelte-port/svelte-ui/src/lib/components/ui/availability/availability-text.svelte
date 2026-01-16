<script lang="ts">
  import { cn } from '$lib/utils';

  interface Props {
    status: 'online' | 'busy' | 'offline';
    customText?: string;
    class?: string;
  }

  let { status, customText, class: className }: Props = $props();

  const statusConfig = {
    online: {
      color: 'bg-green-500',
      text: 'We\'re online',
      description: 'We typically reply within a few minutes'
    },
    busy: {
      color: 'bg-yellow-500',
      text: 'We\'re busy',
      description: 'We\'ll get back to you as soon as possible'
    },
    offline: {
      color: 'bg-slate-400',
      text: 'We\'re away',
      description: 'Leave us a message and we\'ll respond when we\'re back'
    }
  };

  const config = $derived(statusConfig[status]);
</script>

<div class={cn('flex items-center gap-2', className)}>
  <span class={cn('h-2 w-2 rounded-full', config.color)}></span>
  <div>
    <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
      {customText || config.text}
    </div>
    <div class="text-xs text-slate-500 dark:text-slate-400">
      {config.description}
    </div>
  </div>
</div>
