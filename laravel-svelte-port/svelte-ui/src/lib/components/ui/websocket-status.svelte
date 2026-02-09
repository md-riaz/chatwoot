<!--
  WebSocket Connection Status Indicator
  Shows the current WebSocket connection state
-->
<script lang="ts">
  import { getWebSocketStore } from '$lib/websocket/store.svelte.js';
  import { Badge } from '$lib/components/ui/badge/index.js';
  import { Wifi, WifiOff, RotateCcw, AlertTriangle } from 'lucide-svelte';
  
  interface Props {
    showDetails?: boolean;
    class?: string;
  }
  
  let { showDetails = false, class: className = '' }: Props = $props();
  
  let wsStore = getWebSocketStore();
  
  // Reactive values
  let connectionState = $derived(wsStore.connectionState);
  let connectionStatus = $derived(wsStore.connectionStatus);
  let isConnected = $derived(wsStore.isConnected);
  let isConnecting = $derived(wsStore.isConnecting);
  let isReconnecting = $derived(wsStore.isReconnecting);
  let isFailed = $derived(wsStore.isFailed);
  let error = $derived(wsStore.error);
  let stats = $derived(wsStore.stats);
  
  // Get appropriate icon and variant
  let icon = $derived(() => {
    if (isConnected) return Wifi;
    if (isConnecting || isReconnecting) return RotateCcw;
    if (isFailed) return AlertTriangle;
    return WifiOff;
  });
  
  let variant = $derived(() => {
    if (isConnected) return 'default';
    if (isConnecting || isReconnecting) return 'secondary';
    if (isFailed) return 'destructive';
    return 'outline';
  });
  
  let iconClass = $derived(() => {
    if (isConnecting || isReconnecting) return 'animate-spin';
    return '';
  });
</script>

<div class="websocket-status {className}">
  <Badge {variant} class="flex items-center gap-1.5">
    {@const Icon = icon}
    <Icon class="w-3 h-3 {iconClass}" />
    <span class="text-xs font-medium">
      {connectionStatus}
    </span>
  </Badge>
  
  {#if showDetails && (error || stats.connectionDuration)}
    <div class="websocket-status__details">
      {#if error}
        <div class="websocket-status__error">
          <AlertTriangle class="w-3 h-3 text-destructive" />
          <span class="text-xs text-destructive">{error}</span>
        </div>
      {/if}
      
      {#if stats.connectionDuration}
        <div class="websocket-status__duration">
          <span class="text-xs text-muted-foreground">
            Connected for {stats.connectionDuration}
          </span>
        </div>
      {/if}
      
      {#if stats.subscriptionsCount > 0}
        <div class="websocket-status__subscriptions">
          <span class="text-xs text-muted-foreground">
            {stats.subscriptionsCount} subscription{stats.subscriptionsCount === 1 ? '' : 's'}
          </span>
        </div>
      {/if}
    </div>
  {/if}
</div>

<style>
  .websocket-status {
    @apply flex flex-col gap-1;
  }
  
  .websocket-status__details {
    @apply flex flex-col gap-1;
  }
  
  .websocket-status__error {
    @apply flex items-center gap-1;
  }
  
  .websocket-status__duration,
  .websocket-status__subscriptions {
    @apply flex items-center;
  }
</style>