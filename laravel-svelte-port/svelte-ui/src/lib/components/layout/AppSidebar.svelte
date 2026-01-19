<script lang="ts">
  import * as Sidebar from "$lib/components/ui/sidebar/index.js";
  import {
    ChevronDown,
    Search,
    Plus
  } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { navigate, isRouteActive, isAnyRouteActive } from '$lib/routing/navigation';
  import type { NavigationItem, SidebarSection } from './types';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import type { ComponentProps } from "svelte";
  import { authStore } from '$lib/stores/auth.svelte';
  import SidebarAccountSwitcher from './SidebarAccountSwitcher.svelte';
  import SidebarProfileMenu from './SidebarProfileMenu.svelte';
  import Logo from './Logo.svelte';

  let { ...restProps }: ComponentProps<typeof Sidebar.Root> = $props();

  const accountId = authStore.currentAccountId;
  const currentUser = $derived(authStore.currentUser);
  const currentAccount = $derived(authStore.currentAccount);
  const isLoggedIn = $derived(authStore.isLoggedIn);

  const navigationSections: SidebarSection[] = $derived([
    {
      id: 'main',
      items: [
        {
          id: 'inbox',
          label: 'Inbox',
          icon: 'inbox',
          href: `/app/accounts/${accountId}/inbox-view`,
          activeOn: [
            `/app/accounts/${accountId}/inbox-view`,
          ],
        },
      ],
    },
    {
      id: 'conversations',
      title: 'Conversations',
      items: [
        {
          id: 'all',
          label: 'All Conversations',
          icon: 'message-square',
          href: `/app/accounts/${accountId}/conversations`,
          activeOn: [
            `/app/accounts/${accountId}/conversations`,
            `/app/accounts/${accountId}/conversations/`,
          ],
        },
        {
          id: 'mentions',
          label: 'Mentions',
          icon: 'message-square',
          href: `/app/accounts/${accountId}/conversations/mentions`,
          activeOn: [
            `/app/accounts/${accountId}/conversations/mentions`,
          ],
        },
        {
          id: 'unattended',
          label: 'Unattended',
          icon: 'message-square',
          href: `/app/accounts/${accountId}/conversations/unattended`,
          activeOn: [
            `/app/accounts/${accountId}/conversations/unattended`,
          ],
        },
        {
          id: 'folders',
          label: 'Folders',
          icon: 'folder',
          children: labelsStore.sidebarLabels.map(label => ({
            id: `label-${label.id}`,
            label: label.title,
            href: `/app/accounts/${accountId}/conversations/label/${encodeURIComponent(
              label.title
            )}`,
            activeOn: [
              `/app/accounts/${accountId}/conversations/label/${encodeURIComponent(
                label.title
              )}`,
            ],
          })),
        },
        {
          id: 'teams',
          label: 'Teams',
          icon: 'users',
          children: teamsStore.myTeams.map(team => ({
            id: `team-${team.id}`,
            label: team.name,
            href: `/app/accounts/${accountId}/conversations/team/${team.id}`,
            activeOn: [
              `/app/accounts/${accountId}/conversations/team/${team.id}`,
            ],
          })),
        },
        {
          id: 'channels',
          label: 'Channels',
          icon: 'inbox',
          children: inboxesStore.sortedInboxes.map(inbox => ({
            id: `inbox-${inbox.id}`,
            label: inbox.name,
            href: `/app/accounts/${accountId}/conversations/inbox/${inbox.id}`,
            activeOn: [
              `/app/accounts/${accountId}/conversations/inbox/${inbox.id}`,
            ],
          })),
        },
      ],
    },
    {
      id: 'contacts',
      title: 'Contacts',
      items: [
        {
          id: 'contacts-all',
          label: 'All Contacts',
          icon: 'users',
          href: `/app/accounts/${accountId}/contacts`,
          activeOn: [
            `/app/accounts/${accountId}/contacts`,
            `/app/accounts/${accountId}/contacts/`,
          ],
        },
        {
          id: 'contacts-active',
          label: 'Active',
          icon: 'users',
          href: `/app/accounts/${accountId}/contacts/active`,
          activeOn: [
            `/app/accounts/${accountId}/contacts/active`,
          ],
        },
      ],
    },
    {
      id: 'companies',
      title: 'Companies',
      items: [
        {
          id: 'companies-all',
          label: 'All Companies',
          icon: 'building-2',
          href: `/app/accounts/${accountId}/companies`,
          activeOn: [
            `/app/accounts/${accountId}/companies`,
          ],
        },
      ],
    },
    {
      id: 'reports',
      title: 'Reports',
      items: [
        {
          id: 'reports-agent',
          label: 'Agent Reports',
          icon: 'bar-chart-3',
          href: `/app/accounts/${accountId}/reports/agent`,
          activeOn: [
            `/app/accounts/${accountId}/reports/agent`,
          ],
        },
        {
          id: 'reports-label',
          label: 'Label Reports',
          icon: 'bar-chart-3',
          href: `/app/accounts/${accountId}/reports/label`,
          activeOn: [
            `/app/accounts/${accountId}/reports/label`,
          ],
        },
        {
          id: 'reports-inbox',
          label: 'Inbox Reports',
          icon: 'bar-chart-3',
          href: `/app/accounts/${accountId}/reports/inbox`,
          activeOn: [
            `/app/accounts/${accountId}/reports/inbox`,
          ],
        },
        {
          id: 'reports-team',
          label: 'Team Reports',
          icon: 'bar-chart-3',
          href: `/app/accounts/${accountId}/reports/team`,
          activeOn: [
            `/app/accounts/${accountId}/reports/team`,
          ],
        },
      ],
    },
    {
      id: 'campaigns',
      title: 'Campaigns',
      items: [
        {
          id: 'campaigns-livechat',
          label: 'Live chat',
          icon: 'megaphone',
          href: `/app/accounts/${accountId}/campaigns/livechat`,
          activeOn: [
            `/app/accounts/${accountId}/campaigns/livechat`,
          ],
        },
        {
          id: 'campaigns-sms',
          label: 'SMS',
          icon: 'megaphone',
          href: `/app/accounts/${accountId}/campaigns/sms`,
          activeOn: [
            `/app/accounts/${accountId}/campaigns/sms`,
          ],
        },
        {
          id: 'campaigns-whatsapp',
          label: 'WhatsApp',
          icon: 'megaphone',
          href: `/app/accounts/${accountId}/campaigns/whatsapp`,
          activeOn: [
            `/app/accounts/${accountId}/campaigns/whatsapp`,
          ],
        },
      ],
    },
    {
      id: 'portals',
      title: 'Help Center',
      items: [
        {
          id: 'portal-articles',
          label: 'Articles',
          icon: 'library',
          href: `/app/accounts/${accountId}/portals/articles`,
          activeOn: [
            `/app/accounts/${accountId}/portals/articles`,
          ],
        },
        {
          id: 'portal-categories',
          label: 'Categories',
          icon: 'library',
          href: `/app/accounts/${accountId}/portals/categories`,
          activeOn: [
            `/app/accounts/${accountId}/portals/categories`,
          ],
        },
        {
          id: 'portal-locales',
          label: 'Locales',
          icon: 'library',
          href: `/app/accounts/${accountId}/portals/locales`,
          activeOn: [
            `/app/accounts/${accountId}/portals/locales`,
          ],
        },
        {
          id: 'portal-settings',
          label: 'Settings',
          icon: 'library',
          href: `/app/accounts/${accountId}/portals/settings`,
          activeOn: [
            `/app/accounts/${accountId}/portals/settings`,
          ],
        },
      ],
    },
    {
      id: 'settings',
      title: 'Settings',
      items: [
        {
          id: 'settings-account',
          label: 'Account Settings',
          icon: 'briefcase',
          href: `/app/accounts/${accountId}/settings/account`,
          activeOn: [
            `/app/accounts/${accountId}/settings/account`,
          ],
        },
        {
          id: 'settings-agents',
          label: 'Agents',
          icon: 'square-user',
          href: `/app/accounts/${accountId}/settings/agents`,
          activeOn: [
            `/app/accounts/${accountId}/settings/agents`,
          ],
        },
        {
          id: 'settings-teams',
          label: 'Teams',
          icon: 'users',
          href: `/app/accounts/${accountId}/settings/teams`,
          activeOn: [
            `/app/accounts/${accountId}/settings/teams`,
          ],
        },
        {
          id: 'settings-assignment',
          label: 'Agent Assignment',
          icon: 'user-cog',
          href: `/app/accounts/${accountId}/settings/assignment`,
          activeOn: [
            `/app/accounts/${accountId}/settings/assignment`,
          ],
        },
        {
          id: 'settings-inboxes',
          label: 'Inboxes',
          icon: 'inbox',
          href: `/app/accounts/${accountId}/settings/inboxes`,
          activeOn: [
            `/app/accounts/${accountId}/settings/inboxes`,
          ],
        },
        {
          id: 'settings-labels',
          label: 'Labels',
          icon: 'tags',
          href: `/app/accounts/${accountId}/settings/labels`,
          activeOn: [
            `/app/accounts/${accountId}/settings/labels`,
          ],
        },
        {
          id: 'settings-attributes',
          label: 'Custom Attributes',
          icon: 'code',
          href: `/app/accounts/${accountId}/settings/attributes`,
          activeOn: [
            `/app/accounts/${accountId}/settings/attributes`,
          ],
        },
        {
          id: 'settings-automation',
          label: 'Automation',
          icon: 'workflow',
          href: `/app/accounts/${accountId}/settings/automation`,
          activeOn: [
            `/app/accounts/${accountId}/settings/automation`,
          ],
        },
        {
          id: 'settings-agent-bots',
          label: 'Agent Bots',
          icon: 'bot',
          href: `/app/accounts/${accountId}/settings/agent-bots`,
          activeOn: [
            `/app/accounts/${accountId}/settings/agent-bots`,
          ],
        },
        {
          id: 'settings-macros',
          label: 'Macros',
          icon: 'toy-brick',
          href: `/app/accounts/${accountId}/settings/macros`,
          activeOn: [
            `/app/accounts/${accountId}/settings/macros`,
          ],
        },
        {
          id: 'settings-canned',
          label: 'Canned Responses',
          icon: 'message-square-quote',
          href: `/app/accounts/${accountId}/settings/canned`,
          activeOn: [
            `/app/accounts/${accountId}/settings/canned`,
          ],
        },
        {
          id: 'settings-integrations',
          label: 'Integrations',
          icon: 'blocks',
          href: `/app/accounts/${accountId}/settings/integrations`,
          activeOn: [
            `/app/accounts/${accountId}/settings/integrations`,
          ],
        },
        {
          id: 'settings-audit',
          label: 'Audit Logs',
          icon: 'briefcase',
          href: `/app/accounts/${accountId}/settings/audit`,
          activeOn: [
            `/app/accounts/${accountId}/settings/audit`,
          ],
        },
        {
          id: 'settings-roles',
          label: 'Custom Roles',
          icon: 'shield-plus',
          href: `/app/accounts/${accountId}/settings/roles`,
          activeOn: [
            `/app/accounts/${accountId}/settings/roles`,
          ],
        },
        {
          id: 'settings-sla',
          label: 'SLA',
          icon: 'clock-alert',
          href: `/app/accounts/${accountId}/settings/sla`,
          activeOn: [
            `/app/accounts/${accountId}/settings/sla`,
          ],
        },
        {
          id: 'settings-security',
          label: 'Security',
          icon: 'shield',
          href: `/app/accounts/${accountId}/settings/security`,
          activeOn: [
            `/app/accounts/${accountId}/settings/security`,
          ],
        },
        {
          id: 'settings-billing',
          label: 'Billing',
          icon: 'credit-card',
          href: `/app/accounts/${accountId}/settings/billing`,
          activeOn: [
            `/app/accounts/${accountId}/settings/billing`,
          ],
        },
      ],
    },
  ]);

  function handleAccountSwitch(id: number) {
    authStore.setActiveAccount(id);
    navigate(`/app/accounts/${id}`);
  }
  
  function onSearchClick() {
    if (accountId) {
      navigate(`/app/accounts/${accountId}/search`);
    }
  }

  function onComposeClick() {
    navigate(`/app/accounts/${accountId}/conversations/new`);
  }

  import SidebarGroup from './SidebarGroup.svelte';
