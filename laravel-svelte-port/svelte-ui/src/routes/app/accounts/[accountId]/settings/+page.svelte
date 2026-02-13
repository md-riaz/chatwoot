<script lang="ts">
  /**
   * Settings Home Page
   * Overview of all settings sections
   */

  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import {
    Lock,
    ScrollText,
    Settings,
    Shield,
    UserCog,
    Users,
  } from '@lucide/svelte';
  import { ClickableCard } from '$lib/components/custom';
  import * as Card from '$lib/components/ui/card';

  const accountId = $derived($page.params.accountId);

  const settingsSections = $derived([
    {
      id: 'account',
      title: 'Account Settings',
      description: 'Manage account preferences and system-level defaults',
      icon: Settings,
      href: `/app/accounts/${accountId}/settings/account`,
    },
    {
      id: 'agents',
      title: 'Agents',
      description: 'Manage team members and access within your account',
      icon: Users,
      href: `/app/accounts/${accountId}/settings/agents`,
    },
    {
      id: 'assignment-policy',
      title: 'Assignment Policy',
      description: 'Configure automatic conversation assignment behavior',
      icon: UserCog,
      href: `/app/accounts/${accountId}/settings/assignment-policy`,
    },
    {
      id: 'custom-roles',
      title: 'Custom Roles',
      description: 'Create and maintain account-specific permission bundles',
      icon: Shield,
      href: `/app/accounts/${accountId}/settings/custom-roles`,
    },
    {
      id: 'security',
      title: 'Security',
      description: 'Configure SAML, MFA preferences, and session safeguards',
      icon: Lock,
      href: `/app/accounts/${accountId}/settings/security`,
    },
    {
      id: 'audit-logs',
      title: 'Audit Logs',
      description: 'Review account activity and administrative changes',
      icon: ScrollText,
      href: `/app/accounts/${accountId}/settings/audit-logs`,
    },
  ]);
</script>

<div class="space-y-6">
  <div>
    <h1 class="text-3xl font-bold">Settings</h1>
    <p class="text-muted-foreground mt-2">
      Manage account configuration for the selected workspace.
    </p>
  </div>

  <div class="grid gap-4 md:grid-cols-2">
    {#each settingsSections as section}
      <ClickableCard
        class="hover:border-primary transition-colors"
        onclick={() => goto(section.href)}
      >
        <Card.Header>
          <div class="flex items-start gap-4">
            <div class="p-2 bg-primary/10 rounded-lg">
              <section.icon class="h-6 w-6 text-primary" />
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
