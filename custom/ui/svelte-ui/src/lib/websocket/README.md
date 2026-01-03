# WebSocket Client

Native WebSocket client replacing Rails ActionCable with full support for real-time communication in Chatwoot.

## Features

- ✅ **Native WebSocket** - No external dependencies
- ✅ **Automatic Reconnection** - Exponential backoff strategy
- ✅ **Channel Subscriptions** - Multiple channel support
- ✅ **Heartbeat/Ping-Pong** - Connection health monitoring
- ✅ **Connection State** - Reactive state using Svelte 5 runes
- ✅ **Type Safe** - Full TypeScript support
- ✅ **Error Handling** - Comprehensive error management

## Installation

The WebSocket client is already integrated. Import from `$lib/websocket`.

## Basic Usage

### Initialize and Connect

```typescript
import { getWebSocketClient } from '$lib/websocket/client';

// Get WebSocket URL and token (usually from environment/auth)
const wsUrl = import.meta.env.VITE_WS_URL; // ws://localhost:3000/cable
const token = localStorage.getItem('authToken');

// Initialize client
const ws = getWebSocketClient({
  url: wsUrl,
  token: token,
});

// Connect
ws.connect();
```

### Subscribe to Channels

```typescript
// Subscribe to conversations
const unsubscribe = ws.subscribe('conversations', (data) => {
  console.log('New conversation event:', data);
  
  // Handle different event types
  switch (data.type) {
    case 'message.created':
      // Handle new message
      break;
    case 'conversation.status_changed':
      // Handle status change
      break;
    // ... more events
  }
});

// Cleanup when component unmounts
onDestroy(() => {
  unsubscribe();
});
```

### Using Channel Helpers

```typescript
import { subscribeToConversations, subscribeToNotifications } from '$lib/websocket/channels';

// Subscribe to conversations for current account
const unsubConversations = subscribeToConversations(accountId, (data) => {
  console.log('Conversation event:', data);
});

// Subscribe to notifications
const unsubNotifications = subscribeToNotifications(accountId, userId, (data) => {
  console.log('Notification event:', data);
});
```

## Connection State

Access connection state using the reactive store:

```svelte
<script>
  import { getWebSocketClient } from '$lib/websocket/client';
  
  const ws = getWebSocketClient();
  const { connectionState } = ws;
</script>

<div>
  <p>Status: {connectionState.state}</p>
  
  {#if connectionState.isConnecting}
    <span>Connecting...</span>
  {:else if connectionState.isConnected}
    <span>✓ Connected</span>
  {:else if connectionState.hasError}
    <span>⚠ Error: {connectionState.error}</span>
  {/if}
  
  {#if connectionState.reconnectAttempts > 0}
    <span>Reconnect attempts: {connectionState.reconnectAttempts}</span>
  {/if}
</div>
```

## Event Types

### Conversation Events

- `message.created` - New message in conversation
- `message.updated` - Message updated/edited
- `conversation.created` - New conversation created
- `conversation.status_changed` - Status changed (open, resolved, pending)
- `conversation.updated` - Conversation metadata updated
- `assignee.changed` - Assignee changed
- `conversation.typing_on` - User started typing
- `conversation.typing_off` - User stopped typing
- `conversation.read` - Conversation marked as read
- `conversation.contact_changed` - Contact changed
- `conversation.mentioned` - User mentioned in conversation

### Notification Events

- `notification.created` - New notification
- `notification.updated` - Notification updated
- `notification.deleted` - Notification deleted

### Contact Events

- `contact.created` - New contact
- `contact.updated` - Contact updated
- `contact.deleted` - Contact deleted

### Presence Events

- `presence.update` - Agent/contact presence changed

### System Events

- `user:logout` - Force logout
- `page:reload` - Force page reload
- `account.cache_invalidated` - Cache invalidation

## Advanced Usage

### Send Messages

```typescript
import { sendTypingIndicator } from '$lib/websocket/channels';

// Notify that user is typing
sendTypingIndicator(conversationId, true);

// Stop typing notification after 3 seconds
setTimeout(() => {
  sendTypingIndicator(conversationId, false);
}, 3000);
```

### Custom Channel

```typescript
// Subscribe to custom channel
const unsubscribe = ws.subscribe('custom:channel:123', (data) => {
  console.log('Custom event:', data);
});

// Send to custom channel
ws.send('custom:channel:123', {
  action: 'update',
  payload: { /* data */ }
});
```

### Connection Options

