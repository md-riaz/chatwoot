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
  import { page } from '$app/stores';
  import { navigate, isRouteActive } from '$lib/routing/navigation';
  import type { NavigationItem, SidebarSection } from './types';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import type { ComponentProps } from "svelte";

  let { ...restProps }: ComponentProps<typeof Sidebar.Root> = $props();

  // Get current accountId from route params
  const accountId = $derived($page.params.accountId);

  // Navigation sections
  // TODO: Implement permission-based filtering for menu items (RBAC)
  const navigationSections: SidebarSection[] = $derived([
    {
      id: 'main',
      items: [
        {
          id: 'home',
          label: 'Home',
          icon: 'home',
          href: accountId ? `/app/accounts/${accountId}` : '/app',
        },
        {
          id: 'inbox',
          label: 'Inbox',
          icon: 'inbox',
          href: accountId ? `/app/accounts/${accountId}/inbox` : '/app/inbox',
        },
      ],
    },
    {
      id: 'conversations',
      title: 'Conversations',
      items: [
        { id: 'all', label: 'All Conversations', icon: 'message-square', href: accountId ? `/app/accounts/${accountId}/conversations` : '/app/conversations' },
        { id: 'mentions', label: 'Mentions', icon: 'message-square', href: accountId ? `/app/accounts/${accountId}/conversations/mentions` : '/app/conversations/mentions' },
        { id: 'unattended', label: 'Unattended', icon: 'message-square', href: accountId ? `/app/accounts/${accountId}/conversations/unattended` : '/app/conversations/unattended' },
        { id: 'folders', label: 'Folders', icon: 'folder', href: accountId ? `/app/accounts/${accountId}/conversations/folders` : '/app/conversations/folders' },
        // Teams (dynamic)
        ...teamsStore.myTeams.map(team => ({ id: `team-${team.id}`, label: `Team: ${team.name}`, icon: 'users', href: accountId ? `/app/accounts/${accountId}/conversations/team/${team.id}` : `/app/conversations/team/${team.id}` })),
        // Channels (inboxes)
        ...inboxesStore.sortedInboxes.map(inbox => ({ id: `inbox-${inbox.id}`, label: inbox.name, icon: 'inbox', href: accountId ? `/app/accounts/${accountId}/conversations/inbox/${inbox.id}` : `/app/conversations/inbox/${inbox.id}` })),
        // Labels (dynamic)
        ...labelsStore.sidebarLabels.map(label => ({ id: `label-${label.id}`, label: label.title, icon: 'tags', href: accountId ? `/app/accounts/${accountId}/conversations/label/${encodeURIComponent(label.title)}` : `/app/conversations/label/${encodeURIComponent(label.title)}` })),
      ],
    },
    {
      id: 'contacts',
      title: 'Contacts',
      items: [
        { id: 'contacts-all', label: 'All Contacts', icon: 'users', href: accountId ? `/app/accounts/${accountId}/contacts` : '/app/contacts' },
        { id: 'contacts-active', label: 'Active', icon: 'users', href: accountId ? `/app/accounts/${accountId}/contacts/active` : '/app/contacts/active' },
      ],
    },
    {
      id: 'companies',
      title: 'Companies',
      items: [
        { id: 'companies-all', label: 'All Companies', icon: 'building-2', href: accountId ? `/app/accounts/${accountId}/companies` : '/app/companies' },
      ],
    },
    {
      id: 'reports',
      title: 'Reports',
      items: [
        { id: 'reports-overview', label: 'Overview', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/overview` : '/app/reports/overview' },
        { id: 'reports-conversation', label: 'Conversation', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/conversation` : '/app/reports/conversation' },
        { id: 'reports-agent', label: 'Agent', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/agent` : '/app/reports/agent' },
        { id: 'reports-label', label: 'Label', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/label` : '/app/reports/label' },
        { id: 'reports-inbox', label: 'Inbox', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/inbox` : '/app/reports/inbox' },
        { id: 'reports-team', label: 'Team', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/team` : '/app/reports/team' },
        { id: 'reports-csat', label: 'CSAT', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/csat` : '/app/reports/csat' },
        { id: 'reports-sla', label: 'SLA', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/sla` : '/app/reports/sla' },
        { id: 'reports-bot', label: 'Bot', icon: 'bar-chart-3', href: accountId ? `/app/accounts/${accountId}/reports/bot` : '/app/reports/bot' },
      ],
    },
    {
      id: 'campaigns',
      title: 'Campaigns',
      items: [
        { id: 'campaigns-livechat', label: 'Live chat', icon: 'megaphone', href: accountId ? `/app/accounts/${accountId}/campaigns/livechat` : '/app/campaigns/livechat' },
        { id: 'campaigns-sms', label: 'SMS', icon: 'megaphone', href: accountId ? `/app/accounts/${accountId}/campaigns/sms` : '/app/campaigns/sms' },
        { id: 'campaigns-whatsapp', label: 'WhatsApp', icon: 'megaphone', href: accountId ? `/app/accounts/${accountId}/campaigns/whatsapp` : '/app/campaigns/whatsapp' },
      ],
    },
    {
      id: 'portals',
      title: 'Help Center',
      items: [
        { id: 'portal-articles', label: 'Articles', icon: 'library', href: accountId ? `/app/accounts/${accountId}/portals/articles` : '/app/portals/articles' },
        { id: 'portal-categories', label: 'Categories', icon: 'library', href: accountId ? `/app/accounts/${accountId}/portals/categories` : '/app/portals/categories' },
        { id: 'portal-locales', label: 'Locales', icon: 'library', href: accountId ? `/app/accounts/${accountId}/portals/locales` : '/app/portals/locales' },
        { id: 'portal-settings', label: 'Settings', icon: 'library', href: accountId ? `/app/accounts/${accountId}/portals/settings` : '/app/portals/settings' },
      ],
    },
    {
      id: 'settings',
      title: 'Settings',
      items: [
        { id: 'settings-account', label: 'Account Settings', icon: 'briefcase', href: accountId ? `/app/accounts/${accountId}/settings/account` : '/app/settings/account' },
        { id: 'settings-agents', label: 'Agents', icon: 'square-user', href: accountId ? `/app/accounts/${accountId}/settings/agents` : '/app/settings/agents' },
        { id: 'settings-teams', label: 'Teams', icon: 'users', href: accountId ? `/app/accounts/${accountId}/settings/teams` : '/app/settings/teams' },
        { id: 'settings-assignment', label: 'Agent Assignment', icon: 'user-cog', href: accountId ? `/app/accounts/${accountId}/settings/assignment` : '/app/settings/assignment' },
        { id: 'settings-inboxes', label: 'Inboxes', icon: 'inbox', href: accountId ? `/app/accounts/${accountId}/settings/inboxes` : '/app/settings/inboxes' },
        { id: 'settings-labels', label: 'Labels', icon: 'tags', href: accountId ? `/app/accounts/${accountId}/settings/labels` : '/app/settings/labels' },
        { id: 'settings-attributes', label: 'Custom Attributes', icon: 'code', href: accountId ? `/app/accounts/${accountId}/settings/attributes` : '/app/settings/attributes' },
        { id: 'settings-automation', label: 'Automation', icon: 'workflow', href: accountId ? `/app/accounts/${accountId}/settings/automation` : '/app/settings/automation' },
        { id: 'settings-agent-bots', label: 'Agent Bots', icon: 'bot', href: accountId ? `/app/accounts/${accountId}/settings/agent-bots` : '/app/settings/agent-bots' },
        { id: 'settings-macros', label: 'Macros', icon: 'toy-brick', href: accountId ? `/app/accounts/${accountId}/settings/macros` : '/app/settings/macros' },
        { id: 'settings-canned', label: 'Canned Responses', icon: 'message-square-quote', href: accountId ? `/app/accounts/${accountId}/settings/canned` : '/app/settings/canned' },
        { id: 'settings-integrations', label: 'Integrations', icon: 'blocks', href: accountId ? `/app/accounts/${accountId}/settings/integrations` : '/app/settings/integrations' },
        { id: 'settings-audit', label: 'Audit Logs', icon: 'briefcase', href: accountId ? `/app/accounts/${accountId}/settings/audit` : '/app/settings/audit' },
        { id: 'settings-roles', label: 'Custom Roles', icon: 'shield-plus', href: accountId ? `/app/accounts/${accountId}/settings/roles` : '/app/settings/roles' },
        { id: 'settings-sla', label: 'SLA', icon: 'clock-alert', href: accountId ? `/app/accounts/${accountId}/settings/sla` : '/app/settings/sla' },
        { id: 'settings-security', label: 'Security', icon: 'shield', href: accountId ? `/app/accounts/${accountId}/settings/security` : '/app/settings/security' },
        { id: 'settings-billing', label: 'Billing', icon: 'credit-card', href: accountId ? `/app/accounts/${accountId}/settings/billing` : '/app/settings/billing' },
      ],
    },
  ]);

  let searchQuery = $state('');

  function onSearchKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter' && searchQuery.trim().length) {
      const q = encodeURIComponent(searchQuery.trim());
      navigate(accountId ? `/app/accounts/${accountId}/search?q=${q}` : `/app/search?q=${q}`);
      searchQuery = '';
    }
  }

  function onComposeClick() {
    const url = accountId
      ? `/app/accounts/${accountId}/conversations/new`
      : '/app/conversations/new';
    navigate(url);
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
                  class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 pl-8 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
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
                                                    isActive={isRouteActive(child.href)}
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
                                isActive={isRouteActive(item.href)}
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
