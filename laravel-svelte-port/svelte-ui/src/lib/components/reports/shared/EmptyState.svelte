<script lang="ts">
  import { BarChart3, Users, Building2, Inbox } from 'lucide-svelte';
  
  interface Props {
    title: string;
    description?: string;
    icon?: 'chart' | 'users' | 'teams' | 'inbox';
    actionText?: string;
    onAction?: () => void;
  }
  
  let { 
    title, 
    description = '', 
    icon = 'chart',
    actionText = '',
    onAction
  }: Props = $props();
  
  const iconComponents = {
    chart: BarChart3,
    users: Users,
    teams: Building2,
    inbox: Inbox
  };
  
  const IconComponent = $derived(iconComponents[icon]);
</script>

<div class="flex flex-col items-center justify-center p-8 text-center">
  <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
    <IconComponent class="w-8 h-8 text-slate-400 dark:text-slate-500" />
  </div>
  
  <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100 mb-2">
    {title}
  </h3>
  
  {#if description}
    <p class="text-sm text-slate-600 dark:text-slate-400 mb-4 max-w-sm">
      {description}
    </p>
  {/if}
  
  {#if actionText && onAction}
    <button
      onclick={onAction}
      class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors"
    >
      {actionText}
    </button>
  {/if}
</div>