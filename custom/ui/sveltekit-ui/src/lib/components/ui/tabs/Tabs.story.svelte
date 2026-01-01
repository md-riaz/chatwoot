<script lang="ts">
  import * as Tabs from './index.js';
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  
  export let Hst: any;
</script>

<Hst.Story title="Components/Tabs" icon="lucide:layout-list">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-md">
      <Tabs.Root value="account">
        <Tabs.List class="grid w-full grid-cols-2">
          <Tabs.Trigger value="account">Account</Tabs.Trigger>
          <Tabs.Trigger value="password">Password</Tabs.Trigger>
        </Tabs.List>
        <Tabs.Content value="account">
          <Card.Root>
            <Card.Header>
              <Card.Title>Account</Card.Title>
              <Card.Description>
                Make changes to your account here.
              </Card.Description>
            </Card.Header>
            <Card.Content class="space-y-2">
              <div class="space-y-1">
                <Label>Name</Label>
                <Input value="John Doe" />
              </div>
              <div class="space-y-1">
                <Label>Email</Label>
                <Input value="john@example.com" />
              </div>
            </Card.Content>
            <Card.Footer>
              <Button>Save changes</Button>
            </Card.Footer>
          </Card.Root>
        </Tabs.Content>
        <Tabs.Content value="password">
          <Card.Root>
            <Card.Header>
              <Card.Title>Password</Card.Title>
              <Card.Description>
                Change your password here.
              </Card.Description>
            </Card.Header>
            <Card.Content class="space-y-2">
              <div class="space-y-1">
                <Label>Current password</Label>
                <Input type="password" />
              </div>
              <div class="space-y-1">
                <Label>New password</Label>
                <Input type="password" />
              </div>
            </Card.Content>
            <Card.Footer>
              <Button>Update password</Button>
            </Card.Footer>
          </Card.Root>
        </Tabs.Content>
      </Tabs.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Three Tabs">
    <div class="p-4 max-w-lg">
      <Tabs.Root value="overview">
        <Tabs.List class="grid w-full grid-cols-3">
          <Tabs.Trigger value="overview">Overview</Tabs.Trigger>
          <Tabs.Trigger value="analytics">Analytics</Tabs.Trigger>
          <Tabs.Trigger value="settings">Settings</Tabs.Trigger>
        </Tabs.List>
        <Tabs.Content value="overview" class="p-4 border rounded-lg mt-4">
          <h3 class="text-lg font-medium">Dashboard Overview</h3>
          <p class="text-sm text-muted-foreground mt-2">
            View your key metrics and recent activity at a glance.
          </p>
        </Tabs.Content>
        <Tabs.Content value="analytics" class="p-4 border rounded-lg mt-4">
          <h3 class="text-lg font-medium">Analytics</h3>
          <p class="text-sm text-muted-foreground mt-2">
            Deep dive into your conversation and response metrics.
          </p>
        </Tabs.Content>
        <Tabs.Content value="settings" class="p-4 border rounded-lg mt-4">
          <h3 class="text-lg font-medium">Settings</h3>
          <p class="text-sm text-muted-foreground mt-2">
            Configure your account and notification preferences.
          </p>
        </Tabs.Content>
      </Tabs.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Inbox Tabs">
    <div class="p-4 max-w-xl">
      <Tabs.Root value="all">
        <Tabs.List>
          <Tabs.Trigger value="all">All</Tabs.Trigger>
          <Tabs.Trigger value="mine">Mine</Tabs.Trigger>
          <Tabs.Trigger value="unassigned">Unassigned</Tabs.Trigger>
          <Tabs.Trigger value="mentions">Mentions</Tabs.Trigger>
        </Tabs.List>
        <Tabs.Content value="all" class="pt-4">
          <div class="space-y-2">
            <div class="p-3 border rounded-lg flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium">JD</div>
              <div class="flex-1">
                <p class="font-medium">John Doe</p>
                <p class="text-sm text-muted-foreground">Latest message preview...</p>
              </div>
              <span class="text-xs text-muted-foreground">2m ago</span>
            </div>
            <div class="p-3 border rounded-lg flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-success/20 flex items-center justify-center text-sm font-medium">AS</div>
              <div class="flex-1">
                <p class="font-medium">Alice Smith</p>
                <p class="text-sm text-muted-foreground">Thanks for your help!</p>
              </div>
              <span class="text-xs text-muted-foreground">1h ago</span>
            </div>
          </div>
        </Tabs.Content>
        <Tabs.Content value="mine" class="pt-4">
          <p class="text-sm text-muted-foreground">Your assigned conversations will appear here.</p>
        </Tabs.Content>
        <Tabs.Content value="unassigned" class="pt-4">
          <p class="text-sm text-muted-foreground">Unassigned conversations will appear here.</p>
        </Tabs.Content>
        <Tabs.Content value="mentions" class="pt-4">
          <p class="text-sm text-muted-foreground">Conversations where you are mentioned.</p>
        </Tabs.Content>
      </Tabs.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
