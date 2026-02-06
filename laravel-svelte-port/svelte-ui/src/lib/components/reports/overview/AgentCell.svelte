<script lang="ts">
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  
  interface Agent {
    id: number;
    name: string;
    availableName?: string;
    email: string;
    thumbnail?: string;
    availabilityStatus?: 'online' | 'busy' | 'offline';
  }
  
  interface Props {
    agent: Agent;
  }
  
  let { agent }: Props = $props();
  
  const displayName = $derived(agent.availableName || agent.name);
  const initials = $derived.by(() => {
    return displayName
      .split(' ')
      .map(n => n[0])
      .join('')
      .toUpperCase()
      .slice(0, 2);
  });
  
  // Status indicator colors
  const statusColors = {
    online: 'bg-green-500',
    busy: 'bg-yellow-500', 
    offline: 'bg-gray-400'
  };
  
  const statusColor = $derived(
    statusColors[agent.availabilityStatus || 'offline']
  );
</script>

<div class="flex items-center text-left">
  <div class="relative">
    <Avatar class="h-8 w-8">
      {#if agent.thumbnail}
        <AvatarImage src={agent.thumbnail} alt={displayName} />
      {/if}
      <AvatarFallback class="text-xs">
        {initials}
      </AvatarFallback>
    </Avatar>
    
    <!-- Status indicator -->
    {#if agent.availabilityStatus}
      <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white dark:border-slate-900 {statusColor}"></div>
    {/if}
  </div>
  
  <div class="flex flex-col min-w-0 ml-3">
    <h6 class="overflow-hidden text-sm m-0 leading-[1.2] text-slate-900 dark:text-slate-100 whitespace-nowrap text-ellipsis font-medium">
      {displayName}
    </h6>
    <span class="text-xs text-slate-600 dark:text-slate-400">
      {agent.email}
    </span>
  </div>
</div>