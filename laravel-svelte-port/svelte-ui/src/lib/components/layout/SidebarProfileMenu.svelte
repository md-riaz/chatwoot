<script lang="ts">
  import * as Sidebar from "$lib/components/ui/sidebar/index.js";
  import * as DropdownMenu from "$lib/components/ui/dropdown-menu";
  import * as Avatar from "$lib/components/ui/avatar";
  import {
    Castle,
    Keyboard,
    LifeBuoy,
    Palette,
    Power,
    UserPen,
    ChevronDown,
  } from "@lucide/svelte";
  import { authStore } from "$lib/stores/auth.svelte";
  import { navigate } from "$lib/routing/navigation";

  const currentUser = $derived(authStore.currentUser);
  const isLoggedIn = $derived(authStore.isLoggedIn);
  const accountId = authStore.currentAccountId;

  function openKeyboardShortcuts() {
    const event = new CustomEvent("open-keyboard-shortcuts");
    window.dispatchEvent(event);
  }

  function openAppearanceSettings() {
    const ninja = document.querySelector("ninja-keys") as any;
    if (ninja?.open) {
      ninja.open({ parent: "appearance_settings" });
    }
  }
</script>

{#if isLoggedIn && currentUser}
  <Sidebar.Menu>
    <Sidebar.MenuItem>
      <DropdownMenu.Root>
        <DropdownMenu.Trigger>
          {#snippet child({ props })}
            <Sidebar.MenuButton
              {...props}
              size="lg"
              class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground group-data-[collapsible=icon]:hidden"
            >
              <Avatar.Root class="size-8 rounded-lg">
                <Avatar.Image src={currentUser.avatarUrl || ""} alt={currentUser.name} />
                <Avatar.Fallback class="rounded-lg">
                  {currentUser.name?.charAt(0).toUpperCase() || "U"}
                </Avatar.Fallback>
              </Avatar.Root>
              <div class="grid flex-1 text-start text-sm leading-tight">
                <span class="truncate font-medium">{currentUser.name}</span>
                <span class="truncate text-xs text-muted-foreground">
                  {currentUser.email}
                </span>
              </div>
              <ChevronDown class="ms-auto h-4 w-4" />
            </Sidebar.MenuButton>
          {/snippet}
        </DropdownMenu.Trigger>
        <DropdownMenu.Content
          class="w-(--bits-dropdown-menu-anchor-width) min-w-56 rounded-lg"
          side="right"
          align="end"
          sideOffset={4}
        >
          <DropdownMenu.Label class="p-0 font-normal">
            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
              <Avatar.Root class="size-8 rounded-lg">
                <Avatar.Image src={currentUser.avatarUrl || ""} alt={currentUser.name} />
                <Avatar.Fallback class="rounded-lg">
                  {currentUser.name?.charAt(0).toUpperCase() || "U"}
                </Avatar.Fallback>
              </Avatar.Root>
              <div class="grid flex-1 text-start text-sm leading-tight">
                <span class="truncate font-medium">{currentUser.name}</span>
                <span class="truncate text-xs text-muted-foreground">
                  {currentUser.email}
                </span>
              </div>
            </div>
          </DropdownMenu.Label>
          <DropdownMenu.Separator />
          <DropdownMenu.Group>
            <DropdownMenu.Item onclick={() => (window as any).$chatwoot?.toggle?.()}>
              <LifeBuoy class="mr-2 h-4 w-4" />
              Contact Support
            </DropdownMenu.Item>
            <DropdownMenu.Item onclick={openKeyboardShortcuts}>
              <Keyboard class="mr-2 h-4 w-4" />
              Keyboard Shortcuts
            </DropdownMenu.Item>
            <DropdownMenu.Item
              onclick={() =>
                navigate(`/app/accounts/${accountId}/settings/profile`)
              }
            >
              <UserPen class="mr-2 h-4 w-4" />
              Profile Settings
            </DropdownMenu.Item>
            <DropdownMenu.Item onclick={openAppearanceSettings}>
              <Palette class="mr-2 h-4 w-4" />
              Appearance
            </DropdownMenu.Item>
            {#if currentUser.type === "SuperAdmin"}
              <DropdownMenu.Item
                onclick={() => window.open("/super_admin", "_blank")}
              >
                <Castle class="mr-2 h-4 w-4" />
                Super Admin Console
              </DropdownMenu.Item>
            {/if}
          </DropdownMenu.Group>
          <DropdownMenu.Separator />
          <DropdownMenu.Item onclick={() => authStore.logout()}>
            <Power class="mr-2 h-4 w-4" />
            Logout
          </DropdownMenu.Item>
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    </Sidebar.MenuItem>
  </Sidebar.Menu>
{/if}
