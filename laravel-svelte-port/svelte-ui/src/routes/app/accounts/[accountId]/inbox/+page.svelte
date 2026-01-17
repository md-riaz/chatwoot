<script lang="ts">
  /**
   * Account Inbox Page
   * Shows conversations filtered by inbox
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { authStore } from '$lib/stores/auth.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  import * as Tabs from '$lib/components/ui/tabs';
  import { Input } from '$lib/components/ui/input';
  import { Skeleton } from '$lib/components/ui/skeleton';
  import { Search } from 'lucide-svelte';

  let accountId = $state<number | null>(null);
  let inboxId = $state<string | null>(null);
  let isLoading = $state(true);
  let activeTab = $state('all');
  let searchTerm = $state('');

  onMount(async () => {
    // Get account and inbox IDs from URL params
    const path = window.location.pathname;
    const match = path.match(/\/app\/accounts\/(\d+)\/inbox\/([^/]+)/);
    if (match) {
      accountId = parseInt(match[1]);
      inboxId = match[2]; // This could be an inbox ID or a special inbox identifier
      
      // Validate that user has access to this account
      const hasAccess = authStore.currentUser.accounts.some(acc => acc.id === accountId);
      if (!hasAccess) {
        goto('/app/unauthorized');
        return;
      }
      
      try {
        // Load inbox conversations
        await loadInboxConversations();
      } catch (error) {
        console.error('Failed to load inbox conversations:', error);
      } finally {
        isLoading = false;
      }
    } else {
      // Invalid route, redirect to user's first account
      if (authStore.userAccounts.length > 0) {
        goto(`/app/accounts/${authStore.userAccounts[0].id}/conversations`);
      } else {
        goto('/app');
      }
    }
  });

  async function loadInboxConversations() {
    // In a real implementation, this would call the API to get inbox conversations
    // For now, using mock data
    console.log('Loading conversations for inbox:', inboxId, 'in account:', accountId);
  }

  function handleNewConversation() {
    // Navigate to new conversation page
    goto(`/app/accounts/${accountId}/conversations/new`);
  }

  function handleConversationClick(conversationId: number) {
    goto(`/app/accounts/${accountId}/conversations/${conversationId}`);
  }

  // Mock conversations data for the inbox
  const mockConversations = [
    {
      id: 1,
      customerName: 'Alex Johnson',
      customerAvatar: 'https://github.com/shadcn.png',
      lastMessage: 'Can you help me with my order?',
      timestamp: '2 min ago',
      unread: true,
      status: 'open',
      priority: 'medium',
      channel: 'website'
    },
    {
      id: 2,
      customerName: 'Taylor Smith',
      customerAvatar: 'https://github.com/shadcn.png',
      lastMessage: 'Thanks for your help!',
      timestamp: '15 min ago',
      unread: false,
      status: 'resolved',
      priority: 'low',
      channel: 'email'
    },
    {
      id: 3,
      customerName: 'Jordan Williams',
      customerAvatar: 'https://github.com/shadcn.png',
      lastMessage: 'Still waiting for a response...',
      timestamp: '1 hour ago',
      unread: true,
      status: 'open',
      priority: 'high',
      channel: 'facebook'
    }
  ];
</script>

{#if isLoading}
  <div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center">
      <Skeleton class="h-8 w-[200px]" />
      <Skeleton class="h-9 w-[120px]" />
    </div>
    
    <div class="flex items-center gap-2 mb-4">
      <Skeleton class="h-10 w-full max-w-sm" />
      <Skeleton class="h-10 w-10" />
    </div>
    
    <Tabs.Root value="all" class="space-y-4">
      <Tabs.List>
        <Skeleton class="h-9 w-20" />
        <Skeleton class="h-9 w-20" />
        <Skeleton class="h-9 w-20" />
      </Tabs.List>
      
      <div class="space-y-4">
        {#each Array(5) as _}
          <Card>
            <CardContent class="flex items-center gap-4 p-4">
              <Skeleton class="h-10 w-10 rounded-full" />
              <div class="flex-1 space-y-2">
                <Skeleton class="h-4 w-[150px]" />
                <Skeleton class="h-3 w-[200px]" />
              </div>
              <Skeleton class="h-6 w-16" />
            </CardContent>
          </Card>
        {/each}
      </div>
    </Tabs.Root>
  </div>
{:else}
      <div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Inbox: {inboxId}</h1>
        <p class="text-muted-foreground">Conversations in this inbox</p>
      </div>
      <Button onclick={handleNewConversation}>New Conversation</Button>
    </div>

    <div class="flex items-center gap-2">
      <div class="relative flex-1 max-w-sm">
        <Search class="absolute left-2 top-1/2 transform -translate-y-1/2 text-muted-foreground h-4 w-4" />
        <Input 
          placeholder="Search conversations..." 
          class="pl-8" 
          bind:value={searchTerm}
        />
      </div>
    </div>

    <Tabs.Root value={activeTab} onValueChange={(value) => activeTab = value} class="space-y-4">
      <Tabs.List class="grid w-full max-w-md grid-cols-3">
        <Tabs.Trigger value="all">All</Tabs.Trigger>
        <Tabs.Trigger value="unread">Unread</Tabs.Trigger>
        <Tabs.Trigger value="assigned">Assigned</Tabs.Trigger>
      </Tabs.List>
      
      <Tabs.Content value="all" class="space-y-4">
        {#each mockConversations as conversation}
          <Card class="cursor-pointer hover:bg-accent transition-colors" onclick={() => handleConversationClick(conversation.id)}>
            <CardContent class="flex items-center gap-4 p-4">
              <Avatar>
                <AvatarImage src={conversation.customerAvatar} alt={conversation.customerName} />
                <AvatarFallback>{conversation.customerName.split(' ').map(n => n[0]).join('')}</AvatarFallback>
              </Avatar>
              
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <h3 class="font-medium truncate">{conversation.customerName}</h3>
                  {#if conversation.unread}
                    <Badge variant="default">New</Badge>
                  {/if}
                </div>
                <p class="text-sm text-muted-foreground truncate">{conversation.lastMessage}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs text-muted-foreground">{conversation.timestamp}</span>
                  <Badge variant="secondary">{conversation.channel}</Badge>
                  {#if conversation.priority === 'high'}
                    <Badge variant="destructive">High Priority</Badge>
                  {:else if conversation.priority === 'medium'}
                    <Badge variant="default">Medium Priority</Badge>
                  {:else}
                    <Badge variant="outline">Low Priority</Badge>
                  {/if}
                </div>
              </div>
              
              <div class="flex flex-col items-end">
                <Badge 
                  variant={
                    conversation.status === 'open' ? 'default' : 
                    conversation.status === 'resolved' ? 'secondary' : 
                    'outline-solid'
                  }
                >
                  {conversation.status}
                </Badge>
              </div>
            </CardContent>
          </Card>
        {/each}
      </Tabs.Content>
      
      <Tabs.Content value="unread" class="space-y-4">
        {#each mockConversations.filter(c => c.unread) as conversation}
          <Card class="cursor-pointer hover:bg-accent transition-colors" onclick={() => handleConversationClick(conversation.id)}>
            <CardContent class="flex items-center gap-4 p-4">
              <Avatar>
                <AvatarImage src={conversation.customerAvatar} alt={conversation.customerName} />
                <AvatarFallback>{conversation.customerName.split(' ').map(n => n[0]).join('')}</AvatarFallback>
              </Avatar>
              
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <h3 class="font-medium truncate">{conversation.customerName}</h3>
                  {#if conversation.unread}
                    <Badge variant="default">New</Badge>
                  {/if}
                </div>
                <p class="text-sm text-muted-foreground truncate">{conversation.lastMessage}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs text-muted-foreground">{conversation.timestamp}</span>
                  <Badge variant="secondary">{conversation.channel}</Badge>
                  {#if conversation.priority === 'high'}
                    <Badge variant="destructive">High Priority</Badge>
                  {:else if conversation.priority === 'medium'}
                    <Badge variant="default">Medium Priority</Badge>
                  {:else}
                    <Badge variant="outline">Low Priority</Badge>
                  {/if}
                </div>
              </div>
              
              <div class="flex flex-col items-end">
                <Badge 
                  variant={
                    conversation.status === 'open' ? 'default' : 
                    conversation.status === 'resolved' ? 'secondary' : 
                    'outline-solid'
                  }
                >
                  {conversation.status}
                </Badge>
              </div>
            </CardContent>
          </Card>
        {/each}
      </Tabs.Content>
      
      <Tabs.Content value="assigned" class="space-y-4">
        {#each mockConversations as conversation}
          <Card class="cursor-pointer hover:bg-accent transition-colors" onclick={() => handleConversationClick(conversation.id)}>
            <CardContent class="flex items-center gap-4 p-4">
              <Avatar>
                <AvatarImage src={conversation.customerAvatar} alt={conversation.customerName} />
                <AvatarFallback>{conversation.customerName.split(' ').map(n => n[0]).join('')}</AvatarFallback>
              </Avatar>
              
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <h3 class="font-medium truncate">{conversation.customerName}</h3>
                  {#if conversation.unread}
                    <Badge variant="default">New</Badge>
                  {/if}
                </div>
                <p class="text-sm text-muted-foreground truncate">{conversation.lastMessage}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-xs text-muted-foreground">{conversation.timestamp}</span>
                  <Badge variant="secondary">{conversation.channel}</Badge>
                  {#if conversation.priority === 'high'}
                    <Badge variant="destructive">High Priority</Badge>
                  {:else if conversation.priority === 'medium'}
                    <Badge variant="default">Medium Priority</Badge>
                  {:else}
                    <Badge variant="outline">Low Priority</Badge>
                  {/if}
                </div>
              </div>
              
              <div class="flex flex-col items-end">
                <Badge 
                  variant={
                    conversation.status === 'open' ? 'default' : 
                    conversation.status === 'resolved' ? 'secondary' : 
                    'outline-solid'
                  }
                >
                  {conversation.status}
                </Badge>
              </div>
            </CardContent>
          </Card>
        {/each}
      </Tabs.Content>
    </Tabs.Root>
  </div>
{/if}
