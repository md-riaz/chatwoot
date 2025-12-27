<script lang="ts">
  import { ScrollArea } from './index.js';
  import { Badge } from '$lib/components/ui/badge';
  
  export let Hst: any;
  
  const conversations = Array.from({ length: 20 }, (_, i) => ({
    id: i + 1,
    name: ['John Doe', 'Jane Smith', 'Bob Wilson', 'Alice Brown', 'Charlie Davis'][i % 5],
    message: ['Hello, I need help with...', 'Thanks for your support!', 'When will my order arrive?', 'I have a question about...', 'Great product!'][i % 5],
    time: `${(i % 12) + 1}h ago`,
    status: ['open', 'pending', 'resolved'][i % 3]
  }));
  
  const tags = [
    'JavaScript', 'TypeScript', 'Svelte', 'SvelteKit', 'React', 'Vue', 'Angular',
    'Node.js', 'Python', 'Go', 'Rust', 'Ruby', 'PHP', 'Java', 'C#', 'Swift',
    'Tailwind', 'CSS', 'HTML', 'Docker', 'Kubernetes', 'AWS', 'GCP', 'Azure'
  ];
</script>

<Hst.Story title="Components/ScrollArea" icon="lucide:scroll">
  <Hst.Variant title="Vertical">
    <div class="p-4">
      <ScrollArea class="h-72 w-48 rounded-md border">
        <div class="p-4">
          <h4 class="mb-4 text-sm font-medium leading-none">Tags</h4>
          {#each tags as tag}
            <div class="text-sm py-2 border-b last:border-0">{tag}</div>
          {/each}
        </div>
      </ScrollArea>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Horizontal">
    <div class="p-4">
      <ScrollArea class="w-96 whitespace-nowrap rounded-md border" orientation="horizontal">
        <div class="flex w-max space-x-4 p-4">
          {#each tags as tag}
            <Badge variant="secondary">{tag}</Badge>
          {/each}
        </div>
      </ScrollArea>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation List">
    <div class="p-4">
      <div class="w-80 border rounded-lg">
        <div class="p-3 border-b">
          <h3 class="font-semibold">Conversations</h3>
          <p class="text-xs text-muted-foreground">{conversations.length} total</p>
        </div>
        <ScrollArea class="h-80">
          <div class="divide-y">
            {#each conversations as conv}
              <div class="p-3 hover:bg-accent cursor-pointer">
                <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium shrink-0">
                    {conv.name.split(' ').map(n => n[0]).join('')}
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <p class="font-medium text-sm truncate">{conv.name}</p>
                      <span class="text-xs text-muted-foreground">{conv.time}</span>
                    </div>
                    <p class="text-sm text-muted-foreground truncate">{conv.message}</p>
                  </div>
                </div>
              </div>
            {/each}
          </div>
        </ScrollArea>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Both Directions">
    <div class="p-4">
      <ScrollArea class="h-72 w-72 rounded-md border" orientation="both">
        <div class="p-4" style="width: 600px;">
          <table class="w-full">
            <thead>
              <tr class="border-b">
                <th class="p-2 text-left">ID</th>
                <th class="p-2 text-left">Name</th>
                <th class="p-2 text-left">Email</th>
                <th class="p-2 text-left">Status</th>
                <th class="p-2 text-left">Created</th>
              </tr>
            </thead>
            <tbody>
              {#each conversations as conv}
                <tr class="border-b">
                  <td class="p-2">{conv.id}</td>
                  <td class="p-2">{conv.name}</td>
                  <td class="p-2">{conv.name.toLowerCase().replace(' ', '.')}@example.com</td>
                  <td class="p-2"><Badge variant={conv.status === 'open' ? 'success' : conv.status === 'pending' ? 'warning' : 'default'}>{conv.status}</Badge></td>
                  <td class="p-2">{conv.time}</td>
                </tr>
              {/each}
            </tbody>
          </table>
        </div>
      </ScrollArea>
    </div>
  </Hst.Variant>
</Hst.Story>
