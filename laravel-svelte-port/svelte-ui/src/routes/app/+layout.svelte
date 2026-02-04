<script lang="ts">
  /**
   * Authenticated App Layout
   * Main application shell with header and sidebar
   */
  
  import AppSidebar from '$lib/components/layout/AppSidebar.svelte';
  import MobileSidebarLauncher from '$lib/components/layout/MobileSidebarLauncher.svelte';
  import KeyboardShortcutsModal from '$lib/components/ui/keyboard-shortcuts-modal.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { inboxesStore } from '$lib/stores/inboxes.svelte';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import { teamsStore } from '$lib/stores/teams.svelte';
  import { customViewsStore } from '$lib/stores/customViews.svelte';
  import { notificationsStore } from '$lib/stores/notifications.svelte';
  import { getReverbClient } from '$lib/websocket/reverb-client';
  import { getWebSocketEventManager } from '$lib/websocket/event-manager';
  import { getWebSocketStore } from '$lib/websocket/store.svelte';
  import * as Sidebar from '$lib/components/ui/sidebar/index.js';
  import type { Snippet } from 'svelte';
  import { onMount, onDestroy } from 'svelte';
  import { goto } from '$app/navigation';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
  
  // Local state
  let isSidebarOpen = $state(true);
  
  // WebSocket state
  let eventManager = getWebSocketEventManager();
  let wsStore = getWebSocketStore();

  function handleGlobalKeydown(e: KeyboardEvent) {
    // Cmd+K or Ctrl+K -> Search
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
      e.preventDefault();
      if (authStore.currentAccountId) {
        goto(`/app/accounts/${authStore.currentAccountId}/search`);
      }
    }

    // Cmd+/ or Ctrl+/ -> Keyboard Shortcuts
    if ((e.metaKey || e.ctrlKey) && e.key === '/') {
      e.preventDefault();
      window.dispatchEvent(new CustomEvent('open-keyboard-shortcuts'));
    }

    // Alt+O -> Toggle Sidebar (Chatwoot Legacy)
    if (e.altKey && (e.key === 'o' || e.key === 'O')) {
      e.preventDefault();
      isSidebarOpen = !isSidebarOpen;
    }
  }
  
  // WebSocket configuration constants
  // Note: Default ports - Laravel API: 8000, Reverb WebSocket: 8080
  // In production, both typically use the same domain with reverse proxy
  const DEFAULT_API_URL = 'http://localhost:8000';
  const DEFAULT_WS_URL = 'ws://localhost:8080/ws';
  
  // Initialize auth and WebSocket on mount
  onMount(async () => {
    // Validate auth session
    try {
      await authStore.validateSession();
      
      // Load initial data if user is logged in and account is selected
      if (authStore.isLoggedIn && authStore.currentAccountId) {
        Promise.all([
          inboxesStore.fetchInboxes(),
          labelsStore.fetchLabels(),
          teamsStore.fetchTeams(),
          customViewsStore.fetchCustomViews(),
          notificationsStore.fetchUnreadCount(authStore.currentAccountId)
        ]).catch(err => console.error('Failed to load initial data:', err));
        
        // WebSocket initialization is handled by $effect below
      }
    } catch (error) {
      console.error('Session validation failed:', error);
      // Auth guard will handle redirect
    }
  });
  
  // Cleanup on destroy
  onDestroy(() => {
    eventManager.cleanup();
    try {
      const client = getReverbClient();
      client.disconnect();
    } catch (error) {
      // Client might not be initialized
      console.debug('WebSocket client cleanup skipped:', error);
    }
  });

  async function initializeWebSocket() {
    const token = localStorage.getItem('auth_token');
    if (!token || !authStore.currentAccountId || !authStore.currentUser?.id) {
      return;
    }

    try {
      // WebSocket configuration
      const wsUrl = import.meta.env.VITE_WS_URL || DEFAULT_WS_URL;
      let wsHost = '127.0.0.1';
      let wsPort = 8080;
      let useTLS = false;
      let reverbKey = import.meta.env.VITE_REVERB_APP_KEY || 'clearline-app-key';
      
      // Parse WebSocket URL
      try {
        const url = new URL(wsUrl);
        wsHost = url.hostname;
        useTLS = url.protocol === 'wss:';
        
        if (url.pathname === '/' || url.pathname === '') {
          wsPort = url.port ? parseInt(url.port) : 8080;
        } else if (url.pathname.startsWith('/ws')) {
          wsPort = url.port ? parseInt(url.port) : (url.protocol === 'wss:' ? 443 : 80);
        } else if (url.pathname.startsWith('/app/')) {
          const pathParts = url.pathname.split('/');
          if (pathParts.length >= 3) {
            reverbKey = pathParts[2];
          }
          wsPort = url.port ? parseInt(url.port) : 8080;
        } else {
          wsPort = url.port ? parseInt(url.port) : 8080;
        }
      } catch (error) {
        console.error('Invalid WebSocket URL, using defaults:', error);
      }

      // Initialize Reverb client
      const client = getReverbClient({
        host: wsHost,
        port: wsPort,
        key: reverbKey,
        forceTLS: useTLS,
        authEndpoint: `${import.meta.env.VITE_API_BASE_URL || DEFAULT_API_URL}/api/v1/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        },
      });

      // Connect to WebSocket
      client.connect();

      // Initialize event subscriptions
      eventManager.initializeForAccount(authStore.currentAccountId, authStore.currentUser.id);

      console.log('WebSocket initialized successfully');
    } catch (error) {
      console.error('Failed to initialize WebSocket:', error);
      wsStore.setError(error instanceof Error ? error.message : 'WebSocket initialization failed');
    }
  }

  // Reactive: reinitialize WebSocket when account changes
  $effect(() => {
    if (authStore.currentAccountId && authStore.currentUser?.id) {
      initializeWebSocket();
    }
  });
</script>

<svelte:window onkeydown={handleGlobalKeydown} />

<Sidebar.Provider bind:open={isSidebarOpen}>
  <AppSidebar />
  <MobileSidebarLauncher />
  <Sidebar.Inset class="h-svh overflow-hidden">
    <main class="flex-1 overflow-y-auto">
      {@render children()}
    </main>
  </Sidebar.Inset>
  <KeyboardShortcutsModal />
</Sidebar.Provider>
