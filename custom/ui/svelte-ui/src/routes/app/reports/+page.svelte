<script lang="ts">
  /**
   * Reports and Analytics Page
   * View conversation metrics and team performance
   */
  
  import { onMount } from 'svelte';
  import { BarChart3, TrendingUp, Clock, Users, MessageSquare, CheckCircle } from '@lucide/svelte';
  import { conversationsStore } from '$lib/stores/conversations.svelte';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import * as Tabs from '$lib/components/ui/tabs';
  import { Button } from '$lib/components/ui/button';
  
  // Reactive store access
  const conversations = $derived(conversationsStore.allConversations);
  const isLoading = $derived(conversationsStore.isLoading);
  
  // Time period state
  let timePeriod = $state<'today' | 'week' | 'month'>('week');
  
  // Derived analytics
  const totalConversations = $derived(conversations.length);
  const openConversations = $derived(
    conversations.filter(c => c.status === 'open').length
  );
  const resolvedConversations = $derived(
    conversations.filter(c => c.status === 'resolved').length
  );
  const avgResolutionTime = $derived('3.2 hours'); // Placeholder
  const resolutionRate = $derived(
    totalConversations > 0 
      ? Math.round((resolvedConversations / totalConversations) * 100)
      : 0
  );
  
  // Team metrics (placeholder data)
  const teamMetrics = $derived([
    { name: 'Agent 1', resolved: 45, avgTime: '2.5h', satisfaction: 4.8 },
    { name: 'Agent 2', resolved: 38, avgTime: '3.1h', satisfaction: 4.6 },
    { name: 'Agent 3', resolved: 32, avgTime: '3.8h', satisfaction: 4.5 },
  ]);
  
  // Conversation trends (placeholder)
  const conversationTrends = $derived([
    { day: 'Mon', count: 12 },
    { day: 'Tue', count: 18 },
    { day: 'Wed', count: 15 },
    { day: 'Thu', count: 22 },
    { day: 'Fri', count: 19 },
    { day: 'Sat', count: 8 },
    { day: 'Sun', count: 6 },
  ]);
  
  // Load data on mount
  onMount(async () => {
    await conversationsStore.fetchConversations();
  });
</script>

