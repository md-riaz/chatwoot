<script lang="ts">
  import * as Sidebar from "$lib/components/ui/sidebar/index.js";
  import * as Collapsible from "$lib/components/ui/collapsible/index.js";
  import { Badge } from "$lib/components/ui/badge";
  import { ChevronRight } from "lucide-svelte";
  import type { NavigationItem } from "./types";
  import SidebarIcon from "./SidebarIcon.svelte";
  import { isRouteActive, isAnyRouteActive } from "$lib/routing/navigation";

  let { item, sub = false }: { item: NavigationItem; sub?: boolean } = $props();

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

  const isActive = $derived(isNavigationItemActive(item));
</script>

{#if item.children && item.children.length > 0}
  <Collapsible.Root open={isActive} class="group/collapsible">
    {#snippet child({ props })}
      {#if sub}
        <Sidebar.MenuSubItem {...props}>
          {#if item.href}
             <Sidebar.MenuSubButton isActive={isActive} href={item.href}>
                {#if item.icon}
                  <SidebarIcon name={item.icon} />
                {/if}
                <span>{item.label}</span>
             </Sidebar.MenuSubButton>
          {:else}
             <!-- Non-clickable label if no href, but acts as trigger? -->
             <!-- Usually nested collapsible folders don't have their own href, they just toggle. 
                  But if they do, we might need a split button. 
                  For now, assume if children exist, it's a folder toggle. -->
             <Collapsible.Trigger>
                {#snippet child({ props: triggerProps })}
                   <Sidebar.MenuSubButton {...triggerProps}>
                       {#if item.icon}
                         <SidebarIcon name={item.icon} />
                       {/if}
                       <span>{item.label}</span>
                       <ChevronRight class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
                   </Sidebar.MenuSubButton>
                {/snippet}
             </Collapsible.Trigger>
          {/if}
          
          <Collapsible.Content>
            <Sidebar.MenuSub>
              {#each item.children as child (child.id)}
                <svelte:self item={child} sub={true} />
              {/each}
            </Sidebar.MenuSub>
          </Collapsible.Content>
        </Sidebar.MenuSubItem>
      {:else}
        <Sidebar.MenuItem {...props}>
            <Sidebar.MenuButton isActive={isActive} tooltipContent={item.label}>
              {#snippet child({ props: btnProps })}
                 {#if item.href}
                    <a href={item.href} {...btnProps}>
                      <SidebarIcon name={item.icon} />
                      <span>{item.label}</span>
                    </a>
                 {:else}
                    <div {...btnProps} class="cursor-pointer">
                      <SidebarIcon name={item.icon} />
                      <span>{item.label}</span>
                    </div>
                 {/if}
              {/snippet}
            </Sidebar.MenuButton>
            
            <Collapsible.Trigger>
              {#snippet child({ props: triggerProps })}
                <Sidebar.MenuAction
                  {...triggerProps}
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
                  <svelte:self item={child} sub={true} />
                {/each}
              </Sidebar.MenuSub>
            </Collapsible.Content>
        </Sidebar.MenuItem>
      {/if}
    {/snippet}
  </Collapsible.Root>
{:else}
  {#if sub}
    <Sidebar.MenuSubItem>
      <Sidebar.MenuSubButton isActive={isActive} href={item.href}>
        {#if item.icon}
          <SidebarIcon name={item.icon} />
        {/if}
        <span>{item.label}</span>
        {#if item.badge && item.badge > 0}
           <Badge variant="secondary" class="ml-auto text-xs px-1.5 min-w-5 h-5 flex items-center justify-center">
             {item.badge > 99 ? "99+" : item.badge}
           </Badge>
        {/if}
      </Sidebar.MenuSubButton>
    </Sidebar.MenuSubItem>
  {:else}
    <Sidebar.MenuItem>
      <Sidebar.MenuButton isActive={isActive} tooltipContent={item.label}>
         {#snippet child({ props })}
            <a href={item.href} {...props}>
              <SidebarIcon name={item.icon} />
              <span>{item.label}</span>
              {#if item.badge && item.badge > 0}
                <Badge variant="secondary" class="ml-auto text-xs px-1.5 min-w-5 h-5 flex items-center justify-center">
                  {item.badge > 99 ? "99+" : item.badge}
                </Badge>
              {/if}
            </a>
         {/snippet}
      </Sidebar.MenuButton>
    </Sidebar.MenuItem>
  {/if}
{/if}
