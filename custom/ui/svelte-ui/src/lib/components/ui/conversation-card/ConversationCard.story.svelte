<script lang="ts">
  import * as ConversationCard from './index.js';
  import { Badge } from '$lib/components/ui/badge';
  
  export let Hst: any;
  
  const conversations = [
    {
      id: 1,
      name: 'John Doe',
      initials: 'JD',
      message: 'Hello, I need help with my order #12345',
      time: '2m ago',
      unread: true,
      unreadCount: 3,
      status: 'open',
      inbox: 'email'
    },
    {
      id: 2,
      name: 'Jane Smith',
      initials: 'JS',
      message: 'Thank you for your help!',
      time: '15m ago',
      unread: false,
      unreadCount: 0,
      status: 'resolved',
      inbox: 'website'
    },
    {
      id: 3,
      name: 'Bob Wilson',
      initials: 'BW',
      message: 'When will my refund be processed?',
      time: '1h ago',
      unread: true,
      unreadCount: 1,
      status: 'pending',
      inbox: 'whatsapp'
    },
    {
      id: 4,
      name: 'Alice Brown',
      initials: 'AB',
      message: 'I love your product!',
      time: '2h ago',
      unread: false,
      unreadCount: 0,
      status: 'open',
      inbox: 'facebook'
    }
  ];
  
  let selected = $state(1);
</script>

<Hst.Story title="Chatwoot/ConversationCard" icon="lucide:message-square-text">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-sm bg-background border rounded-lg">
      <ConversationCard.Root>
        <ConversationCard.Header name="John Doe" avatarFallback="JD">
          <div class="flex items-center gap-2">
            <span class="font-medium">John Doe</span>
            <Badge variant="success" class="text-xs h-5">Open</Badge>
          </div>
        </ConversationCard.Header>
        <ConversationCard.Preview message="Hello, I need help with my order..." />
        <ConversationCard.Meta time="2m ago" unreadCount={3} />
      </ConversationCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Selected">
    <div class="p-4 max-w-sm bg-background border rounded-lg">
      <ConversationCard.Root selected>
        <ConversationCard.Header name="Jane Smith" avatarFallback="JS">
          <span class="font-medium">Jane Smith</span>
        </ConversationCard.Header>
        <ConversationCard.Preview message="Thank you for your help!" />
        <ConversationCard.Meta time="15m ago" />
      </ConversationCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Unread">
    <div class="p-4 max-w-sm bg-background border rounded-lg">
      <ConversationCard.Root unread>
        <ConversationCard.Header name="Bob Wilson" avatarFallback="BW">
          <span class="font-medium">Bob Wilson</span>
        </ConversationCard.Header>
        <ConversationCard.Preview message="When will my refund be processed?" unread />
        <ConversationCard.Meta time="1h ago" unreadCount={5} />
      </ConversationCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Inbox Icon">
    <div class="p-4 max-w-sm bg-background border rounded-lg space-y-1 divide-y">
      <ConversationCard.Root>
        <ConversationCard.Header name="Email User" avatarFallback="EU">
          {#snippet inboxIcon()}
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
          {/snippet}
          <span class="font-medium">Email User</span>
        </ConversationCard.Header>
        <ConversationCard.Preview message="Message via email..." />
        <ConversationCard.Meta time="5m ago" />
      </ConversationCard.Root>
      
      <ConversationCard.Root>
        <ConversationCard.Header name="WhatsApp User" avatarFallback="WU">
          {#snippet inboxIcon()}
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" class="text-green-500"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          {/snippet}
          <span class="font-medium">WhatsApp User</span>
        </ConversationCard.Header>
        <ConversationCard.Preview message="Message via WhatsApp..." />
        <ConversationCard.Meta time="10m ago" />
      </ConversationCard.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation List">
    <div class="w-80 bg-background border rounded-lg divide-y">
      {#each conversations as conv}
        <ConversationCard.Root
          selected={selected === conv.id}
          unread={conv.unread}
          onclick={() => (selected = conv.id)}
        >
          <ConversationCard.Header name={conv.name} avatarFallback={conv.initials}>
            <div class="flex items-center gap-2">
              <span class="font-medium truncate">{conv.name}</span>
              {#if conv.status === 'open'}
                <Badge variant="success" class="text-xs h-5">Open</Badge>
              {:else if conv.status === 'pending'}
                <Badge variant="warning" class="text-xs h-5">Pending</Badge>
              {:else if conv.status === 'resolved'}
                <Badge variant="secondary" class="text-xs h-5">Resolved</Badge>
              {/if}
            </div>
          </ConversationCard.Header>
          <ConversationCard.Preview message={conv.message} unread={conv.unread} />
          <ConversationCard.Meta time={conv.time} unreadCount={conv.unreadCount} />
        </ConversationCard.Root>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Labels">
    <div class="p-4 max-w-sm bg-background border rounded-lg">
      <ConversationCard.Root>
        <ConversationCard.Header name="Priority Customer" avatarFallback="PC">
          <div class="flex items-center gap-2">
            <span class="font-medium">Priority Customer</span>
          </div>
        </ConversationCard.Header>
        <ConversationCard.Preview message="I need urgent assistance..." />
        <div class="flex items-center gap-1 mt-1">
          <Badge variant="destructive" class="text-xs h-5">Urgent</Badge>
          <Badge variant="outline" class="text-xs h-5">VIP</Badge>
          <Badge class="text-xs h-5 bg-purple-500 text-white border-transparent">Premium</Badge>
        </div>
        <ConversationCard.Meta time="Just now" unreadCount={1} />
      </ConversationCard.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