<div class="h-full flex flex-col">
  <!-- Header -->
  <div class="p-6 border-b">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h1 class="text-3xl font-bold">Reports & Analytics</h1>
        <p class="text-muted-foreground">
          Track your team's performance and conversation metrics
        </p>
      </div>
      <div class="flex gap-2">
        <Button 
          variant={timePeriod === 'today' ? 'default' : 'outline'}
          size="sm"
          onclick={() => timePeriod = 'today'}
        >
          Today
        </Button>
        <Button 
          variant={timePeriod === 'week' ? 'default' : 'outline'}
          size="sm"
          onclick={() => timePeriod = 'week'}
        >
          This Week
        </Button>
        <Button 
          variant={timePeriod === 'month' ? 'default' : 'outline'}
          size="sm"
          onclick={() => timePeriod = 'month'}
        >
          This Month
        </Button>
      </div>
    </div>
  </div>
  
  <!-- Content -->
  <div class="flex-1 overflow-y-auto p-6">
    <Tabs.Root value="overview" class="space-y-6">
      <Tabs.List>
        <Tabs.Trigger value="overview">Overview</Tabs.Trigger>
        <Tabs.Trigger value="team">Team Performance</Tabs.Trigger>
        <Tabs.Trigger value="trends">Trends</Tabs.Trigger>
      </Tabs.List>
      
      <!-- Overview Tab -->
      <Tabs.Content value="overview" class="space-y-6">
        <!-- Key Metrics -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <Card.Root>
            <Card.Header class="flex flex-row items-center justify-between pb-2">
              <Card.Title class="text-sm font-medium text-muted-foreground">
                Total Conversations
              </Card.Title>
              <MessageSquare class="h-4 w-4 text-blue-600" />
            </Card.Header>
            <Card.Content>
              <div class="text-2xl font-bold">{totalConversations}</div>
              <p class="text-xs text-muted-foreground mt-1">
                {timePeriod === 'today' ? 'Today' : timePeriod === 'week' ? 'This week' : 'This month'}
              </p>
            </Card.Content>
          </Card.Root>
          
          <Card.Root>
            <Card.Header class="flex flex-row items-center justify-between pb-2">
              <Card.Title class="text-sm font-medium text-muted-foreground">
                Resolution Rate
              </Card.Title>
              <CheckCircle class="h-4 w-4 text-green-600" />
            </Card.Header>
            <Card.Content>
              <div class="text-2xl font-bold">{resolutionRate}%</div>
              <p class="text-xs text-muted-foreground mt-1">
                {resolvedConversations} of {totalConversations} resolved
              </p>
            </Card.Content>
          </Card.Root>
          
          <Card.Root>
            <Card.Header class="flex flex-row items-center justify-between pb-2">
              <Card.Title class="text-sm font-medium text-muted-foreground">
                Avg Resolution Time
              </Card.Title>
              <Clock class="h-4 w-4 text-purple-600" />
            </Card.Header>
            <Card.Content>
              <div class="text-2xl font-bold">{avgResolutionTime}</div>
              <p class="text-xs text-muted-foreground mt-1">
                Average time to resolve
              </p>
            </Card.Content>
          </Card.Root>
          
          <Card.Root>
            <Card.Header class="flex flex-row items-center justify-between pb-2">
              <Card.Title class="text-sm font-medium text-muted-foreground">
                Currently Open
              </Card.Title>
              <TrendingUp class="h-4 w-4 text-yellow-600" />
            </Card.Header>
            <Card.Content>
              <div class="text-2xl font-bold">{openConversations}</div>
              <p class="text-xs text-muted-foreground mt-1">
                Needs attention
              </p>
            </Card.Content>
          </Card.Root>
        </div>
        
        <!-- Conversation Status Breakdown -->
        <Card.Root>
          <Card.Header>
            <Card.Title>Conversation Status</Card.Title>
            <Card.Description>
              Distribution of conversations by status
            </Card.Description>
          </Card.Header>
          <Card.Content>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="h-3 w-3 rounded-full bg-green-500"></div>
                  <span class="text-sm">Resolved</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium">{resolvedConversations}</span>
                  <Badge variant="secondary">{resolutionRate}%</Badge>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="h-3 w-3 rounded-full bg-blue-500"></div>
                  <span class="text-sm">Open</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium">{openConversations}</span>
                  <Badge variant="secondary">
                    {totalConversations > 0 ? Math.round((openConversations / totalConversations) * 100) : 0}%
                  </Badge>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                  <span class="text-sm">Pending</span>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium">
                    {conversations.filter(c => c.status === 'pending').length}
                  </span>
                  <Badge variant="secondary">
                    {totalConversations > 0 ? Math.round((conversations.filter(c => c.status === 'pending').length / totalConversations) * 100) : 0}%
                  </Badge>
                </div>
              </div>
            </div>
          </Card.Content>
        </Card.Root>
      </Tabs.Content>
      
      <!-- Team Performance Tab -->
      <Tabs.Content value="team" class="space-y-6">
        <Card.Root>
          <Card.Header>
            <Card.Title>Team Performance</Card.Title>
            <Card.Description>
              Individual agent metrics and performance
            </Card.Description>
          </Card.Header>
          <Card.Content>
            <div class="space-y-4">
              {#each teamMetrics as agent}
                <div class="flex items-center gap-4 p-4 border rounded-lg">
                  <div class="flex-1">
                    <h3 class="font-medium">{agent.name}</h3>
                    <div class="flex gap-4 mt-2 text-sm text-muted-foreground">
                      <div>
                        <span class="font-medium">{agent.resolved}</span> resolved
                      </div>
                      <div>
                        Avg time: <span class="font-medium">{agent.avgTime}</span>
                      </div>
                      <div>
                        Rating: <span class="font-medium">{agent.satisfaction}/5.0</span>
                      </div>
                    </div>
                  </div>
                  <Badge variant="secondary">
                    {agent.resolved} conversations
                  </Badge>
                </div>
              {/each}
            </div>
          </Card.Content>
        </Card.Root>
      </Tabs.Content>
      
      <!-- Trends Tab -->
      <Tabs.Content value="trends" class="space-y-6">
        <Card.Root>
          <Card.Header>
            <Card.Title>Conversation Trends</Card.Title>
            <Card.Description>
              Daily conversation volume over the past week
            </Card.Description>
          </Card.Header>
          <Card.Content>
            <div class="space-y-2">
              {#each conversationTrends as trend}
                <div class="flex items-center gap-3">
                  <span class="text-sm font-medium w-12">{trend.day}</span>
                  <div class="flex-1 bg-muted rounded-full h-8 relative overflow-hidden">
                    <div 
                      class="bg-blue-500 h-full transition-all"
                      style="width: {(trend.count / 25) * 100}%"
                    ></div>
                    <span class="absolute inset-0 flex items-center justify-center text-xs font-medium">
                      {trend.count} conversations
                    </span>
                  </div>
                </div>
              {/each}
            </div>
          </Card.Content>
        </Card.Root>
      </Tabs.Content>
    </Tabs.Root>
  </div>
</div>
