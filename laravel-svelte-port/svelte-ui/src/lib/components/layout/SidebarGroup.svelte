<script lang="ts">
  import * as Sidebar from "$lib/components/ui/sidebar/index.js";
  import * as Collapsible from "$lib/components/ui/collapsible/index.js";
  import { Badge } from "$lib/components/ui/badge";
  import {
    Home,
    MessageSquare,
    Users,
    Inbox,
    Tags,
    BarChart3,
    Settings,
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
    Folder,
    ChevronRight
  } from "@lucide/svelte";
  import type { SidebarSection, NavigationItem } from "./types";
  import { isRouteActive, isAnyRouteActive } from "$lib/routing/navigation";

  interface Props {
    section: SidebarSection;
  }

  let { section }: Props = $props();

  const icons: Record<string, any> = {
    home: Home,
    "message-square": MessageSquare,
    users: Users,
    inbox: Inbox,
    tags: Tags,
    "bar-chart-3": BarChart3,
    settings: Settings,
    "building-2": Building2,
    megaphone: Megaphone,
    library: Library,
    briefcase: Briefcase,
    "square-user": SquareUser,
    "user-cog": UserCog,
    code: Code,
    workflow: Workflow,
    bot: Bot,
    "toy-brick": ToyBrick,
    "message-square-quote": MessageSquareQuote,
    blocks: Blocks,
    "shield-plus": ShieldPlus,
    "clock-alert": ClockAlert,
    shield: Shield,
    "credit-card": CreditCard,
    folder: Folder
  };

  function getIconComponent(iconName: string | undefined) {
    if (!iconName) return Home;
    return icons[iconName] || Home;
  }

  function isNavigationItemActive(item: NavigationItem): boolean {
    if (item.activeOn && item.activeOn.length > 0) {
      return isAnyRouteActive(item.activeOn);
    }
    if (item.href) {
      return isRouteActive(item.href);
    }
    if (item.children && item.children.length > 0) {
      return item.children.some(child => isNavigationItemActive(child));
    }
    return false;
  }
</script>

<Sidebar.Group>
  {#if section.title}
    <Sidebar.GroupLabel>{section.title}</Sidebar.GroupLabel>
  {/if}
  <Sidebar.GroupContent>
    <Sidebar.Menu>
      {#each section.items as item (item.id)}
        {@const Icon = getIconComponent(item.icon)}
        {#if item.children && item.children.length > 0}
          <Collapsible.Root open={isNavigationItemActive(item)}>
            {#snippet child({ props })}
              <Sidebar.MenuItem {...props}>
                <Sidebar.MenuButton
                  isActive={isNavigationItemActive(item)}
                  tooltipContent={item.label}
                >
                  {#snippet child({ props })}
                    <a href={item.href || "#"} {...props}>
                      <Icon />
                      <span>{item.label}</span>
                    </a>
                  {/snippet}
                </Sidebar.MenuButton>
                <Collapsible.Trigger>
                  {#snippet child({ props })}
                    <Sidebar.MenuAction
                      {...props}
                      class="data-[state=open]:rotate-90"
                    >
                      <ChevronRight />
                      <span class="sr-only">Toggle</span>
                    </Sidebar.MenuAction>
                  {/snippet}
                </Collapsible.Trigger>
                <Collapsible.Content>
                  <Sidebar.MenuSub>
                    {#each item.children as child (child.id)}
                      <Sidebar.MenuSubItem>
                        <Sidebar.MenuSubButton
                          href={child.href}
                          isActive={isNavigationItemActive(child)}
                        >
                          <span>{child.label}</span>
                        </Sidebar.MenuSubButton>
                      </Sidebar.MenuSubItem>
                    {/each}
                  </Sidebar.MenuSub>
                </Collapsible.Content>
              </Sidebar.MenuItem>
            {/snippet}
          </Collapsible.Root>
        {:else}
          <Sidebar.MenuItem>
            <Sidebar.MenuButton
              isActive={isNavigationItemActive(item)}
              tooltipContent={item.label}
            >
              {#snippet child({ props })}
                <a href={item.href} {...props}>
                  <Icon />
                  <span>{item.label}</span>
                  {#if item.badge && item.badge > 0}
                    <Badge
                      variant="secondary"
                      class="ml-auto text-xs px-1.5 min-w-5 h-5 flex items-center justify-center"
                    >
                      {item.badge > 99 ? "99+" : item.badge}
                    </Badge>
                  {/if}
                </a>
              {/snippet}
            </Sidebar.MenuButton>
          </Sidebar.MenuItem>
        {/if}
      {/each}
    </Sidebar.Menu>
  </Sidebar.GroupContent>
</Sidebar.Group>