```typescript
const ws = getWebSocketClient({
  url: 'ws://localhost:3000/cable',
  token: 'auth-token',
  reconnectAttempts: 10,        // Max reconnection attempts (default: 10)
  reconnectDelay: 1000,         // Initial delay in ms (default: 1000)
  maxReconnectDelay: 30000,     // Max delay in ms (default: 30000)
  heartbeatInterval: 30000,     // Ping interval in ms (default: 30000)
  heartbeatTimeout: 5000,       // Pong timeout in ms (default: 5000)
});
```

## Reconnection Strategy

The client uses exponential backoff for reconnection:

1. **1st attempt**: 1 second delay
2. **2nd attempt**: 2 seconds delay
3. **3rd attempt**: 4 seconds delay
4. **4th attempt**: 8 seconds delay
5. **5th attempt**: 16 seconds delay
6. **6th+ attempts**: 30 seconds delay (capped)

After 10 failed attempts, connection state becomes `'failed'`.

## Heartbeat

- **Ping sent**: Every 30 seconds (configurable)
- **Pong expected**: Within 5 seconds (configurable)
- **No pong received**: Triggers reconnection

## In Component

```svelte
<script lang="ts">
  import { onMount, onDestroy } from 'svelte';
  import { getWebSocketClient } from '$lib/websocket/client';
  import { subscribeToConversations } from '$lib/websocket/channels';
  
  let accountId = $state(1);
  let messages = $state<any[]>([]);
  let unsubscribe: (() => void) | null = null;
  
  onMount(() => {
    // Initialize WebSocket
    const ws = getWebSocketClient({
      url: import.meta.env.VITE_WS_URL,
      token: localStorage.getItem('authToken') || '',
    });
    ws.connect();
    
    // Subscribe to conversations
    unsubscribe = subscribeToConversations(accountId, (data) => {
      if (data.type === 'message.created') {
        messages = [...messages, data];
      }
    });
  });
  
  onDestroy(() => {
    // Cleanup subscription
    unsubscribe?.();
    
    // Optionally disconnect
    // const ws = getWebSocketClient();
    // ws.disconnect();
  });
</script>

<div>
  <h2>Real-time Messages</h2>
  {#each messages as message}
    <div>{message.content}</div>
  {/each}
</div>
```

## Testing

```typescript
import { getWebSocketClient, resetWebSocketClient } from '$lib/websocket/client';

// Before each test
beforeEach(() => {
  resetWebSocketClient();
});

// Test
test('connects successfully', () => {
  const ws = getWebSocketClient({
    url: 'ws://localhost:3000/cable',
    token: 'test-token',
  });
  
  ws.connect();
  
  expect(ws.connectionState.state).toBe('connecting');
});
```

## Migration from ActionCable

### Before (Vue + ActionCable)

```javascript
import ActionCable from '@rails/actioncable';

const cable = ActionCable.createConsumer(websocketURL);
const subscription = cable.subscriptions.create('ConversationsChannel', {
  received(data) {
    console.log(data);
  }
});

subscription.unsubscribe();
```

### After (Svelte + Native WebSocket)

```typescript
import { getWebSocketClient } from '$lib/websocket/client';

const ws = getWebSocketClient({ url: websocketURL, token });
ws.connect();

const unsubscribe = ws.subscribe('conversations', (data) => {
  console.log(data);
});

unsubscribe();
```

## Benefits

- **No external dependency** - Uses native WebSocket API
- **Smaller bundle size** - No ActionCable library needed
- **Better TypeScript support** - Fully typed
- **Svelte 5 runes integration** - Reactive state management
- **Automatic reconnection** - Built-in with exponential backoff
- **Better error handling** - Comprehensive error states

## Connection States

- `disconnected` - Not connected
- `connecting` - Initial connection attempt
- `connected` - Successfully connected
- `reconnecting` - Attempting to reconnect
- `failed` - Max reconnection attempts reached

## Troubleshooting

### Connection fails immediately

- Check WebSocket URL is correct
- Verify authentication token is valid
- Check network/firewall settings

### Frequent disconnections

- Check heartbeat interval settings
- Verify server supports ping/pong
- Check network stability

### Messages not received

- Verify channel subscription is active
- Check event type matches expected format
- Ensure callback is properly registered

## Best Practices

1. **Initialize once** - Use singleton pattern (already implemented)
2. **Cleanup subscriptions** - Always unsubscribe in `onDestroy`
3. **Handle connection state** - Show UI indicators for connection status
4. **Error handling** - Implement fallbacks for failed connections
5. **Testing** - Use `resetWebSocketClient()` between tests
