<script lang="ts">
  /**
   * AppHeader - Main application header
   * Features: Account switcher, notifications, user menu, mobile menu toggle
   */
  
  import { Menu, Bell, Settings, HelpCircle, ChevronDown } from '@lucide/svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import * as Avatar from '$lib/components/ui/avatar';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { authStore } from '$lib/stores/auth.svelte';
  import { navigate } from '$lib/routing/navigation';
  import { page } from '$app/stores';
  import type { AccountInfo, UserMenuItem } from './types';
  
  interface Props {
    onMobileMenuToggle?: () => void;
  }
  
  let { onMobileMenuToggle }: Props = $props();
  
  // Reactive store access
  const currentUser = $derived(authStore.currentUser);
  const currentAccount = $derived(authStore.currentAccount);
  const isLoggedIn = $derived(authStore.isLoggedIn);
  const accountId = $derived($page.params.accountId);
  
  // Local state
  let notificationCount = $state(0);
  
  // User menu items
  const userMenuItems: UserMenuItem[] = $derived([
    {
      id: 'profile',
      label: 'Profile Settings',
      icon: 'user',
      onClick: () => navigate(accountId ? `/app/accounts/${accountId}/settings/profile` : '/profile'),
    },
    {
      id: 'divider1',
      label: '',
      divider: true,
    },
    {
      id: 'settings',
      label: 'Account Settings',
      icon: 'settings',
      onClick: () => navigate(accountId ? `/app/accounts/${accountId}/settings` : '/settings'),
    },
    {
      id: 'help',
      label: 'Help & Support',
      icon: 'help-circle',
      onClick: () => navigate('/help'),
    },
    {
      id: 'divider2',
      label: '',
      divider: true,
    },
    {
      id: 'logout',
      label: 'Sign Out',
      icon: 'log-out',
      onClick: () => authStore.logout(),
    },
  ]);
  
  function handleAccountSwitch(accountId: number) {
    authStore.setActiveAccount(accountId);
    // Navigate to the new account's dashboard
    navigate(`/app/accounts/${accountId}`);
  }
  
  function handleNotificationsClick() {
    if (accountId) {
      navigate(`/app/accounts/${accountId}/notifications`);
    } else {
      navigate('/notifications');
    }
  }
</script>

<header class="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
  <div class="flex h-16 items-center px-4 gap-4">
    <!-- Mobile menu toggle -->
    <Button
      variant="ghost"
      size="icon"
      class="lg:hidden"
      onclick={(e: MouseEvent) => onMobileMenuToggle?.()}
      aria-label="Toggle menu"
    >
      <Menu class="h-5 w-5" />
    </Button>
    
    <!-- Logo / Brand (visible on mobile) -->
    <div class="flex items-center lg:hidden">
      <span class="text-xl font-semibold">ClearLine</span>
    </div>
    
    <!-- Spacer -->
    <div class="flex-1"></div>
    
    {#if isLoggedIn && currentUser}
      <!-- Account Switcher -->
      {#if currentAccount}
        <DropdownMenu.Root>
          <DropdownMenu.Trigger>
            {#snippet child({ props })}
              <Button {...props} variant="ghost" class="gap-2">
                <Avatar.Root class="h-6 w-6">
                  <Avatar.Image src={currentAccount.name} alt={currentAccount.name} />
                  <Avatar.Fallback>{currentAccount.name.charAt(0).toUpperCase()}</Avatar.Fallback>
                </Avatar.Root>
                <span class="hidden md:inline">{currentAccount.name}</span>
                <ChevronDown class="h-4 w-4 opacity-50" />
              </Button>
            {/snippet}
          </DropdownMenu.Trigger>
          <DropdownMenu.Content class="w-56">
            <DropdownMenu.Label>Switch Account</DropdownMenu.Label>
            <DropdownMenu.Separator />
            {#each (currentUser.accounts || []) as account}
              <DropdownMenu.Item
                class="gap-2"
                onclick={(e: MouseEvent) => handleAccountSwitch(account.id)}
              >
                <Avatar.Root class="h-6 w-6">
                  <Avatar.Image src="" alt={account.name} />
                  <Avatar.Fallback>{account.name.charAt(0).toUpperCase()}</Avatar.Fallback>
                </Avatar.Root>
                <div class="flex flex-col">
                  <span>{account.name}</span>
                  <span class="text-xs text-muted-foreground">{account.role}</span>
                </div>
                {#if account.id === currentAccount.id}
                  <span class="ml-auto text-xs">✓</span>
                {/if}
              </DropdownMenu.Item>
            {/each}
          </DropdownMenu.Content>
        </DropdownMenu.Root>
      {/if}
      
      <!-- Notifications -->
      <Button
        variant="ghost"
        size="icon"
        class="relative"
        onclick={(e: MouseEvent) => handleNotificationsClick()}
        aria-label="Notifications"
      >
        <Bell class="h-5 w-5" />
        {#if notificationCount > 0}
          <Badge
            variant="destructive"
            class="absolute -top-1 -right-1 h-5 min-w-5 px-1 text-xs"
          >
            {notificationCount > 99 ? '99+' : notificationCount}
          </Badge>
        {/if}
      </Button>
      
      <!-- Help -->
      <Button
        variant="ghost"
        size="icon"
        onclick={(e: MouseEvent) => navigate('/help')}
        aria-label="Help"
      >
        <HelpCircle class="h-5 w-5" />
      </Button>
      
      <!-- Settings -->
      <Button
        variant="ghost"
        size="icon"
        onclick={(e: MouseEvent) => navigate(accountId ? `/app/accounts/${accountId}/settings` : '/settings')}
        aria-label="Settings"
        class="hidden md:inline-flex"
      >
        <Settings class="h-5 w-5" />
      </Button>
      
      <!-- User Menu -->
      <DropdownMenu.Root>
        <DropdownMenu.Trigger>
          {#snippet child({ props })}
            <Button {...props} variant="ghost" class="gap-2">
              <Avatar.Root class="h-8 w-8">
                <Avatar.Image src={currentUser.avatarUrl || ''} alt={currentUser.name} />
                <Avatar.Fallback>
                  {currentUser.name?.charAt(0).toUpperCase() || 'U'}
                </Avatar.Fallback>
              </Avatar.Root>
              <span class="hidden md:inline">{currentUser.name}</span>
              <ChevronDown class="h-4 w-4 opacity-50 hidden md:inline" />
            </Button>
          {/snippet}
        </DropdownMenu.Trigger>
        <DropdownMenu.Content class="w-56">
          <DropdownMenu.Label>
            <div class="flex flex-col space-y-1">
              <p class="text-sm font-medium">{currentUser.name}</p>
              <p class="text-xs text-muted-foreground">{currentUser.email}</p>
            </div>
          </DropdownMenu.Label>
          <DropdownMenu.Separator />
          {#each userMenuItems as item}
            {#if item.divider}
              <DropdownMenu.Separator />
            {:else}
              <DropdownMenu.Item onclick={() => item.onClick?.()}>
                {item.label}
              </DropdownMenu.Item>
            {/if}
          {/each}
        </DropdownMenu.Content>
      </DropdownMenu.Root>
    {/if}
  </div>
</header>