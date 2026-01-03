<script lang="ts">
  import type { ConversationMetrics } from '$lib/api/reports';
  import * as Card from '$lib/components/ui/card';
  import { TrendingUp, MessageCircle, CheckCircle, Clock } from '@lucide/svelte';
  
  interface Props {
    metrics: ConversationMetrics | null;
    isLoading?: boolean;
  }
  
  let { metrics, isLoading = false }: Props = $props();
  
  function formatTime(seconds: number): string {
    if (seconds < 60) return `${Math.round(seconds)}s`;
    if (seconds < 3600) return `${Math.round(seconds / 60)}m`;
    return `${Math.round(seconds / 3600)}h`;
  }
</script>

<div class="metrics-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  <!-- Total Conversations -->
  <Card.Root>
    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
      <Card.Title class="text-sm font-medium">Total Conversations</Card.Title>
      <MessageCircle class="h-4 w-4 text-muted-foreground" />
    </Card.Header>
    <Card.Content>
      {#if isLoading}
        <div class="animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-20"></div>
        </div>
      {:else}
        <div class="text-2xl font-bold">{metrics?.totalConversations || 0}</div>
      {/if}
    </Card.Content>
  </Card.Root>
  
  <!-- Open Conversations -->
  <Card.Root>
    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
      <Card.Title class="text-sm font-medium">Open</Card.Title>
      <TrendingUp class="h-4 w-4 text-blue-600" />
    </Card.Header>
    <Card.Content>
      {#if isLoading}
        <div class="animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-20"></div>
        </div>
      {:else}
        <div class="text-2xl font-bold text-blue-600">{metrics?.openConversations || 0}</div>
      {/if}
    </Card.Content>
  </Card.Root>
  
  <!-- Resolved Conversations -->
  <Card.Root>
    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
      <Card.Title class="text-sm font-medium">Resolved</Card.Title>
      <CheckCircle class="h-4 w-4 text-green-600" />
    </Card.Header>
    <Card.Content>
      {#if isLoading}
        <div class="animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-20"></div>
        </div>
      {:else}
        <div class="text-2xl font-bold text-green-600">{metrics?.resolvedConversations || 0}</div>
      {/if}
    </Card.Content>
  </Card.Root>
  
  <!-- Avg Response Time -->
  <Card.Root>
    <Card.Header class="flex flex-row items-center justify-between space-y-0 pb-2">
      <Card.Title class="text-sm font-medium">Avg Response Time</Card.Title>
      <Clock class="h-4 w-4 text-muted-foreground" />
    </Card.Header>
    <Card.Content>
      {#if isLoading}
        <div class="animate-pulse">
          <div class="h-8 bg-gray-200 rounded w-20"></div>
        </div>
      {:else}
        <div class="text-2xl font-bold">
          {formatTime(metrics?.avgResponseTime || 0)}
        </div>
      {/if}
    </Card.Content>
  </Card.Root>
</div>
