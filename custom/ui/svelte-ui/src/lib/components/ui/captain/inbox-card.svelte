<script lang="ts">
  import { cn } from '$lib/utils';
  import { Badge } from '$lib/components/ui/badge';

  interface Props {
    name: string;
    channelType: 'web' | 'email' | 'whatsapp' | 'facebook' | 'twitter' | 'telegram' | 'api';
    conversationCount?: number;
    isEnabled?: boolean;
    class?: string;
  }

  let { name, channelType, conversationCount = 0, isEnabled = true, class: className }: Props = $props();

  const channelIcons: Record<string, string> = {
    web: '🌐',
    email: '📧',
    whatsapp: '💬',
    facebook: '📘',
    twitter: '🐦',
    telegram: '✈️',
    api: '🔌'
  };
</script>

<div class={cn(
  'flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800',
  !isEnabled && 'opacity-50',
  className
)}>
  <div class="flex-shrink-0 text-2xl">{channelIcons[channelType] || '📨'}</div>
  <div class="flex-1 min-w-0">
    <div class="font-medium text-slate-900 dark:text-slate-100 truncate">{name}</div>
    <div class="text-xs text-slate-500 dark:text-slate-400 capitalize">{channelType}</div>
  </div>
  <div class="flex items-center gap-2">
    {#if conversationCount > 0}
      <Badge variant="secondary">{conversationCount} chats</Badge>
    {/if}
    {#if !isEnabled}
      <Badge variant="outline">Disabled</Badge>
    {/if}
  </div>
</div>
