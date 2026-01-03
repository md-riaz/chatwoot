<script lang="ts">
  /**
   * SettingsNav - Settings navigation sidebar
   */
  
  import {
    Settings,
    Users,
    Bell,
    Lock,
    Palette,
    Globe,
    UserCog,
    Inbox,
    FileText,
    CreditCard,
    Clock,
  } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { isRouteActive } from '$lib/routing/navigation';
  import { navigate } from '$lib/routing/navigation';
  import type { SettingsNavItem } from './types';
  
  interface Props {
    basePath?: string;
  }
  
  let { basePath = '/app/settings' }: Props = $props();
  
  const navItems: SettingsNavItem[] = [
    {
      id: 'account',
      label: 'Account',
      icon: 'settings',
      href: `${basePath}/account`,
    },
    {
      id: 'profile',
      label: 'Profile',
      icon: 'users',
      href: `${basePath}/profile`,
    },
    {
      id: 'agents',
      label: 'Agents',
      icon: 'user-cog',
      href: `${basePath}/agents`,
    },
    {
      id: 'inboxes',
      label: 'Inboxes',
      icon: 'inbox',
      href: `${basePath}/inboxes`,
    },
    {
      id: 'attributes',
      label: 'Custom Attributes',
      icon: 'file-text',
      href: `${basePath}/attributes`,
    },
    {
      id: 'notifications',
      label: 'Notifications',
      icon: 'bell',
      href: `${basePath}/notifications`,
    },
    {
      id: 'billing',
      label: 'Billing',
      icon: 'credit-card',
      href: `${basePath}/billing`,
    },
    {
      id: 'automation',
      label: 'Automation',
      icon: 'settings',
      href: `${basePath}/automation`,
    },
    {
      id: 'macros',
      label: 'Macros',
      icon: 'file-text',
      href: `${basePath}/macros`,
    },
    {
      id: 'sla',
      label: 'SLA',
      icon: 'clock',
      href: `${basePath}/sla`,
    },
    {
      id: 'audit-logs',
      label: 'Audit Logs',
      icon: 'file-text',
      href: `${basePath}/audit-logs`,
    },
  ];
  
  function getIcon(iconName: string) {
    const icons: Record<string, any> = {
      settings: Settings,
      users: Users,
      bell: Bell,
      lock: Lock,
      palette: Palette,
      globe: Globe,
      'user-cog': UserCog,
      inbox: Inbox,
      'file-text': FileText,
      'credit-card': CreditCard,
      clock: Clock,
    };
    return icons[iconName] || Settings;
  }
</script>

<nav class="space-y-1">
  {#each navItems as item}
    {@const IconComponent = getIcon(item.icon || 'settings')}
    {@const active = isRouteActive(item.href)}
    
    <Button
      variant={active ? 'secondary' : 'ghost'}
      class="w-full justify-start gap-3"
      onclick={() => navigate(item.href)}
    >
      <svelte:component this={IconComponent} class="h-4 w-4" />
      <span class="flex-1 text-left">{item.label}</span>
      {#if item.badge}
        <Badge variant="default" class="text-xs">
          {item.badge}
        </Badge>
      {/if}
    </Button>
  {/each}
</nav>
