<script lang="ts">
  import * as Table from './index.js';
  import { Badge } from '$lib/components/ui/badge';
  import { Button } from '$lib/components/ui/button';
  import { Checkbox } from '$lib/components/ui/checkbox';
  
  export let Hst: any;
  
  const conversations = [
    { id: 1, contact: 'John Doe', email: 'john@example.com', subject: 'Order inquiry', status: 'open', agent: 'Alice', created: '2 hours ago' },
    { id: 2, contact: 'Jane Smith', email: 'jane@example.com', subject: 'Refund request', status: 'pending', agent: 'Bob', created: '5 hours ago' },
    { id: 3, contact: 'Bob Wilson', email: 'bob@example.com', subject: 'Product feedback', status: 'resolved', agent: 'Alice', created: '1 day ago' },
    { id: 4, contact: 'Alice Brown', email: 'alice@example.com', subject: 'Technical issue', status: 'open', agent: 'Charlie', created: '2 days ago' },
    { id: 5, contact: 'Charlie Davis', email: 'charlie@example.com', subject: 'Billing question', status: 'pending', agent: 'Bob', created: '3 days ago' }
  ];
  
  const agents = [
    { name: 'Alice', email: 'alice@team.com', role: 'Agent', conversations: 45, status: 'online' },
    { name: 'Bob', email: 'bob@team.com', role: 'Agent', conversations: 38, status: 'away' },
    { name: 'Charlie', email: 'charlie@team.com', role: 'Admin', conversations: 62, status: 'online' },
    { name: 'Diana', email: 'diana@team.com', role: 'Agent', conversations: 29, status: 'offline' }
  ];
</script>

<Hst.Story title="Components/Table" icon="lucide:table-2">
  <Hst.Variant title="Default">
    <div class="p-4">
      <Table.Root>
        <Table.Caption>A list of recent conversations.</Table.Caption>
        <Table.Header>
          <Table.Row>
            <Table.Head class="w-[100px]">ID</Table.Head>
            <Table.Head>Contact</Table.Head>
            <Table.Head>Subject</Table.Head>
            <Table.Head>Status</Table.Head>
            <Table.Head>Agent</Table.Head>
            <Table.Head class="text-right">Created</Table.Head>
          </Table.Row>
        </Table.Header>
        <Table.Body>
          {#each conversations as conv}
            <Table.Row>
              <Table.Cell class="font-medium">#{conv.id}</Table.Cell>
              <Table.Cell>
                <div>
                  <p class="font-medium">{conv.contact}</p>
                  <p class="text-xs text-muted-foreground">{conv.email}</p>
                </div>
              </Table.Cell>
              <Table.Cell>{conv.subject}</Table.Cell>
              <Table.Cell>
                {#if conv.status === 'open'}
                  <Badge variant="success">Open</Badge>
                {:else if conv.status === 'pending'}
                  <Badge variant="warning">Pending</Badge>
                {:else}
                  <Badge variant="secondary">Resolved</Badge>
                {/if}
              </Table.Cell>
              <Table.Cell>{conv.agent}</Table.Cell>
              <Table.Cell class="text-right">{conv.created}</Table.Cell>
            </Table.Row>
          {/each}
        </Table.Body>
      </Table.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Checkbox">
    <div class="p-4">
      <Table.Root>
        <Table.Header>
          <Table.Row>
            <Table.Head class="w-[50px]">
              <Checkbox />
            </Table.Head>
            <Table.Head>Contact</Table.Head>
            <Table.Head>Subject</Table.Head>
            <Table.Head>Status</Table.Head>
            <Table.Head class="w-[100px]">Actions</Table.Head>
          </Table.Row>
        </Table.Header>
        <Table.Body>
          {#each conversations.slice(0, 3) as conv}
            <Table.Row>
              <Table.Cell>
                <Checkbox />
              </Table.Cell>
              <Table.Cell class="font-medium">{conv.contact}</Table.Cell>
              <Table.Cell>{conv.subject}</Table.Cell>
              <Table.Cell>
                {#if conv.status === 'open'}
                  <Badge variant="success">Open</Badge>
                {:else if conv.status === 'pending'}
                  <Badge variant="warning">Pending</Badge>
                {:else}
                  <Badge variant="secondary">Resolved</Badge>
                {/if}
              </Table.Cell>
              <Table.Cell>
                <Button variant="ghost" size="sm">View</Button>
              </Table.Cell>
            </Table.Row>
          {/each}
        </Table.Body>
      </Table.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Agents Table">
    <div class="p-4">
      <Table.Root>
        <Table.Header>
          <Table.Row>
            <Table.Head>Agent</Table.Head>
            <Table.Head>Role</Table.Head>
            <Table.Head>Conversations</Table.Head>
            <Table.Head>Status</Table.Head>
            <Table.Head class="text-right">Actions</Table.Head>
          </Table.Row>
        </Table.Header>
        <Table.Body>
          {#each agents as agent}
            <Table.Row>
              <Table.Cell>
                <div class="flex items-center gap-3">
                  <div class="h-8 w-8 rounded-full bg-muted flex items-center justify-center text-sm font-medium">
                    {agent.name[0]}
                  </div>
                  <div>
                    <p class="font-medium">{agent.name}</p>
                    <p class="text-xs text-muted-foreground">{agent.email}</p>
                  </div>
                </div>
              </Table.Cell>
              <Table.Cell>{agent.role}</Table.Cell>
              <Table.Cell>{agent.conversations}</Table.Cell>
              <Table.Cell>
                <div class="flex items-center gap-2">
                  <span class={`h-2 w-2 rounded-full ${
                    agent.status === 'online' ? 'bg-green-500' :
                    agent.status === 'away' ? 'bg-yellow-500' : 'bg-gray-400'
                  }`}></span>
                  <span class="text-sm capitalize">{agent.status}</span>
                </div>
              </Table.Cell>
              <Table.Cell class="text-right">
                <Button variant="ghost" size="sm">Edit</Button>
              </Table.Cell>
            </Table.Row>
          {/each}
        </Table.Body>
        <Table.Footer>
          <Table.Row>
            <Table.Cell colspan="2">Total Agents</Table.Cell>
            <Table.Cell>{agents.reduce((sum, a) => sum + a.conversations, 0)} conversations</Table.Cell>
            <Table.Cell colspan="2"></Table.Cell>
          </Table.Row>
        </Table.Footer>
      </Table.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
