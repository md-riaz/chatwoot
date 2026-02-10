<script lang="ts">
  import * as Avatar from '$lib/components/ui/avatar';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  import { ChevronDown } from '@lucide/svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { navigate } from '$lib/routing/navigation';
  import type { HTMLAttributes } from 'svelte/elements';

  let {
    class: className = '',
    ...restProps
  }: HTMLAttributes<HTMLDivElement> = $props();

  const currentUser = $derived(authStore.currentUser);
  const currentAccount = $derived(authStore.currentAccount);
  const isLoggedIn = $derived(authStore.isLoggedIn);

  function handleAccountSwitch(id: number) {
    authStore.setActiveAccount(id);
    navigate(`/app/accounts/${id}`);
  }
</script>

{#if isLoggedIn && currentAccount}
  <div class={className} {...restProps}>
    <DropdownMenu.Root>
      <DropdownMenu.Trigger>
        {#snippet child({ props })}
          <button
            type="button"
            class="flex flex-1 items-center gap-2 min-w-0 rounded-md px-1 py-1 text-left text-sm hover:bg-muted"
            {...props}
          >
            <div class="grid flex-1 text-start leading-tight">
              <span class="truncate font-medium">{currentAccount.name}</span>
              <span class="truncate text-xs text-muted-foreground">
                {currentAccount.role}
              </span>
            </div>
            <ChevronDown class="h-3 w-3 text-muted-foreground" />
          </button>
        {/snippet}
      </DropdownMenu.Trigger>
      <DropdownMenu.Content class="w-64">
        <DropdownMenu.Label>Switch Account</DropdownMenu.Label>
        <DropdownMenu.Separator />
        {#each (currentUser.accounts || []) as account}
          <DropdownMenu.Item
            class="gap-2"
            onclick={() => handleAccountSwitch(account.id)}
          >
            <Avatar.Root class="h-6 w-6 rounded-lg">
              <Avatar.Image src={account.avatarUrl || ''} alt={account.name} />
              <Avatar.Fallback class="rounded-lg">
                {account.name.charAt(0).toUpperCase()}
              </Avatar.Fallback>
            </Avatar.Root>
            <div class="flex flex-col">
              <span>{account.name}</span>
              <span class="text-xs text-muted-foreground">
                {account.role}
              </span>
            </div>
            {#if account.id === currentAccount.id}
              <span class="ml-auto text-xs">✓</span>
            {/if}
          </DropdownMenu.Item>
        {/each}
      </DropdownMenu.Content>
    </DropdownMenu.Root>
  </div>
{/if}