</script>

<Sidebar.Root collapsible="icon" {...restProps}>
  <Sidebar.Header>
    <div class="flex flex-col gap-2 p-2">
      {#if isLoggedIn && currentAccount}
        <div class="flex gap-2 items-center px-2 min-w-0 group-data-[collapsible=icon]:hidden">
          <div class="grid flex-shrink-0 place-content-center size-6">
            <Logo class="h-4 w-4" aria-label="Chatwoot logo" />
          </div>
          <div class="flex-shrink-0 w-px h-3 bg-border" />
          <SidebarAccountSwitcher class="flex-1 min-w-0" />
        </div>
      {:else}
        <div class="flex items-center gap-2 px-2">
          <div class="grid flex-shrink-0 place-content-center size-6">
            <Logo class="h-4 w-4" aria-label="Chatwoot logo" />
          </div>
          <span class="text-lg font-semibold truncate">ClearLine</span>
        </div>
      {/if}
      <div class="flex gap-2 px-2">
        <button
          type="button"
          class="flex gap-2 items-center px-2 py-1 w-full h-7 rounded-md border border-input bg-muted text-xs text-muted-foreground group-data-[collapsible=icon]:hidden"
          onclick={onSearchClick}
        >
          <Search class="h-4 w-4 text-muted-foreground" />
          <span class="flex-1 text-left truncate">Search</span>
        </button>
        <Button
          variant="ghost"
          size="icon"
          onclick={onComposeClick}
          class="h-7 w-7 group-data-[collapsible=icon]:hidden"
        >
          <Plus class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </Sidebar.Header>
  <Sidebar.Content>
    {#each navigationSections as section}
      <SidebarGroup {section} />
    {/each}
  </Sidebar.Content>
  <Sidebar.Footer>
    <SidebarProfileMenu />
  </Sidebar.Footer>
  <Sidebar.Rail />
</Sidebar.Root>
