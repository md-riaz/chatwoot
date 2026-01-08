<script lang="ts">
  import * as ContactCard from './index.js';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Separator } from '$lib/components/ui/separator';
  
  export let Hst: any;
  
  const contacts = [
    {
      id: 1,
      name: 'John Doe',
      email: 'john.doe@example.com',
      phone: '+1 (555) 123-4567',
      company: 'Acme Inc.',
      location: 'San Francisco, CA',
      status: 'online'
    },
    {
      id: 2,
      name: 'Jane Smith',
      email: 'jane.smith@company.com',
      phone: '+1 (555) 987-6543',
      company: 'Tech Corp',
      location: 'New York, NY',
      status: 'away'
    },
    {
      id: 3,
      name: 'Bob Wilson',
      email: 'bob.wilson@business.org',
      phone: '+44 20 1234 5678',
      company: 'Global Solutions',
      location: 'London, UK',
      status: 'offline'
    }
  ];
</script>

<Hst.Story title="Chatwoot/ContactCard" icon="lucide:contact">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-sm">
      <ContactCard.Root>
        <ContactCard.Header
          name="John Doe"
          email="john.doe@example.com"
          avatarFallback="JD"
          status="online"
        />
        <ContactCard.Details>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-muted-foreground">Phone</span>
              <span>+1 (555) 123-4567</span>
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
        </ContactCard.Details>
        <ContactCard.Actions>
          <Button variant="outline" size="sm" class="flex-1">Edit</Button>
          <Button size="sm" class="flex-1">Message</Button>
        </ContactCard.Actions>
      </ContactCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Compact">
    <div class="p-4 max-w-sm">
      <ContactCard.Root variant="compact">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium">
            JS
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium truncate">Jane Smith</p>
            <p class="text-sm text-muted-foreground truncate">jane.smith@company.com</p>
          </div>
          <Button variant="ghost" size="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </Button>
        </div>
      </ContactCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Status">
    <div class="p-4 max-w-xs space-y-3">
      <ContactCard.Root variant="compact">
        <ContactCard.Header name="Online User" avatarFallback="OU" status="online" />
      </ContactCard.Root>
      <ContactCard.Root variant="compact">
        <ContactCard.Header name="Away User" avatarFallback="AU" status="away" />
      </ContactCard.Root>
      <ContactCard.Root variant="compact">
        <ContactCard.Header name="Busy User" avatarFallback="BU" status="busy" />
      </ContactCard.Root>
      <ContactCard.Root variant="compact">
        <ContactCard.Header name="Offline User" avatarFallback="OF" status="offline" />
      </ContactCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Tags">
    <div class="p-4 max-w-sm">
      <ContactCard.Root>
        <ContactCard.Header
          name="VIP Customer"
          email="vip@important.com"
          avatarFallback="VC"
          status="online"
        >
          <div>
            <p class="font-semibold text-lg">VIP Customer</p>
            <p class="text-sm text-muted-foreground">vip@important.com</p>
            <div class="flex gap-1 mt-2">
              <Badge class="bg-purple-500 text-white border-transparent">VIP</Badge>
              <Badge variant="success">Premium</Badge>
              <Badge variant="outline">Enterprise</Badge>
            </div>
          </div>
        </ContactCard.Header>
        <Separator class="my-4" />
        <ContactCard.Details>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-muted-foreground">Lifetime Value</span>
              <span class="font-medium text-success">$15,000</span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground">Conversations</span>
              <span>42</span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground">Last Active</span>
              <span>2 hours ago</span>
            </div>
          </div>
        </ContactCard.Details>
        <ContactCard.Actions>
          <Button variant="outline" size="sm" class="flex-1">View Profile</Button>
          <Button size="sm" class="flex-1">Start Conversation</Button>
        </ContactCard.Actions>
      </ContactCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Contact List">
    <div class="w-80 border rounded-lg divide-y">
      {#each contacts as contact}
        <div class="p-3 hover:bg-accent cursor-pointer transition-colors">
          <div class="flex items-center gap-3">
            <div class="relative">
              <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium">
                {contact.name.split(' ').map(n => n[0]).join('')}
              </div>
              <span class={`absolute bottom-0 right-0 h-3 w-3 rounded-full ring-2 ring-background ${
                contact.status === 'online' ? 'bg-green-500' :
                contact.status === 'away' ? 'bg-yellow-500' : 'bg-gray-400'
              }`}></span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium truncate">{contact.name}</p>
              <p class="text-sm text-muted-foreground truncate">{contact.email}</p>
            </div>
            <div class="text-right text-xs text-muted-foreground">
              <p>{contact.company}</p>
            </div>
          </div>
        </div>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Custom Attributes">
    <div class="p-4 max-w-md">
      <ContactCard.Root>
        <ContactCard.Header
          name="Enterprise Customer"
          email="enterprise@bigcorp.com"
          avatarFallback="EC"
        />
        <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
          <div class="p-3 rounded-lg bg-muted/50">
            <p class="text-muted-foreground text-xs">Plan</p>
            <p class="font-medium">Enterprise</p>
          </div>
          <div class="p-3 rounded-lg bg-muted/50">
            <p class="text-muted-foreground text-xs">Seats</p>
            <p class="font-medium">250</p>
          </div>
          <div class="p-3 rounded-lg bg-muted/50">
            <p class="text-muted-foreground text-xs">MRR</p>
            <p class="font-medium text-success">$2,500</p>
          </div>
          <div class="p-3 rounded-lg bg-muted/50">
            <p class="text-muted-foreground text-xs">CSAT</p>
            <p class="font-medium">4.8/5</p>
          </div>
        </div>
        <ContactCard.Actions>
          <Button variant="outline" size="sm">View Details</Button>
          <Button variant="outline" size="sm">Edit</Button>
          <Button size="sm">Message</Button>
        </ContactCard.Actions>
      </ContactCard.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
