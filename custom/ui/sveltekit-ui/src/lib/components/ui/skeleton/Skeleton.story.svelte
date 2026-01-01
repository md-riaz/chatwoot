<script lang="ts">
  import { Skeleton } from './index.js';
  import * as Card from '$lib/components/ui/card';
  
  export let Hst: any;
</script>

<Hst.Story title="Components/Skeleton" icon="lucide:loader">
  <Hst.Variant title="Default">
    <div class="p-4 space-y-4 max-w-sm">
      <Skeleton class="h-4 w-full" />
      <Skeleton class="h-4 w-3/4" />
      <Skeleton class="h-4 w-1/2" />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Card Loading">
    <div class="p-4 max-w-sm">
      <Card.Root>
        <Card.Header class="flex flex-row items-center gap-4">
          <Skeleton class="h-12 w-12 rounded-full" />
          <div class="space-y-2 flex-1">
            <Skeleton class="h-4 w-[200px]" />
            <Skeleton class="h-3 w-[150px]" />
          </div>
        </Card.Header>
        <Card.Content class="space-y-2">
          <Skeleton class="h-4 w-full" />
          <Skeleton class="h-4 w-full" />
          <Skeleton class="h-4 w-3/4" />
        </Card.Content>
      </Card.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation Loading">
    <div class="p-4 max-w-sm space-y-4">
      {#each [1, 2, 3] as _}
        <div class="flex gap-3 p-3 border rounded-lg">
          <Skeleton class="h-10 w-10 rounded-full shrink-0" />
          <div class="space-y-2 flex-1">
            <Skeleton class="h-4 w-[120px]" />
            <Skeleton class="h-3 w-full" />
            <div class="flex justify-between">
              <Skeleton class="h-3 w-[60px]" />
              <Skeleton class="h-5 w-5 rounded-full" />
            </div>
          </div>
        </div>
      {/each}
    </div>
  </Hst.Variant>

  <Hst.Variant title="Message Bubble Loading">
    <div class="p-4 max-w-md space-y-4">
      <!-- Incoming message -->
      <div class="flex gap-2">
        <Skeleton class="h-8 w-8 rounded-full shrink-0" />
        <div class="space-y-2">
          <Skeleton class="h-20 w-[250px] rounded-lg rounded-bl-none" />
          <Skeleton class="h-3 w-[60px]" />
        </div>
      </div>
      
      <!-- Outgoing message -->
      <div class="flex gap-2 flex-row-reverse">
        <div class="space-y-2">
          <Skeleton class="h-16 w-[200px] rounded-lg rounded-br-none" />
          <Skeleton class="h-3 w-[60px] ml-auto" />
        </div>
      </div>
      
      <!-- Incoming message -->
      <div class="flex gap-2">
        <Skeleton class="h-8 w-8 rounded-full shrink-0" />
        <div class="space-y-2">
          <Skeleton class="h-12 w-[180px] rounded-lg rounded-bl-none" />
          <Skeleton class="h-3 w-[60px]" />
        </div>
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Table Loading">
    <div class="p-4">
      <div class="border rounded-lg">
        <!-- Header -->
        <div class="flex gap-4 p-4 border-b bg-muted/50">
          <Skeleton class="h-4 w-[100px]" />
          <Skeleton class="h-4 w-[150px]" />
          <Skeleton class="h-4 w-[100px]" />
          <Skeleton class="h-4 w-[80px]" />
        </div>
        <!-- Rows -->
        {#each [1, 2, 3, 4] as _}
          <div class="flex gap-4 p-4 border-b last:border-0">
            <Skeleton class="h-4 w-[100px]" />
            <Skeleton class="h-4 w-[150px]" />
            <Skeleton class="h-4 w-[100px]" />
            <Skeleton class="h-6 w-[60px] rounded-full" />
          </div>
        {/each}
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Sidebar Loading">
    <div class="w-64 h-96 border-r p-4 space-y-4">
      <!-- Logo -->
      <div class="flex items-center gap-2 mb-6">
        <Skeleton class="h-8 w-8 rounded-lg" />
        <Skeleton class="h-4 w-[100px]" />
      </div>
      
      <!-- Nav items -->
      {#each [1, 2, 3] as _}
        <div class="flex items-center gap-3 px-2 py-2">
          <Skeleton class="h-5 w-5 rounded" />
          <Skeleton class="h-4 w-[120px]" />
        </div>
      {/each}
      
      <!-- Section -->
      <div class="pt-4">
        <Skeleton class="h-3 w-[80px] mb-3" />
        {#each [1, 2, 3, 4] as _}
          <div class="flex items-center gap-3 px-2 py-2">
            <Skeleton class="h-5 w-5 rounded" />
            <Skeleton class="h-4 w-[100px]" />
          </div>
        {/each}
      </div>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Contact Card Loading">
    <div class="p-4 max-w-sm">
      <Card.Root>
        <Card.Content class="pt-6">
          <div class="flex items-center gap-4">
            <Skeleton class="h-14 w-14 rounded-full" />
            <div class="space-y-2 flex-1">
              <Skeleton class="h-5 w-[150px]" />
              <Skeleton class="h-3 w-[180px]" />
            </div>
          </div>
          <div class="mt-6 space-y-3">
            <div class="flex justify-between">
              <Skeleton class="h-4 w-[60px]" />
              <Skeleton class="h-4 w-[120px]" />
            </div>
            <div class="flex justify-between">
              <Skeleton class="h-4 w-[80px]" />
              <Skeleton class="h-4 w-[100px]" />
            </div>
            <div class="flex justify-between">
              <Skeleton class="h-4 w-[70px]" />
              <Skeleton class="h-4 w-[90px]" />
            </div>
          </div>
          <div class="mt-6 flex gap-2">
            <Skeleton class="h-9 flex-1 rounded-md" />
            <Skeleton class="h-9 flex-1 rounded-md" />
          </div>
        </Card.Content>
      </Card.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
