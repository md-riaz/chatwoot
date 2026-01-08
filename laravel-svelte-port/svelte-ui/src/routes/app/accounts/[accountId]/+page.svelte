<script lang="ts">
  /**
   * App Home Page
   * Dashboard home page showing overview with real data
   */
  
  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { authStore } from '$lib/stores/auth.svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import { contactsStore } from '$lib/stores/contacts.svelte';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { MessageSquare, Users, Clock, CheckCircle, AlertCircle, TrendingUp } from '@lucide/svelte';
  import { goto } from '$app/navigation';
  
  const currentUser = $derived(authStore.currentUser);
  const currentAccount = $derived(authStore.currentAccount);
  const accountId = $derived($page.params.accountId);
  
  // Reactive store access
  const conversations = $derived(conversationsStore.allConversations);
  const contacts = $derived(contactsStore.allContacts);
  const isLoading = $derived(conversationsStore.isLoading);
  
  // Derived metrics
  const totalConversations = $derived(conversations.length);
  const openConversations = $derived(
    conversations.filter(c => c.status === 'open').length
  );
  const pendingConversations = $derived(
    conversations.filter(c => c.status === 'pending').length
  );
  const resolvedConversations = $derived(
    conversations.filter(c => c.status === 'resolved').length
  );
  const unassignedConversations = $derived(
    conversations.filter(c => !c.meta?.assignee).length
  );
  const totalContacts = $derived(contacts.length);
  
  const stats = $derived([
    {
      title: 'Open Conversations',
      value: openConversations,
      icon: MessageSquare,
      color: 'text-blue-600',
      bgColor: 'bg-blue-100 dark:bg-blue-900/20',
      href: `/app/accounts/${accountId}/conversations`,
      description: openConversations === 0 ? 'All caught up!' : 'Need attention',
    },
    {
      title: 'Unassigned',
      value: unassignedConversations,
      icon: AlertCircle,
      color: 'text-yellow-600',
      bgColor: 'bg-yellow-100 dark:bg-yellow-900/20',
      href: `/app/accounts/${accountId}/conversations`,
      description: unassignedConversations === 0 ? 'Nothing pending' : 'Assign to agents',
    },
    {
      title: 'Resolved',
      value: resolvedConversations,
      icon: CheckCircle,
      color: 'text-green-600',
      bgColor: 'bg-green-100 dark:bg-green-900/20',
      href: `/app/accounts/${accountId}/conversations`,
      description: 'Total resolved',
    },
    {
      title: 'Total Contacts',
      value: totalContacts,
      icon: Users,
      color: 'text-purple-600',
      bgColor: 'bg-purple-100 dark:bg-purple-900/20',
      href: `/app/accounts/${accountId}/contacts`,
      description: totalContacts === 0 ? 'Start adding contacts' : 'In your list',
    },
  ]);
  
  // Load data on mount
  onMount(async () => {
    await Promise.all([
      conversationsStore.fetchConversations(),
      contactsStore.fetchContacts(),
    ]);
  });
</script>

<div class="container py-8">
  <div class="mb-8">
    <h1 class="text-3xl font-bold tracking-tight">
      Welcome back, {currentUser?.name || 'User'}!
    </h1>
    <p class="text-muted-foreground">
      Here's what's happening with your conversations today.
    </p>
  </div>
  
  {#if currentAccount}
    <div class="mb-6">
      <Card.Root class="p-6">
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <h2 class="text-xl font-semibold">{currentAccount.name}</h2>
            <p class="text-sm text-muted-foreground">
              Role: <Badge variant="secondary">{currentAccount.role}</Badge>
            </p>
          </div>
        </div>
      </Card.Root>
    </div>
  {/if}
  
  <!-- Stats Grid -->
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
    {#each stats as stat}
      <Card.Root 
        class="hover:shadow-md transition-shadow cursor-pointer"
        onclick={() => goto(stat.href)}
      >
        <Card.Header class="flex flex-row items-center justify-between pb-2">
          <Card.Title class="text-sm font-medium text-muted-foreground">
            {stat.title}
          </Card.Title>
          <div class="{stat.bgColor} p-2 rounded-lg">
            <svelte:component this={stat.icon} class="h-4 w-4 {stat.color}" />
          </div>
        </Card.Header>
        <Card.Content>
          <div class="text-2xl font-bold">{stat.value}</div>
          <p class="text-xs text-muted-foreground mt-1">{stat.description}</p>
        </Card.Content>
      </Card.Root>
    {/each}
  </div>
  
  <!-- Quick Actions -->
  <div class="grid gap-4 md:grid-cols-2 mb-6">
    <Card.Root>
      <Card.Header>
        <Card.Title>Quick Actions</Card.Title>
        <Card.Description>Common tasks to manage your workspace</Card.Description>
      </Card.Header>
      <Card.Content class="space-y-2">
        <Button
          variant="outline"
          class="w-full justify-start gap-2"
          onclick={() => goto(`/app/accounts/${accountId}/conversations`)}
        >
          <MessageSquare class="h-4 w-4" />
          View All Conversations
        </Button>
        <Button
          variant="outline"
          class="w-full justify-start gap-2"
          onclick={() => goto(`/app/accounts/${accountId}/contacts`)}
        >
          <Users class="h-4 w-4" />
          Manage Contacts
        </Button>
        <Button
          variant="outline"
          class="w-full justify-start gap-2"
          onclick={() => goto(`/app/accounts/${accountId}/settings`)}
        >
          <AlertCircle class="h-4 w-4" />
          Account Settings
        </Button>
      </Card.Content>
    </Card.Root>
    
    <!-- Recent Activity -->
    <Card.Root>
      <Card.Header>
        <Card.Title>Recent Activity</Card.Title>
        <Card.Description>Latest updates from your conversations</Card.Description>
      </Card.Header>
      <Card.Content>
        {#if isLoading}
          <div class="text-center text-muted-foreground py-4">
            Loading...
          </div>
        {:else if conversations.length === 0}
          <div class="text-center text-muted-foreground py-4">
            <p class="mb-2">No conversations yet</p>
            <p class="text-sm">Start by creating your first conversation</p>
          </div>
        {:else}
          <div class="space-y-2">
            {#each conversations.slice(0, 3) as conversation}
              <div 
                class="flex items-center gap-2 p-2 rounded-md hover:bg-muted/50 cursor-pointer transition-colors text-sm"
                onclick={() => goto(`/app/accounts/${accountId}/conversations/${conversation.id}`)}
              >
                <div class="flex-1 min-w-0 truncate">
                  <span class="font-medium">
                    {conversation.meta?.sender?.name || 'Unknown'}
                  </span>
                  <Badge variant="outline" class="ml-2 text-xs">
                    {conversation.status}
                  </Badge>
                </div>
              </div>
            {/each}
            
            {#if conversations.length > 3}
              <Button
                variant="link"
                size="sm"
                class="w-full mt-2"
                onclick={() => goto(`/app/accounts/${accountId}/conversations`)}
              >
                View all {conversations.length} conversations →
              </Button>
            {/if}
          </div>
        {/if}
      </Card.Content>
    </Card.Root>
  </div>
</div>
