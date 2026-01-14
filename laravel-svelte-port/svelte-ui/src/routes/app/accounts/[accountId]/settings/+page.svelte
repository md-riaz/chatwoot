<script lang="ts">
  /**
   * Settings Home Page
   * Overview of all settings sections
   */
  
  import { Settings, Users, Bell, Lock, Palette, Globe } from '@lucide/svelte';
  import * as Card from '$lib/components/ui/card';
  import { ClickableCard } from '$lib/components/custom';
  import { Button } from '$lib/components/ui/button';
  import { goto } from '$app/navigation';
  
  const settingsSections = [
    {
      id: 'general',
      title: 'General Settings',
      description: 'Manage your account preferences and basic settings',
      icon: Settings,
      href: '/app/settings/general',
    },
    {
      id: 'account',
      title: 'Account Settings',
      description: 'Update your profile, email, and account details',
      icon: Users,
      href: '/app/settings/account',
    },
    {
      id: 'notifications',
      title: 'Notifications',
      description: 'Configure email and push notification preferences',
      icon: Bell,
      href: '/app/settings/notifications',
    },
    {
      id: 'security',
      title: 'Security & Privacy',
      description: 'Manage passwords, two-factor authentication, and privacy',
      icon: Lock,
      href: '/app/settings/security',
    },
    {
      id: 'appearance',
      title: 'Appearance',
      description: 'Customize theme, language, and display preferences',
      icon: Palette,
      href: '/app/settings/appearance',
    },
    {
      id: 'integrations',
      title: 'Integrations',
      description: 'Connect with third-party apps and services',
      icon: Globe,
      href: '/app/settings/integrations',
    },
  ];
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Settings</h1>
    <p class="text-muted-foreground mt-2">
      Manage your account settings and preferences
    </p>
  </div>
  
  <div class="grid gap-4 md:grid-cols-2">
    {#each settingsSections as section}
      <ClickableCard class="hover:border-primary transition-colors" onclick={() => goto(section.href)}>
        <Card.Header>
          <div class="flex items-start gap-4">
            <div class="p-2 bg-primary/10 rounded-lg">
              <svelte:component this={section.icon} class="h-6 w-6 text-primary" />
            </div>
            <div class="flex-1">
              <Card.Title class="text-base">{section.title}</Card.Title>
              <Card.Description class="text-sm mt-1">
                {section.description}
              </Card.Description>
            </div>
          </div>
        </Card.Header>
      </ClickableCard>
    {/each}
  </div>
</div>
