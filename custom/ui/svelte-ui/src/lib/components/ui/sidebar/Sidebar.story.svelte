<script lang="ts">
  import * as Sidebar from './index.js';
  import { Button } from '$lib/components/ui/button';
  import * as DropdownMenu from '$lib/components/ui/dropdown-menu';
  
  export let Hst: any;
  
  let collapsed = $state(false);
  let activeItem = $state('conversations');
  
  const inboxes = [
    { id: 'email', name: 'Email', count: 12 },
    { id: 'website', name: 'Website Chat', count: 5 },
    { id: 'whatsapp', name: 'WhatsApp', count: 3 },
    { id: 'facebook', name: 'Facebook', count: 0 }
  ];
</script>

<Hst.Story title="Chatwoot/Sidebar" icon="lucide:layout-sidebar-left">
  <Hst.Variant title="Default">
    <div class="h-[600px] flex bg-background">
      <Sidebar.Root>
        <Sidebar.Header>
          <div class="flex items-center gap-2">
            <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold">
              C
            </div>
            <span class="font-semibold">Chatwoot</span>
          </div>
        </Sidebar.Header>
        
        <Sidebar.Nav>
          <Sidebar.Section>
            <Sidebar.NavItem active={activeItem === 'conversations'} onclick={() => activeItem = 'conversations'} badge={20}>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              {/snippet}
              Conversations
            </Sidebar.NavItem>
            <Sidebar.NavItem active={activeItem === 'contacts'} onclick={() => activeItem = 'contacts'}>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              {/snippet}
              Contacts
            </Sidebar.NavItem>
            <Sidebar.NavItem active={activeItem === 'reports'} onclick={() => activeItem = 'reports'}>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              {/snippet}
              Reports
            </Sidebar.NavItem>
          </Sidebar.Section>
          
          <Sidebar.Section title="Inboxes">
            {#each inboxes as inbox}
              <Sidebar.NavItem 
                active={activeItem === inbox.id} 
                onclick={() => activeItem = inbox.id}
                badge={inbox.count || undefined}
              >
                {#snippet icon()}
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                {/snippet}
                {inbox.name}
              </Sidebar.NavItem>
            {/each}
          </Sidebar.Section>
        </Sidebar.Nav>
        
        <Sidebar.Footer>
          <div class="flex items-center gap-3 flex-1 px-2">
            <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center text-sm font-medium">JD</div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium truncate">John Doe</p>
              <p class="text-xs text-muted-foreground truncate">Agent</p>
            </div>
            <Button variant="ghost" size="icon" class="h-8 w-8">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </Button>
          </div>
        </Sidebar.Footer>
      </Sidebar.Root>
      
      <div class="flex-1 p-4">
        <p class="text-muted-foreground">Main content area</p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Collapsed">
    <div class="h-[600px] flex bg-background">
      <Sidebar.Root collapsed>
        <Sidebar.Header collapsed>
          <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold">
            C
          </div>
        </Sidebar.Header>
        
        <Sidebar.Nav>
          <Sidebar.Section collapsed>
            <Sidebar.NavItem collapsed active>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              {/snippet}
            </Sidebar.NavItem>
            <Sidebar.NavItem collapsed>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              {/snippet}
            </Sidebar.NavItem>
            <Sidebar.NavItem collapsed>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
              {/snippet}
            </Sidebar.NavItem>
          </Sidebar.Section>
        </Sidebar.Nav>
        
        <Sidebar.Footer>
          <div class="flex items-center justify-center flex-1">
            <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center text-sm font-medium">JD</div>
          </div>
        </Sidebar.Footer>
      </Sidebar.Root>
      
      <div class="flex-1 p-4">
        <p class="text-muted-foreground">Collapsed sidebar mode</p>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Toggle Collapse">
    <div class="h-[600px] flex bg-background">
      <Sidebar.Root {collapsed}>
        <Sidebar.Header {collapsed}>
          {#if !collapsed}
            <div class="flex items-center gap-2 flex-1">
              <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold">
                C
              </div>
              <span class="font-semibold">Chatwoot</span>
            </div>
          {:else}
            <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold">
              C
            </div>
          {/if}
        </Sidebar.Header>
        
        <Sidebar.Nav>
          <Sidebar.Section {collapsed}>
            <Sidebar.NavItem {collapsed} active>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              {/snippet}
              Conversations
            </Sidebar.NavItem>
            <Sidebar.NavItem {collapsed}>
              {#snippet icon()}
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              {/snippet}
              Contacts
            </Sidebar.NavItem>
          </Sidebar.Section>
        </Sidebar.Nav>
        
        <Sidebar.Footer>
          <Button variant="ghost" size="icon" class="h-8 w-8 ml-auto" onclick={() => collapsed = !collapsed}>
            {#if collapsed}
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            {:else}
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            {/if}
          </Button>
        </Sidebar.Footer>
      </Sidebar.Root>
      
      <div class="flex-1 p-4">
        <p class="text-muted-foreground">Click the arrow in the sidebar footer to toggle collapse</p>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
