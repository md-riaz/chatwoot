<script lang="ts">
  import { onMount } from 'svelte';
  import { auditLogsStore } from '$lib/stores/auditLogs.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Input } from '$lib/components/ui/input';
  import { FileText, Download, RefreshCw, Loader2, Clock, AlertCircle } from '@lucide/svelte';
  import { toast } from 'svelte-sonner';
  
  const logs = $derived(auditLogsStore.logs);
  const isLoading = $derived(auditLogsStore.isLoading);
  const isExporting = $derived(auditLogsStore.isExporting);
  const hasMorePages = $derived(auditLogsStore.hasMorePages);
  const totalCount = $derived(auditLogsStore.totalCount);
  
  let startDate = $state('');
  let endDate = $state('');
  
  onMount(() => {
    auditLogsStore.fetchLogs();
  });
  
  async function handleRefresh() {
    await auditLogsStore.fetchLogs({ startDate, endDate });
  }
  
  async function handleExport() {
    await auditLogsStore.exportLogs();
    toast.success('Audit logs exported successfully');
  }
  
  function handleLoadMore() {
    auditLogsStore.loadMore();
  }
  
  function formatTimestamp(timestamp: string): string {
    const date = new Date(timestamp);
    return date.toLocaleString();
  }
  
  function formatRelativeTime(timestamp: string): string {
    const date = new Date(timestamp);
    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);
    
    if (seconds < 60) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString();
  }
</script>

<div class="audit-logs-page">
  <div class="header mb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <FileText class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">Audit Logs</h1>
        </div>
        <p class="text-gray-600">
          Track all activities and changes in your account
        </p>
        <div class="mt-4">
          <span class="text-2xl font-bold text-primary">{totalCount}</span>
          <span class="text-gray-600 ml-2">Total Logs</span>
        </div>
      </div>
      
      <div class="flex items-center gap-2">
        <Input
          type="date"
          bind:value={startDate}
          placeholder="Start date"
          class="w-40"
        />
        <span class="text-sm text-gray-500">to</span>
        <Input
          type="date"
          bind:value={endDate}
          placeholder="End date"
          class="w-40"
        />
        <Button onclick={handleRefresh} disabled={isLoading}>
          <RefreshCw class="h-4 w-4 mr-2 {isLoading ? 'animate-spin' : ''}" />
          Refresh
        </Button>
        <Button onclick={handleExport} disabled={isExporting} variant="outline">
          <Download class="h-4 w-4 mr-2" />
          Export CSV
        </Button>
      </div>
    </div>
  </div>
  
  <div class="content">
    {#if isLoading && logs.length === 0}
      <div class="flex items-center justify-center py-12">
        <Loader2 class="h-8 w-8 animate-spin text-gray-400" />
      </div>
    {:else if logs.length === 0}
      <Card.Root>
        <Card.Content class="p-12 text-center">
          <AlertCircle class="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-semibold mb-2">No audit logs found</h3>
          <p class="text-gray-600">
            Activity logs will appear here as actions are performed
          </p>
        </Card.Content>
      </Card.Root>
    {:else}
      <Card.Root>
        <Card.Content class="p-0">
          <div class="divide-y">
            {#each logs as log (log.id)}
              <div class="p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start gap-4">
                  {#if log.userAvatar}
                    <img 
                      src={log.userAvatar} 
                      alt={log.userName}
                      class="w-10 h-10 rounded-full"
                    />
                  {:else}
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-sm font-semibold">
                      {log.userName.charAt(0)}
                    </div>
                  {/if}
                  
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                      <div class="flex-1">
                        <p class="font-medium text-sm">{log.userName}</p>
                        <p class="text-sm text-gray-700 mt-1">{log.activityMessage}</p>
                        <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                          <span class="flex items-center gap-1">
                            <Clock class="h-3 w-3" />
                            {formatRelativeTime(log.createdAt)}
                          </span>
                          <span>•</span>
                          <span>{log.activityType}</span>
                          {#if log.resourceType}
                            <span>•</span>
                            <span>{log.resourceType}</span>
                          {/if}
                        </div>
                      </div>
                      <span class="text-xs text-gray-400 whitespace-nowrap">
                        {formatTimestamp(log.createdAt)}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            {/each}
          </div>
          
          {#if hasMorePages}
            <div class="p-4 text-center border-t">
              <Button
                variant="ghost"
                size="sm"
                onclick={handleLoadMore}
                disabled={isLoading}
              >
                {#if isLoading}
                  <Loader2 class="h-4 w-4 mr-2 animate-spin" />
                {/if}
                Load more
              </Button>
            </div>
          {/if}
        </Card.Content>
      </Card.Root>
    {/if}
  </div>
</div>

<style>
  .audit-logs-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }
  
  .header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1.5rem;
  }
</style>
