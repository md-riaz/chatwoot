<script lang="ts">
  import * as Sidebar from "$lib/components/ui/sidebar/index.js";
  import * as Collapsible from "$lib/components/ui/collapsible/index.js";
  import {
    Home,
    MessageSquare,
    Users,
    Inbox,
    Tags,
    BarChart3,
    Settings,
    ChevronRight,
    Search,
    Plus,
    Building2,
    Megaphone,
    Library,
    Briefcase,
    SquareUser,
    UserCog,
    Code,
    Workflow,
    Bot,
    ToyBrick,
    MessageSquareQuote,
    Blocks,
    ShieldPlus,
    ClockAlert,
    Shield,
    CreditCard,
    Folder
  } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { navigate, isRouteActive, isAnyRouteActive } from '$lib/routing/navigation';
  import type { NavigationItem, SidebarSection } from './types';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import type { ComponentProps } from "svelte";
  import { authStore } from '$lib/stores/auth.svelte';

  let { ...restProps }: ComponentProps<typeof Sidebar.Root> = $props();

  const accountId = authStore.currentAccountId;

  const navigationSections: SidebarSection[] = $derived([
    {
      id: 'main',
      items: [
        {
          id: 'inbox',
          label: 'Inbox',
          icon: 'inbox',
          href: `/app/accounts/${accountId}/inbox`,
          activeOn: [
            `/app/accounts/${accountId}/inbox`,
            `/app/accounts/${accountId}/inbox/`,
            `/app/accounts/${accountId}/conversations`,
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

  let searchQuery = $state('');

  function onSearchKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && searchQuery.trim().length) {
      const q = encodeURIComponent(searchQuery.trim());
      navigate(`/app/accounts/${accountId}/search?q=${q}`);
      searchQuery = '';
    }
  }

  function onComposeClick() {
    navigate(`/app/accounts/${accountId}/conversations/new`);
  }

  // Icon component mapper
  const icons: Record<string, any> = {
      'home': Home,
      'message-square': MessageSquare,
      'users': Users,
      'inbox': Inbox,
      'tags': Tags,
      'bar-chart-3': BarChart3,
      'settings': Settings,
      'building-2': Building2,
      'megaphone': Megaphone,
      'library': Library,
      'briefcase': Briefcase,
      'square-user': SquareUser,
      'user-cog': UserCog,
      'code': Code,
      'workflow': Workflow,
      'bot': Bot,
      'toy-brick': ToyBrick,
      'message-square-quote': MessageSquareQuote,
      'blocks': Blocks,
      'shield-plus': ShieldPlus,
      'clock-alert': ClockAlert,
      'shield': Shield,
      'credit-card': CreditCard,
      'folder': Folder
  };

  function getIconComponent(iconName: string) {
    return icons[iconName] || Home;
  }
</script>

<Sidebar.Root collapsible="icon" {...restProps}>
  <Sidebar.Header>
     <div class="flex flex-col gap-2 p-2">
        <div class="flex items-center gap-2 px-2">
            <span class="text-lg font-semibold truncate">ClearLine</span>
        </div>
        <div class="flex items-center gap-1">
             <div class="relative flex-1 group-data-[collapsible=icon]:hidden">
                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground pointer-events-none" />
                <input
                  class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 pl-8 text-sm shadow-xs transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-hidden focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                  placeholder="Search..."
                  bind:value={searchQuery}
                  onkeydown={onSearchKeydown}
                />
             </div>
             <Button variant="ghost" size="icon" onclick={onComposeClick} class="h-9 w-9 group-data-[collapsible=icon]:hidden">
                <Plus class="h-4 w-4" />
             </Button>
        </div>
     </div>
  </Sidebar.Header>
  <Sidebar.Content>
    {#each navigationSections as section}
      <Sidebar.Group>
        {#if section.title}
            <Sidebar.GroupLabel>{section.title}</Sidebar.GroupLabel>
        {/if}
        <Sidebar.GroupContent>
            <Sidebar.Menu>
                {#each section.items as item}
                    {@const Icon = getIconComponent(item.icon || 'home')}
                    {#if item.children && item.children.length > 0}
                         <Collapsible.Root class="group/collapsible">
                             <Sidebar.MenuItem>
                                 <Collapsible.Trigger>
                                     {#snippet child({ props })}
                                        <Sidebar.MenuButton {...props}>
                                            <Icon />
                                            <span>{item.label}</span>
                                            <ChevronRight class="ml-auto transition-transform group-data-[state=open]/collapsible:rotate-90" />
                                        </Sidebar.MenuButton>
                                     {/snippet}
                                 </Collapsible.Trigger>
                                 <Collapsible.Content>
                                     <Sidebar.MenuSub>
                                         {#each item.children as child}
                                            <Sidebar.MenuSubItem>
                                                <Sidebar.MenuSubButton
                                isActive={child.activeOn ? isAnyRouteActive(child.activeOn) : isRouteActive(child.href)}
                                                    onclick={() => navigate(child.href)}
                                                >
                                                    <span>{child.label}</span>
                                                </Sidebar.MenuSubButton>
                                            </Sidebar.MenuSubItem>
                                         {/each}
                                     </Sidebar.MenuSub>
                                 </Collapsible.Content>
                             </Sidebar.MenuItem>
                         </Collapsible.Root>
                    {:else}
                        <Sidebar.MenuItem>
                            <Sidebar.MenuButton
                                isActive={item.activeOn ? isAnyRouteActive(item.activeOn) : isRouteActive(item.href)}
                                onclick={() => navigate(item.href)}
                            >
                                <Icon />
                                <span>{item.label}</span>
                                {#if item.badge && item.badge > 0}
                                    <Badge variant="secondary" class="ml-auto text-xs px-1.5 min-w-5 h-5 flex items-center justify-center">
                                        {item.badge > 99 ? '99+' : item.badge}
                                    </Badge>
                                {/if}
                            </Sidebar.MenuButton>
                        </Sidebar.MenuItem>
                    {/if}
                {/each}
            </Sidebar.Menu>
        </Sidebar.GroupContent>
      </Sidebar.Group>
    {/each}
  </Sidebar.Content>
  <Sidebar.Footer>
      <div class="p-4 text-xs text-center text-muted-foreground group-data-[collapsible=icon]:hidden">
        © 2026 ClearLine
      </div>
  </Sidebar.Footer>
  <Sidebar.Rail />
</Sidebar.Root>
