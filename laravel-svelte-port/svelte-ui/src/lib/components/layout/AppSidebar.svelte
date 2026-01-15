<script lang="ts">
  /**
   * AppSidebar - Main navigation sidebar
   * Features: Navigation menu, workspace selector, status filters, collapsible sections
   */
  
  import {
    Home,
    MessageSquare,
    Users,
    Inbox,
    Tags,
    BarChart3,
    Settings,
    ChevronRight,
    X,
  } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import * as Separator from '$lib/components/ui/separator';
  import { page } from '$app/stores';
  import { navigate } from '$lib/routing/navigation';
  import { isRouteActive } from '$lib/routing/navigation';
  import type { NavigationItem, SidebarSection } from './types';
  
  interface Props {
    isOpen?: boolean;
    onClose?: () => void;
    class?: string;
  }
  
  let { isOpen = true, onClose, class: className = '' }: Props = $props();
  
  // Get current accountId from route params
  const accountId = $derived($page.params.accountId);
  
  // Navigation sections
  const navigationSections: SidebarSection[] = $derived([
    {
      id: 'main',
      items: [
        {
          id: 'home',
          label: 'Home',
          icon: 'home',
          href: accountId ? `/app/accounts/${accountId}` : '/app',
          badge: 0,
        },
        {
          id: 'conversations',
          label: 'Conversations',
          icon: 'message-square',
          href: accountId ? `/app/accounts/${accountId}/conversations` : '/app/conversations',
          badge: 12,
        },
        {
          id: 'contacts',
          label: 'Contacts',
          icon: 'users',
          href: accountId ? `/app/accounts/${accountId}/contacts` : '/app/contacts',
          badge: 0,
        },
      ],
    },
    {
      id: 'workspace',
      title: 'Workspace',
      items: [
        {
          id: 'inboxes',
          label: 'Inboxes',
          icon: 'inbox',
          href: accountId ? `/app/accounts/${accountId}/inboxes` : '/app/inboxes',
          badge: 0,
        },
        {
          id: 'labels',
          label: 'Labels',
          icon: 'tags',
          href: accountId ? `/app/accounts/${accountId}/labels` : '/app/labels',
          badge: 0,
        },
        {
          id: 'reports',
          label: 'Reports',
          icon: 'bar-chart-3',
          href: accountId ? `/app/accounts/${accountId}/reports` : '/app/reports',
          badge: 0,
        },
      ],
    },
    {
      id: 'settings',
      title: 'Settings',
      items: [
        {
          id: 'settings',
          label: 'Settings',
          icon: 'settings',
          href: accountId ? `/app/accounts/${accountId}/settings` : '/app/settings',
          badge: 0,
        },
      ],
    },
  ]);
  
  // Icon component mapper
  function getIconComponent(iconName: string) {
    const icons: Record<string, any> = {
      'home': Home,
      'message-square': MessageSquare,
      'users': Users,
      'inbox': Inbox,
      'tags': Tags,
      'bar-chart-3': BarChart3,
      'settings': Settings,
    };
    return icons[iconName] || Home;
  }
  
  function handleNavigate(item: NavigationItem) {
    navigate(item.href);
    // Close mobile menu after navigation
    if (onClose && window.innerWidth < 1024) {
      onClose();
    }
  }
  
  // Check if route is active
  function isItemActive(item: NavigationItem): boolean {
    return isRouteActive(item.href);
  }
</script>

<!-- Mobile backdrop -->
{#if isOpen}
  <button
    type="button"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden cursor-default"
    onclick={onClose}
    aria-label="Close sidebar"
  ></button>
{/if}

<!-- Sidebar -->
<aside
  class="fixed inset-y-0 left-0 z-50 w-64 transform border-r bg-background transition-transform duration-200 ease-in-out lg:static lg:translate-x-0 {isOpen ? 'translate-x-0' : '-translate-x-full'} {className}"
>
  <!-- Header -->
  <div class="flex h-16 items-center justify-between border-b px-4">
    <div class="flex items-center gap-2">
      <span class="text-xl font-semibold">ClearLine</span>
    </div>
    
    <!-- Mobile close button -->
    <Button
      variant="ghost"
      size="icon"
      class="lg:hidden"
      onclick={onClose}
      aria-label="Close sidebar"
    >
      <X class="h-5 w-5" />
    </Button>
  </div>
  
  <!-- Navigation -->
  <nav class="flex-1 space-y-6 overflow-y-auto p-4">
    {#each navigationSections as section, sectionIndex}
      <div class="space-y-1">
        {#if section.title}
          <h3 class="mb-2 px-2 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            {section.title}
          </h3>
        {/if}
        
        {#each section.items as item}
          {@const IconComponent = getIconComponent(item.icon || 'home')}
          {@const active = isItemActive(item)}
          
          <Button
            variant={active ? 'secondary' : 'ghost'}
            class="w-full justify-start gap-3 {active ? 'font-medium' : ''}"
            onclick={() => handleNavigate(item)}
          >
            <IconComponent class="h-4 w-4" />
            <span class="flex-1 text-left">{item.label}</span>
            {#if item.badge && item.badge > 0}
              <Badge variant="default" class="ml-auto">
                {item.badge > 99 ? '99+' : item.badge}
              </Badge>
            {/if}
          </Button>
        {/each}
      </div>
      
      {#if sectionIndex !== navigationSections.length - 1}
        <Separator.Root />
      {/if}
    {/each}
  </nav>
  
  <!-- Footer -->
  <div class="border-t p-4">
    <div class="text-xs text-muted-foreground text-center">
      © 2026 ClearLine
    </div>
  </div>
</aside>
