<script lang="ts">
  import * as Sidebar from '$lib/components/ui/sidebar/index.js';
  import { ChevronDown, Search, Plus } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { _ } from '$lib/i18n';
  import { get } from 'svelte/store';

  // Safe translator: avoid calling the formatter before initial locale is set.
  function safeT(key: string, fallback?: string) {
    try {
      return get(_)(key);
    } catch (e) {
      return fallback ?? key;
    }
  }
  import {
    navigate,
    isRouteActive,
    isAnyRouteActive,
  } from '$lib/routing/navigation';
  import type { NavigationItem, SidebarSection } from './types';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { customViewsStore } from '$lib/stores/customViews.svelte';
  import { notificationsStore } from '$lib/stores/notifications.svelte';
  import type { ComponentProps } from 'svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { globalConfig } from '$lib/stores/globalConfig.svelte';
  import { segmentsStore } from '$lib/stores/segments.svelte';
  import SidebarAccountSwitcher from './SidebarAccountSwitcher.svelte';
  import SidebarProfileMenu from './SidebarProfileMenu.svelte';
  import Logo from './Logo.svelte';

  let { ...restProps }: ComponentProps<typeof Sidebar.Root> = $props();

  const accountId = $derived(authStore.currentAccountId);
  const currentUser = $derived(authStore.currentUser);
  const currentAccount = $derived(authStore.currentAccount);
  const isLoggedIn = $derived(authStore.isLoggedIn);

  function getChannelIcon(channelType: string) {
    const map: Record<string, string> = {
      'Channel::FacebookPage': 'facebook',
      'Channel::TwitterProfile': 'twitter',
      'Channel::TwilioSms': 'smartphone',
      'Channel::Whatsapp': 'message-circle',
      'Channel::Email': 'mail',
      'Channel::Api': 'globe',
      'Channel::WebWidget': 'globe',
      'Channel::Line': 'message-circle',
      'Channel::Telegram': 'send',
    };
    return map[channelType] || 'inbox';
  }

  function filterItems(items: NavigationItem[]): NavigationItem[] {
    return items.filter(item => {
      // Check permissions
      if (item.permission && !authStore.hasPermission(item.permission)) {
        return false;
      }
      // Check feature flags
      if (
        item.featureFlag &&
        !globalConfig.isFeatureEnabled(item.featureFlag)
      ) {
        return false;
      }

      // Filter children
      if (item.children) {
        const filteredChildren = filterItems(item.children);
        // If it was a folder (has children prop) and now empty, hide it unless it's a link itself
        if (filteredChildren.length === 0 && !item.href) {
          return false;
        }
        // Return a new object with filtered children to avoid mutating
        item.children = filteredChildren;
      }

      return true;
    });
  }

  const mainNavItems: NavigationItem[] = $derived(
    filterItems([
      {
        id: 'inbox',
        label: 'Inbox',
        icon: 'inbox',
        href: `/app/accounts/${accountId}/inbox-view`,
        badge: notificationsStore.unreadCount,
        activeOn: [`/app/accounts/${accountId}/inbox-view`],
      },
      {
        id: 'conversations',
        label: 'Conversations',
        icon: 'message-circle',
        children: [
          {
            id: 'all',
            label: 'All Conversations',
            href: `/app/accounts/${accountId}/conversations`,
            activeOn: [
              `/app/accounts/${accountId}/conversations`,
              `/app/accounts/${accountId}/conversations/`,
            ],
            permission: 'conversation_manage',
          },
          {
            id: 'mentions',
            label: 'Mentions',
            href: `/app/accounts/${accountId}/conversations/mentions`,
            activeOn: [`/app/accounts/${accountId}/conversations/mentions`],
          },
          {
            id: 'unattended',
            label: 'Unattended',
            href: `/app/accounts/${accountId}/conversations/unattended`,
            activeOn: [`/app/accounts/${accountId}/conversations/unattended`],
          },
          {
            id: 'custom-views',
            label: 'Custom Views',
            icon: 'folder',
            children: customViewsStore.conversationViews.map(view => ({
              id: `view-${view.id}`,
              label: view.name,
              href: `/app/accounts/${accountId}/conversations/custom_view/${view.id}`,
              activeOn: [
                `/app/accounts/${accountId}/conversations/custom_view/${view.id}`,
              ],
            })),
          },
          {
            id: 'channels',
            label: 'Inboxes',
            icon: 'inbox',
            children: inboxesStore.sortedInboxes.map(inbox => ({
              id: `inbox-${inbox.id}`,
              label: inbox.name,
              icon: getChannelIcon(inbox.channelType),
              href: `/app/accounts/${accountId}/conversations/inbox/${inbox.id}`,
              activeOn: [
                `/app/accounts/${accountId}/conversations/inbox/${inbox.id}`,
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
            id: 'folders',
            label: 'Labels',
            icon: 'tags',
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
        ],
      },
      {
        id: 'contacts',
        label: 'Contacts',
        icon: 'contact',
        children: [
          {
            id: 'contacts-all',
            label: 'All Contacts',
            href: `/app/accounts/${accountId}/contacts`,
            activeOn: [
              `/app/accounts/${accountId}/contacts`,
              `/app/accounts/${accountId}/contacts/`,
            ],
            permission: 'contact_manage',
          },
          {
            id: 'contacts-active',
            label: 'Active',
            href: `/app/accounts/${accountId}/contacts/active`,
            activeOn: [`/app/accounts/${accountId}/contacts/active`],
            permission: 'contact_manage',
          },

          // Segments (collapsible group only, no parent href — matches Vue parity)
          {
            id: 'contacts-segments',
            label: 'Segments',
            icon: 'users-round',
            children: segmentsStore.allSegments.map(segment => ({
              id: `segment-${segment.id}`,
              label: segment.name,
              href: `/app/accounts/${accountId}/contacts/segments/${segment.id}`,
              activeOn: [
                `/app/accounts/${accountId}/contacts/segments/${segment.id}`,
              ],
            })),
            permission: 'contact_manage',
          },
          // Tagged With (Vue parity: SIDEBAR.TAGGED_WITH)
          {
            id: 'contacts-labels',
            label: 'Tagged With',
            icon: 'tags',
            children: labelsStore.sidebarLabels.map(label => ({
              id: `contact-label-${label.title}`,
              label: label.title,
              icon: 'tag',
              iconColor: label.color,
              href: `/app/accounts/${accountId}/contacts/labels/${encodeURIComponent(label.title)}`,
              activeOn: [
                `/app/accounts/${accountId}/contacts/labels/${encodeURIComponent(label.title)}`,
              ],
            })),
            permission: 'contact_manage',
          },
        ],
      },
      // Companies — separate top-level group matching Vue parity
      {
        id: 'companies',
        label: 'Companies',
        icon: 'building-2',
        children: [
          {
            id: 'companies-all',
            label: 'All Companies',
            href: `/app/accounts/${accountId}/companies`,
            activeOn: [`/app/accounts/${accountId}/companies`],
            permission: 'contact_manage',
          },
        ],
      },
      {
        id: 'reports',
        label: 'Reports',
        icon: 'chart-spline',
        children: [
          // The following items map to the report pages present in Vue
          // Use a safe translation helper to avoid calling the formatter before
          // the initial locale is set (which throws in svelte-i18n).
          {
            id: 'reports-overview',
            label: safeT('SIDEBAR.REPORTS_OVERVIEW', 'Overview'),
            href: `/app/accounts/${accountId}/reports`,
            activeOn: [`/app/accounts/${accountId}/reports`],
            permission: 'report_manage',
          },
          {
            id: 'reports-conversation',
            label: safeT('SIDEBAR.REPORTS_CONVERSATION', 'Conversation'),
            href: `/app/accounts/${accountId}/reports/conversation`,
            activeOn: [`/app/accounts/${accountId}/reports/conversation`],
            permission: 'report_manage',
          },
          {
            id: 'reports-agent',
            label: safeT('SIDEBAR.REPORTS_AGENT', 'Agent Reports'),
            href: `/app/accounts/${accountId}/reports/agent`,
            activeOn: [`/app/accounts/${accountId}/reports/agent`],
            permission: 'report_manage',
          },
          {
            id: 'reports-label',
            label: safeT('SIDEBAR.REPORTS_LABEL', 'Label Reports'),
            href: `/app/accounts/${accountId}/reports/label`,
            activeOn: [`/app/accounts/${accountId}/reports/label`],
            permission: 'report_manage',
          },
          {
            id: 'reports-inbox',
            label: safeT('SIDEBAR.REPORTS_INBOX', 'Inbox Reports'),
            href: `/app/accounts/${accountId}/reports/inbox`,
            activeOn: [`/app/accounts/${accountId}/reports/inbox`],
            permission: 'report_manage',
          },
          {
            id: 'reports-team',
            label: safeT('SIDEBAR.REPORTS_TEAM', 'Team Reports'),
            href: `/app/accounts/${accountId}/reports/team`,
            activeOn: [`/app/accounts/${accountId}/reports/team`],
            permission: 'report_manage',
          },
          {
            id: 'reports-csat',
            label: safeT('SIDEBAR.CSAT', 'CSAT'),
            href: `/app/accounts/${accountId}/reports/csat`,
            activeOn: [`/app/accounts/${accountId}/reports/csat`],
            permission: 'report_manage',
          },
          {
            id: 'reports-sla',
            label: safeT('SIDEBAR.REPORTS_SLA', 'SLA'),
            href: `/app/accounts/${accountId}/reports/sla`,
            activeOn: [`/app/accounts/${accountId}/reports/sla`],
            permission: 'report_manage',
          },
          {
            id: 'reports-bot',
            label: safeT('SIDEBAR.REPORTS_BOT', 'Bot'),
            href: `/app/accounts/${accountId}/reports/bot`,
            activeOn: [`/app/accounts/${accountId}/reports/bot`],
            permission: 'report_manage',
          },
        ],
      },
      {
        id: 'campaigns',
        label: 'Campaigns',
        icon: 'megaphone',
        children: [
          {
            id: 'campaigns-livechat',
            label: 'Live chat',
            href: `/app/accounts/${accountId}/campaigns/livechat`,
            activeOn: [`/app/accounts/${accountId}/campaigns/livechat`],
            permission: 'administrator',
          },
          {
            id: 'campaigns-sms',
            label: 'SMS',
            href: `/app/accounts/${accountId}/campaigns/sms`,
            activeOn: [`/app/accounts/${accountId}/campaigns/sms`],
            permission: 'administrator',
          },
          {
            id: 'campaigns-whatsapp',
            label: 'WhatsApp',
            href: `/app/accounts/${accountId}/campaigns/whatsapp`,
            activeOn: [`/app/accounts/${accountId}/campaigns/whatsapp`],
            permission: 'administrator',
          },
        ],
      },
      {
        id: 'portals',
        label: 'Help Center',
        icon: 'library-big',
        children: [
          {
            id: 'portal-articles',
            label: 'Articles',
            href: `/app/accounts/${accountId}/portals/articles`,
            activeOn: [`/app/accounts/${accountId}/portals/articles`],
            permission: 'knowledge_base_manage',
          },
          {
            id: 'portal-categories',
            label: 'Categories',
            href: `/app/accounts/${accountId}/portals/categories`,
            activeOn: [`/app/accounts/${accountId}/portals/categories`],
            permission: 'knowledge_base_manage',
          },
          {
            id: 'portal-locales',
            label: 'Locales',
            href: `/app/accounts/${accountId}/portals/locales`,
            activeOn: [`/app/accounts/${accountId}/portals/locales`],
            permission: 'knowledge_base_manage',
          },
          {
            id: 'portal-settings',
            label: 'Settings',
            href: `/app/accounts/${accountId}/portals/settings`,
            activeOn: [`/app/accounts/${accountId}/portals/settings`],
            permission: 'knowledge_base_manage',
          },
        ],
      },
      {
        id: 'settings',
        label: 'Settings',
        icon: 'bolt',
        children: [
          {
            id: 'settings-account',
            label: 'Account Settings',
            icon: 'briefcase',
            href: `/app/accounts/${accountId}/settings/account`,
            activeOn: [`/app/accounts/${accountId}/settings/account`],
            permission: 'administrator',
          },
          {
            id: 'settings-agents',
            label: 'Agents',
            icon: 'square-user',
            href: `/app/accounts/${accountId}/settings/agents`,
            activeOn: [`/app/accounts/${accountId}/settings/agents`],
            permission: 'administrator',
          },
          {
            id: 'settings-teams',
            label: 'Teams',
            icon: 'users',
            href: `/app/accounts/${accountId}/settings/teams`,
            activeOn: [`/app/accounts/${accountId}/settings/teams`],
            permission: 'administrator',
          },
          {
            id: 'settings-assignment',
            label: 'Agent Assignment',
            icon: 'user-cog',
            href: `/app/accounts/${accountId}/settings/assignment-policy`,
            activeOn: [`/app/accounts/${accountId}/settings/assignment-policy`],
            permission: 'administrator',
          },
          {
            id: 'settings-inboxes',
            label: 'Inboxes',
            icon: 'inbox',
            href: `/app/accounts/${accountId}/settings/inboxes`,
            activeOn: [`/app/accounts/${accountId}/settings/inboxes`],
            permission: 'administrator',
          },
          {
            id: 'settings-labels',
            label: 'Labels',
            icon: 'tags',
            href: `/app/accounts/${accountId}/settings/labels`,
            activeOn: [`/app/accounts/${accountId}/settings/labels`],
            permission: 'administrator',
          },
          {
            id: 'settings-attributes',
            label: 'Custom Attributes',
            icon: 'code',
            href: `/app/accounts/${accountId}/settings/attributes`,
            activeOn: [`/app/accounts/${accountId}/settings/attributes`],
            permission: 'administrator',
          },
          {
            id: 'settings-automation',
            label: 'Automation',
            icon: 'workflow',
            href: `/app/accounts/${accountId}/settings/automation`,
            activeOn: [`/app/accounts/${accountId}/settings/automation`],
            permission: 'administrator',
          },
          {
            id: 'settings-agent-bots',
            label: 'Agent Bots',
            icon: 'bot',
            href: `/app/accounts/${accountId}/settings/agent-bots`,
            activeOn: [`/app/accounts/${accountId}/settings/agent-bots`],
            permission: 'administrator',
          },
          {
            id: 'settings-macros',
            label: 'Macros',
            icon: 'toy-brick',
            href: `/app/accounts/${accountId}/settings/macros`,
            activeOn: [`/app/accounts/${accountId}/settings/macros`],
            permission: 'administrator',
          },
          {
            id: 'settings-canned',
            label: 'Canned Responses',
            icon: 'message-square-quote',
            href: `/app/accounts/${accountId}/settings/canned`,
            activeOn: [`/app/accounts/${accountId}/settings/canned`],
            permission: 'administrator',
          },
          {
            id: 'settings-integrations',
            label: 'Integrations',
            icon: 'blocks',
            href: `/app/accounts/${accountId}/settings/integrations`,
            activeOn: [`/app/accounts/${accountId}/settings/integrations`],
            permission: 'administrator',
          },
          {
            id: 'settings-audit',
            label: 'Audit Logs',
            icon: 'briefcase',
            href: `/app/accounts/${accountId}/settings/audit-logs`,
            activeOn: [`/app/accounts/${accountId}/settings/audit-logs`],
            permission: 'administrator',
          },
          {
            id: 'settings-roles',
            label: 'Custom Roles',
            icon: 'shield-plus',
            href: `/app/accounts/${accountId}/settings/custom-roles`,
            activeOn: [`/app/accounts/${accountId}/settings/custom-roles`],
            permission: 'administrator',
          },
          {
            id: 'settings-sla',
            label: 'SLA',
            icon: 'clock-alert',
            href: `/app/accounts/${accountId}/settings/sla`,
            activeOn: [`/app/accounts/${accountId}/settings/sla`],
            permission: 'administrator',
          },
          {
            id: 'settings-security',
            label: 'Security',
            icon: 'shield',
            href: `/app/accounts/${accountId}/settings/security`,
            activeOn: [`/app/accounts/${accountId}/settings/security`],
            permission: 'administrator',
          },
          {
            id: 'settings-billing',
            label: 'Billing',
            icon: 'credit-card',
            href: `/app/accounts/${accountId}/settings/billing`,
            activeOn: [`/app/accounts/${accountId}/settings/billing`],
            permission: 'administrator',
          },
        ],
      },
    ])
  );

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
        <div
          class="flex gap-2 items-center px-2 min-w-0 group-data-[collapsible=icon]:hidden"
        >
          <div class="grid flex-shrink-0 place-content-center size-6">
            <Logo class="h-4 w-4" aria-label="Chatwoot logo" />
          </div>
          <div class="flex-shrink-0 w-px h-3 bg-border"></div>
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
    <SidebarGroup section={{ id: 'main', items: mainNavItems }} />
  </Sidebar.Content>
  <Sidebar.Footer>
    <SidebarProfileMenu />
  </Sidebar.Footer>
  <Sidebar.Rail />
</Sidebar.Root>
