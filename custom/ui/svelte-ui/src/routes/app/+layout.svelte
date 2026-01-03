<script lang="ts">
  /**
   * Authenticated App Layout
   * Main application shell with header and sidebar
   */
  
  import type { Snippet } from 'svelte';
  import { onMount } from 'svelte';
  import AppHeader from '$lib/components/layout/AppHeader.svelte';
  import AppSidebar from '$lib/components/layout/AppSidebar.svelte';
  import { authStore } from '$lib/stores/auth.svelte';
  import { WebSocketClient } from '$lib/websocket/client';
  
  interface Props {
    children: Snippet;
  }
  
  let { children }: Props = $props();
  
  // Local state
  let sidebarOpen = $state(true);
  let mobileMenuOpen = $state(false);
  let wsClient: WebSocketClient | null = null;
  
  // Toggle mobile menu
  function toggleMobileMenu() {
    mobileMenuOpen = !mobileMenuOpen;
  }
  
  // Close mobile menu
  function closeMobileMenu() {
    mobileMenuOpen = false;
  }
  
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
    const token = localStorage.getItem('chatwoot_auth_token');
    const wsUrl = import.meta.env.VITE_WS_URL || 'ws://localhost:8000/cable';
    
    if (token) {
      wsClient = new WebSocketClient({
        url: wsUrl,
        token,
      });
      
      wsClient.connect();
    }
    
    // Cleanup on unmount
    return () => {
      if (wsClient) {
        wsClient.disconnect();
      }
    };
  });
</script>

<div class="flex h-screen overflow-hidden bg-background">
  <!-- Sidebar -->
  <AppSidebar
    isOpen={sidebarOpen || mobileMenuOpen}
    onClose={closeMobileMenu}
  />
  
  <!-- Main content area -->
  <div class="flex flex-1 flex-col overflow-hidden">
    <!-- Header -->
    <AppHeader onMobileMenuToggle={toggleMobileMenu} />
    
    <!-- Page content -->
    <main class="flex-1 overflow-y-auto">
      {@render children()}
    </main>
  </div>
</div>
