<script lang="ts">
  /**
   * SettingsNav - Settings navigation sidebar
   */
  
  import { Settings, Users, Bell, Lock, Palette, Globe } from '@lucide/svelte';
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
      id: 'general',
      label: 'General',
      icon: 'settings',
      href: `${basePath}/general`,
    },
    {
      id: 'account',
      label: 'Account',
      icon: 'users',
      href: `${basePath}/account`,
    },
    {
      id: 'notifications',
      label: 'Notifications',
      icon: 'bell',
      href: `${basePath}/notifications`,
    },
    {
      id: 'security',
      label: 'Security',
      icon: 'lock',
      href: `${basePath}/security`,
    },
    {
      id: 'appearance',
      label: 'Appearance',
      icon: 'palette',
      href: `${basePath}/appearance`,
    },
    {
      id: 'integrations',
      label: 'Integrations',
      icon: 'globe',
      href: `${basePath}/integrations`,
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
