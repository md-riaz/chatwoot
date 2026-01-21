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
  import { ReverbClient, getReverbClient } from '$lib/websocket/reverb-client';
  import * as Sidebar from '$lib/components/ui/sidebar/index.js';
  import type { Snippet } from 'svelte';
  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
  
  // Local state
  let reverbClient: ReverbClient | null = null;
  let isSidebarOpen = $state(true);

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
      }
    } catch (error) {
      console.error('Session validation failed:', error);
      // Auth guard will handle redirect
    }
    
    // Initialize WebSocket connection
    const token = localStorage.getItem('auth_token');
    
    if (token) {
      // Get WebSocket URL from environment or construct default
      const wsUrl = import.meta.env.VITE_WS_URL || DEFAULT_WS_URL;
      let wsHost = '127.0.0.1';
      let wsPort = 8080;
      let useTLS = false;
      let reverbKey = 'clearline-app-key'; // Default key from Laravel .env
      
      try {
        const url = new URL(wsUrl);
        wsHost = url.hostname;
        useTLS = url.protocol === 'wss:';
        
        // Handle different URL formats:
        // Direct Reverb: ws://host:port (Pusher.js will add /app/{key})
        // Proxied: ws://host/ws or wss://host/ws
        if (url.pathname === '/' || url.pathname === '') {
          // Direct connection to Reverb - use port from URL or default
          wsPort = url.port ? parseInt(url.port) : 8080;
        } else if (url.pathname.startsWith('/ws')) {
          // Proxied connection - use standard ports
          wsPort = url.port ? parseInt(url.port) : (url.protocol === 'wss:' ? 443 : 80);
        } else if (url.pathname.startsWith('/app/')) {
          // Legacy format with key in path - extract key and use direct connection
          const pathParts = url.pathname.split('/');
          if (pathParts.length >= 3) {
            reverbKey = pathParts[2];
          }
          wsPort = url.port ? parseInt(url.port) : 8080;
        } else {
          // Unknown format - assume direct connection
          wsPort = url.port ? parseInt(url.port) : 8080;
        }
      } catch (error) {
        console.error('Invalid WebSocket URL, using defaults:', error);
      }
      
      reverbClient = getReverbClient({
        host: wsHost,
        port: wsPort,
        key: reverbKey,
        forceTLS: useTLS,
        authEndpoint: `${import.meta.env.VITE_API_BASE_URL || DEFAULT_API_URL}/api/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${token}`,
          },
        },
      });
      
      reverbClient.connect();

      // Subscribe to user notifications
      if (authStore.currentUser?.id) {
        reverbClient.subscribePrivate(
          `user.${authStore.currentUser.id}`,
          'notification.created',
          (data: any) => {
            notificationsStore.handleNewNotification(data);
          }
        );
      }
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
