<script lang="ts">
  import { cn } from '$lib/utils';
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';

  interface Props {
    name: string;
    email?: string;
    phone?: string;
    avatar?: string;
    status?: 'online' | 'offline' | 'away';
    labels?: string[];
    onEdit?: () => void;
    onMore?: () => void;
    class?: string;
  }

  let { name, email, phone, avatar, status, labels = [], onEdit, onMore, class: className }: Props = $props();

  const statusColors = {
    online: 'bg-green-500',
    offline: 'bg-slate-400',
    away: 'bg-yellow-500'
  };
</script>

<div class={cn('flex items-start gap-4 p-4', className)}>
  <div class="relative">
    <Avatar class="h-16 w-16">
      <AvatarImage src={avatar} alt={name} />
      <AvatarFallback>{name.slice(0, 2).toUpperCase()}</AvatarFallback>
    </Avatar>
    {#if status}
      <span class={cn('absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white dark:border-slate-900', statusColors[status])}></span>
    {/if}
  </div>
  
  <div class="flex-1 min-w-0">
    <div class="flex items-center gap-2">
      <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 truncate">{name}</h2>
      {#if status}
        <Badge variant="outline" class="capitalize">{status}</Badge>
      {/if}
    </div>
    {#if email}
      <div class="text-sm text-slate-500 dark:text-slate-400">{email}</div>
    {/if}
    {#if phone}
      <div class="text-sm text-slate-500 dark:text-slate-400">{phone}</div>
    {/if}
    {#if labels.length > 0}
      <div class="flex flex-wrap gap-1 mt-2">
        {#each labels as label}
          <Badge variant="secondary" class="text-xs">{label}</Badge>
        {/each}
      </div>
    {/if}
  </div>

  <div class="flex items-center gap-2">
    <Button variant="outline" size="sm" onclick={onEdit}>
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
      </svg>
    </Button>
    <Button variant="ghost" size="sm" onclick={onMore}>
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
      </svg>
    </Button>
  </div>
</div>
