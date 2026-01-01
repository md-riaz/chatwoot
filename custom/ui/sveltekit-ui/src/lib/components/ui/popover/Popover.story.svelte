<script lang="ts">
  import * as Popover from './index.js';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  
  export let Hst: any;
</script>

<Hst.Story title="Components/Popover" icon="lucide:square-stack">
  <Hst.Variant title="Default">
    <div class="flex items-center justify-center p-16">
      <Popover.Root>
        <Popover.Trigger>
          <Button variant="outline">Open Popover</Button>
        </Popover.Trigger>
        <Popover.Content>
          <div class="grid gap-4">
            <div class="space-y-2">
              <h4 class="font-medium leading-none">Dimensions</h4>
              <p class="text-sm text-muted-foreground">
                Set the dimensions for the layer.
              </p>
            </div>
            <div class="grid gap-2">
              <div class="grid grid-cols-3 items-center gap-4">
                <Label>Width</Label>
                <Input class="col-span-2 h-8" value="100%" />
              </div>
              <div class="grid grid-cols-3 items-center gap-4">
                <Label>Height</Label>
                <Input class="col-span-2 h-8" value="25px" />
              </div>
            </div>
          </div>
        </Popover.Content>
      </Popover.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Filter Popover">
    <div class="flex items-center justify-center p-16">
      <Popover.Root>
        <Popover.Trigger>
          <Button variant="outline" size="sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mr-2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
            Filters
          </Button>
        </Popover.Trigger>
        <Popover.Content class="w-80">
          <div class="space-y-4">
            <h4 class="font-medium">Filter Conversations</h4>
            <div class="space-y-2">
              <Label>Status</Label>
              <select class="w-full h-9 rounded-md border border-input bg-background px-3 py-1 text-sm">
                <option>All</option>
                <option>Open</option>
                <option>Pending</option>
                <option>Resolved</option>
              </select>
            </div>
            <div class="space-y-2">
              <Label>Assignee</Label>
              <Input placeholder="Search agent..." />
            </div>
            <div class="flex justify-end gap-2">
              <Button variant="outline" size="sm">Clear</Button>
              <Button size="sm">Apply</Button>
            </div>
          </div>
        </Popover.Content>
      </Popover.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="User Info">
    <div class="flex items-center justify-center p-16">
      <Popover.Root>
        <Popover.Trigger>
          <div class="flex items-center gap-2 cursor-pointer hover:bg-accent p-2 rounded-lg">
            <div class="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center text-sm font-medium">JD</div>
            <span class="text-sm font-medium">John Doe</span>
          </div>
        </Popover.Trigger>
        <Popover.Content class="w-64" align="start">
          <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-full bg-primary/20 flex items-center justify-center text-lg font-medium">JD</div>
              <div>
                <p class="font-medium">John Doe</p>
                <p class="text-sm text-muted-foreground">john@example.com</p>
              </div>
            </div>
            <div class="text-sm space-y-1">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Phone</span>
                <span>+1 234 567 8900</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Location</span>
                <span>San Francisco</span>
              </div>
            </div>
            <Button size="sm" class="w-full">View Profile</Button>
          </div>
        </Popover.Content>
      </Popover.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
