<script lang="ts">
  import * as Card from './index.js';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  
  export let Hst: any;
</script>

<Hst.Story title="Components/Card" icon="lucide:square">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-sm">
      <Card.Root>
        <Card.Header>
          <Card.Title>Card Title</Card.Title>
          <Card.Description>Card description goes here.</Card.Description>
        </Card.Header>
        <Card.Content>
          <p>Card content</p>
        </Card.Content>
        <Card.Footer>
          <Button>Action</Button>
        </Card.Footer>
      </Card.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation Card">
    <div class="p-4 max-w-md">
      <Card.Root class="hover:bg-accent cursor-pointer transition-colors">
        <Card.Header class="pb-2">
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium">
              JD
            </div>
            <div class="flex-1 min-w-0">
              <Card.Title class="text-base">John Doe</Card.Title>
              <Card.Description class="truncate">Last message preview...</Card.Description>
            </div>
            <span class="text-xs text-muted-foreground">2m ago</span>
          </div>
        </Card.Header>
      </Card.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Contact Card">
    <div class="p-4 max-w-sm">
      <Card.Root>
        <Card.Header>
          <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-full bg-primary/20 flex items-center justify-center text-xl font-medium">
              AB
            </div>
            <div>
              <Card.Title>Alice Brown</Card.Title>
              <Card.Description>alice@example.com</Card.Description>
            </div>
          </div>
        </Card.Header>
        <Card.Content>
          <div class="grid gap-2 text-sm">
            <div class="flex justify-between">
              <span class="text-muted-foreground">Phone</span>
              <span>+1 234 567 8900</span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground">Company</span>
              <span>Acme Inc.</span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground">Location</span>
              <span>San Francisco, CA</span>
            </div>
          </div>
        </Card.Content>
        <Card.Footer class="gap-2">
          <Button variant="outline" size="sm" class="flex-1">Edit</Button>
          <Button size="sm" class="flex-1">Message</Button>
        </Card.Footer>
      </Card.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Stats Card">
    <div class="p-4 grid grid-cols-3 gap-4 max-w-2xl">
      <Card.Root>
        <Card.Header class="pb-2">
          <Card.Description>Total Conversations</Card.Description>
          <Card.Title class="text-3xl">1,234</Card.Title>
        </Card.Header>
        <Card.Content>
          <p class="text-xs text-success">↑ 12% from last month</p>
        </Card.Content>
      </Card.Root>
      <Card.Root>
        <Card.Header class="pb-2">
          <Card.Description>Active Agents</Card.Description>
          <Card.Title class="text-3xl">24</Card.Title>
        </Card.Header>
        <Card.Content>
          <p class="text-xs text-muted-foreground">Currently online</p>
        </Card.Content>
      </Card.Root>
      <Card.Root>
        <Card.Header class="pb-2">
          <Card.Description>Response Time</Card.Description>
          <Card.Title class="text-3xl">2.4m</Card.Title>
        </Card.Header>
        <Card.Content>
          <p class="text-xs text-warning">↓ 5% from last week</p>
        </Card.Content>
      </Card.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Login Card">
    <div class="p-4 max-w-sm">
      <Card.Root>
        <Card.Header class="text-center">
          <Card.Title>Welcome back</Card.Title>
          <Card.Description>Enter your credentials to continue</Card.Description>
        </Card.Header>
        <Card.Content class="space-y-4">
          <div class="space-y-2">
            <label class="text-sm font-medium">Email</label>
            <Input type="email" placeholder="email@example.com" />
          </div>
          <div class="space-y-2">
            <label class="text-sm font-medium">Password</label>
            <Input type="password" placeholder="••••••••" />
          </div>
        </Card.Content>
        <Card.Footer>
          <Button class="w-full">Sign In</Button>
        </Card.Footer>
      </Card.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
