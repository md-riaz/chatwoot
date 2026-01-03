<script lang="ts">
  /**
   * App Home Page
   * Dashboard home page showing overview
   */
  
  import { authStore } from '$lib/stores/auth.svelte';
  import { Card } from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  
  const currentUser = $derived(authStore.currentUser);
  const currentAccount = $derived(authStore.currentAccount);
</script>

<div class="container py-8">
  <div class="mb-8">
    <h1 class="text-3xl font-bold tracking-tight">
      Welcome back, {currentUser?.name || 'User'}!
    </h1>
    <p class="text-muted-foreground">
      Here's what's happening with your conversations today.
    </p>
  </div>
  
  {#if currentAccount}
    <div class="mb-6">
      <Card.Root class="p-6">
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <h2 class="text-xl font-semibold">{currentAccount.name}</h2>
            <p class="text-sm text-muted-foreground">
              Role: <Badge variant="secondary">{currentAccount.role}</Badge>
            </p>
          </div>
        </div>
      </Card.Root>
    </div>
  {/if}
  
  <!-- Stats Grid -->
  <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
    <Card.Root>
      <Card.Header>
        <Card.Title class="text-sm font-medium">Total Conversations</Card.Title>
      </Card.Header>
      <Card.Content>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">No conversations yet</p>
      </Card.Content>
    </Card.Root>
    
    <Card.Root>
      <Card.Header>
        <Card.Title class="text-sm font-medium">Open Conversations</Card.Title>
      </Card.Header>
      <Card.Content>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">All caught up!</p>
      </Card.Content>
    </Card.Root>
    
    <Card.Root>
      <Card.Header>
        <Card.Title class="text-sm font-medium">Unassigned</Card.Title>
      </Card.Header>
      <Card.Content>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">Nothing pending</p>
      </Card.Content>
    </Card.Root>
    
    <Card.Root>
      <Card.Header>
        <Card.Title class="text-sm font-medium">Contacts</Card.Title>
      </Card.Header>
      <Card.Content>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">Start adding contacts</p>
      </Card.Content>
    </Card.Root>
  </div>
</div>
