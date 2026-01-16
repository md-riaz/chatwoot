<script lang="ts">
  /**
   * Authenticated App Layout
   * Main application shell with header and sidebar
   */
  
  import AppHeader from '$lib/components/layout/AppHeader.svelte';
  import AppSidebar from '$lib/components/layout/AppSidebar.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { ReverbClient, getReverbClient } from '$lib/websocket/reverb-client';
  import * as Sidebar from '$lib/components/ui/sidebar/index.js';
  import type { Snippet } from 'svelte';
  import { onMount } from 'svelte';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
  
  // Local state
  let reverbClient: ReverbClient | null = null;
  
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
    }
  });
</script>

<Sidebar.Provider>
  <AppSidebar />
  <Sidebar.Inset class="h-svh overflow-hidden">
    <AppHeader />
    <main class="flex-1 overflow-y-auto">
      {@render children()}
    </main>
  </Sidebar.Inset>
</Sidebar.Provider>
