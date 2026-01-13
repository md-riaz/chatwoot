<script lang="ts">
  /**
   * Account Dashboard Page
   * Main dashboard for a specific account
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { authStore } from '$lib/stores/auth.svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import { Separator } from '$lib/components/ui/separator';
  import { Avatar, AvatarFallback, AvatarImage } from '$lib/components/ui/avatar';
  import { Skeleton } from '$lib/components/ui/skeleton';

  let accountId = $state<number | null>(null);
  let isLoading = $state(true);
  let stats = $state({
    totalConversations: 0,
    openConversations: 0,
    unassignedConversations: 0,
    pendingMessages: 0
  });

  onMount(async () => {
    // Get account ID from URL params
    const path = window.location.pathname;
    const match = path.match(/\/app\/accounts\/(\d+)\/dashboard/);
    if (match) {
      accountId = parseInt(match[1]);
      
      // Validate that user has access to this account
      const hasAccess = authStore.currentUser.accounts.some(acc => acc.id === accountId);
      if (!hasAccess) {
        goto('/app/unauthorized');
        return;
      }
      
      try {
        // Load dashboard stats
        await loadDashboardStats();
      } catch (error) {
        console.error('Failed to load dashboard stats:', error);
      } finally {
        isLoading = false;
      }
    } else {
      // Invalid route, redirect to user's first account
      if (authStore.userAccounts.length > 0) {
        goto(`/app/accounts/${authStore.userAccounts[0].id}/dashboard`);
      } else {
        goto('/app');
      }
    }
  });

  async function loadDashboardStats() {
    // In a real implementation, this would call the API to get dashboard stats
    // For now, using mock data
    stats = {
      totalConversations: 142,
      openConversations: 28,
      unassignedConversations: 12,
      pendingMessages: 5
    };
  }

  function handleViewConversations() {
    goto(`/app/accounts/${accountId}/conversations`);
  }

  function handleViewReports() {
    goto(`/app/accounts/${accountId}/reports`);
  }
</script>

{#if isLoading}
  <div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center">
      <Skeleton class="h-8 w-[200px]" />
      <Skeleton class="h-9 w-[120px]" />
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      {#each Array(4) as _}
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <Skeleton class="h-4 w-[80px]" />
            <Skeleton class="h-4 w-4" />
          </CardHeader>
          <CardContent>
            <Skeleton class="h-8 w-[60px] mb-2" />
            <Skeleton class="h-3 w-[100px]" />
          </CardContent>
        </Card>
      {/each}
    </div>
    
    <Card>
      <CardHeader>
        <Skeleton class="h-6 w-[150px]" />
      </CardHeader>
      <CardContent>
        <Skeleton class="h-32 w-full" />
      </CardContent>
    </Card>
  </div>
{:else}
  <div class="container mx-auto py-6 space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">Dashboard</h1>
        <p class="text-muted-foreground">Welcome back! Here's what's happening with your account.</p>
      </div>
      <Button>View Reports</Button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Conversations</CardTitle>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
          </svg>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{stats.totalConversations}</div>
          <p class="text-xs text-muted-foreground">+12% from last month</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Open</CardTitle>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10" />
            <line x1="15" y1="9" x2="9" y2="15" />
            <line x1="9" y1="9" x2="15" y2="15" />
          </svg>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{stats.openConversations}</div>
          <p class="text-xs text-muted-foreground">+2 from last hour</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Unassigned</CardTitle>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
            <line x1="9" y1="9" x2="15" y2="15" />
            <line x1="15" y1="9" x2="9" y2="15" />
          </svg>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{stats.unassignedConversations}</div>
          <p class="text-xs text-muted-foreground">-1 from yesterday</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Messages</CardTitle>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
          </svg>
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{stats.pendingMessages}</div>
          <p class="text-xs text-muted-foreground">+3 from last hour</p>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <Card class="lg:col-span-2">
        <CardHeader>
          <CardTitle>Recent Conversations</CardTitle>
          <CardDescription>Your most recent customer interactions</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            {#each Array(5) as _, i}
              <div class="flex items-center gap-4">
                <Avatar>
                  <AvatarImage src={`https://github.com/shadcn.png`} alt="User" />
                  <AvatarFallback>CN</AvatarFallback>
                </Avatar>
                <div class="flex-1">
                  <div class="font-medium">Customer {i + 1}</div>
                  <div class="text-sm text-muted-foreground">Need help with my order #ORD-{i + 123}</div>
                </div>
                <Badge variant="outline">Open</Badge>
              </div>
            {/each}
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Quick Actions</CardTitle>
          <CardDescription>Common tasks you might need</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 gap-2">
            <Button variant="outline" class="justify-start" onclick={handleViewConversations}>
              View All Conversations
            </Button>
            <Button variant="outline" class="justify-start" onclick={handleViewReports}>
              View Reports
            </Button>
            <Button variant="outline" class="justify-start">
              Manage Agents
            </Button>
            <Button variant="outline" class="justify-start">
              Configure Settings
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
{/if}