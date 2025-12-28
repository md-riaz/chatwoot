<script lang="ts">
  import { Separator } from './index.js';
  import { Button } from '../button/index.js';
  
  export let Hst: any;
</script>

<Hst.Story title="Primitives/Separator" icon="lucide:minus">
  <Hst.Variant title="Default (Horizontal)">
    <div class="p-4 max-w-md">
      <div class="space-y-1">
        <h4 class="text-sm font-medium leading-none">Chatwoot Dashboard</h4>
        <p class="text-sm text-muted-foreground">
          Manage your conversations and customers
        </p>
      </div>
      <Separator class="my-4" />
      <div class="flex h-5 items-center space-x-4 text-sm">
        <div>Inbox</div>
        <Separator orientation="vertical" />
        <div>Contacts</div>
        <Separator orientation="vertical" />
        <div>Reports</div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Horizontal">
    <div class="flex flex-col gap-4 p-4">
      <div class="max-w-md space-y-4">
        <div>
          <h3 class="text-base font-semibold">Section One</h3>
          <p class="text-sm text-muted-foreground">Content for the first section</p>
        </div>
        
        <Separator />
        
        <div>
          <h3 class="text-base font-semibold">Section Two</h3>
          <p class="text-sm text-muted-foreground">Content for the second section</p>
        </div>
        
        <Separator />
        
        <div>
          <h3 class="text-base font-semibold">Section Three</h3>
          <p class="text-sm text-muted-foreground">Content for the third section</p>
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Vertical">
    <div class="p-4">
      <div class="flex h-20 items-center space-x-4">
        <div class="flex flex-col justify-center">
          <span class="text-sm font-medium">Home</span>
          <span class="text-xs text-muted-foreground">Dashboard</span>
        </div>
        
        <Separator orientation="vertical" />
        
        <div class="flex flex-col justify-center">
          <span class="text-sm font-medium">Inbox</span>
          <span class="text-xs text-muted-foreground">Messages</span>
        </div>
        
        <Separator orientation="vertical" />
        
        <div class="flex flex-col justify-center">
          <span class="text-sm font-medium">Contacts</span>
          <span class="text-xs text-muted-foreground">Directory</span>
        </div>
        
        <Separator orientation="vertical" />
        
        <div class="flex flex-col justify-center">
          <span class="text-sm font-medium">Reports</span>
          <span class="text-xs text-muted-foreground">Analytics</span>
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="In Navigation">
    <div class="p-4 max-w-md">
      <div class="flex items-center justify-between py-2">
        <div class="flex items-center space-x-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
          <span class="font-semibold">Dashboard</span>
        </div>
      </div>
      
      <Separator class="my-2" />
      
      <nav class="space-y-1">
        <a href="#" class="block px-2 py-1.5 text-sm rounded hover:bg-accent">Conversations</a>
        <a href="#" class="block px-2 py-1.5 text-sm rounded hover:bg-accent">Contacts</a>
        <a href="#" class="block px-2 py-1.5 text-sm rounded hover:bg-accent">Reports</a>
      </nav>
      
      <Separator class="my-2" />
      
      <nav class="space-y-1">
        <a href="#" class="block px-2 py-1.5 text-sm rounded hover:bg-accent">Settings</a>
        <a href="#" class="block px-2 py-1.5 text-sm rounded hover:bg-accent">Help</a>
      </nav>
    </div>
  </Hst.Variant>

  <Hst.Variant title="In Toolbar">
    <div class="p-4">
      <div class="flex items-center space-x-2">
        <Button variant="ghost" size="sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
        </Button>
        <Button variant="ghost" size="sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        </Button>
        
        <Separator orientation="vertical" class="h-6" />
        
        <Button variant="ghost" size="sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </Button>
        
        <Separator orientation="vertical" class="h-6" />
        
        <Button variant="ghost" size="sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
        </Button>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="In List">
    <div class="p-4 max-w-sm">
      <div class="space-y-0">
        <div class="flex items-center justify-between py-3">
          <div>
            <p class="text-sm font-medium">John Doe</p>
            <p class="text-sm text-muted-foreground">john@example.com</p>
          </div>
          <Button variant="ghost" size="sm">View</Button>
        </div>
        
        <Separator />
        
        <div class="flex items-center justify-between py-3">
          <div>
            <p class="text-sm font-medium">Jane Smith</p>
            <p class="text-sm text-muted-foreground">jane@example.com</p>
          </div>
          <Button variant="ghost" size="sm">View</Button>
        </div>
        
        <Separator />
        
        <div class="flex items-center justify-between py-3">
          <div>
            <p class="text-sm font-medium">Bob Johnson</p>
            <p class="text-sm text-muted-foreground">bob@example.com</p>
          </div>
          <Button variant="ghost" size="sm">View</Button>
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Styling">
    <div class="flex flex-col gap-6 p-4">
      <div class="max-w-md space-y-3">
        <p class="text-sm">Default separator</p>
        <Separator />
        
        <p class="text-sm">Separator with custom color</p>
        <Separator class="bg-primary" />
        
        <p class="text-sm">Thicker separator</p>
        <Separator class="h-0.5" />
        
        <p class="text-sm">Even thicker separator</p>
        <Separator class="h-1" />
        
        <p class="text-sm">Dashed separator</p>
        <Separator class="border-t border-dashed border-border" />
        
        <p class="text-sm">Dotted separator</p>
        <Separator class="border-t border-dotted border-border" />
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Text">
    <div class="p-4 max-w-md">
      <div class="space-y-4">
        <div class="flex items-center gap-4">
          <Separator class="flex-1" />
          <span class="text-xs text-muted-foreground">OR</span>
          <Separator class="flex-1" />
        </div>
        
        <div class="flex items-center gap-4">
          <Separator class="flex-1" />
          <span class="text-xs text-muted-foreground">CONTINUE WITH</span>
          <Separator class="flex-1" />
        </div>
        
        <div class="flex items-center gap-4">
          <Separator class="flex-1" />
          <span class="text-sm font-medium">Section Break</span>
          <Separator class="flex-1" />
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Card with Sections">
    <div class="p-4 max-w-md">
      <div class="rounded-lg border p-4">
        <div class="space-y-4">
          <div>
            <h3 class="text-sm font-semibold">Personal Information</h3>
            <p class="text-sm text-muted-foreground mt-1">Update your personal details</p>
          </div>
          
          <Separator />
          
          <div>
            <h3 class="text-sm font-semibold">Account Settings</h3>
            <p class="text-sm text-muted-foreground mt-1">Manage your account preferences</p>
          </div>
          
          <Separator />
          
          <div>
            <h3 class="text-sm font-semibold">Security</h3>
            <p class="text-sm text-muted-foreground mt-1">Configure security options</p>
          </div>
        </div>
      </div>
    </div>
  </Hst.Variant>
</Hst.Story>
