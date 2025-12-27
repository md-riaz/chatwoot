<script lang="ts">
  import * as Select from './index.js';
  import { Label } from '$lib/components/ui/label';
  
  export let Hst: any;
  
  let fruit = $state('');
  let status = $state('');
  let priority = $state('');
</script>

<Hst.Story title="Components/Select" icon="lucide:chevrons-up-down">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-xs">
      <Select.Root bind:selected={{ value: fruit }}>
        <Select.Trigger>
          <Select.Value placeholder="Select a fruit" />
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="apple">Apple</Select.Item>
          <Select.Item value="banana">Banana</Select.Item>
          <Select.Item value="cherry">Cherry</Select.Item>
          <Select.Item value="orange">Orange</Select.Item>
          <Select.Item value="grape">Grape</Select.Item>
        </Select.Content>
      </Select.Root>
      {#if fruit}
        <p class="text-sm text-muted-foreground mt-2">Selected: {fruit}</p>
      {/if}
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Label">
    <div class="p-4 max-w-xs space-y-2">
      <Label>Select an inbox</Label>
      <Select.Root>
        <Select.Trigger>
          <Select.Value placeholder="Choose inbox..." />
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="email">Email</Select.Item>
          <Select.Item value="website">Website Chat</Select.Item>
          <Select.Item value="facebook">Facebook</Select.Item>
          <Select.Item value="whatsapp">WhatsApp</Select.Item>
          <Select.Item value="twitter">Twitter</Select.Item>
        </Select.Content>
      </Select.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Groups">
    <div class="p-4 max-w-xs">
      <Select.Root>
        <Select.Trigger>
          <Select.Value placeholder="Assign to agent..." />
        </Select.Trigger>
        <Select.Content>
          <Select.Group>
            <Select.Label>Online</Select.Label>
            <Select.Item value="john">John Doe</Select.Item>
            <Select.Item value="jane">Jane Smith</Select.Item>
          </Select.Group>
          <Select.Separator />
          <Select.Group>
            <Select.Label>Offline</Select.Label>
            <Select.Item value="bob">Bob Wilson</Select.Item>
            <Select.Item value="alice">Alice Brown</Select.Item>
          </Select.Group>
        </Select.Content>
      </Select.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Status Select">
    <div class="p-4 max-w-xs space-y-2">
      <Label>Conversation Status</Label>
      <Select.Root bind:selected={{ value: status }}>
        <Select.Trigger>
          <Select.Value placeholder="Change status..." />
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="open">🟢 Open</Select.Item>
          <Select.Item value="pending">🟡 Pending</Select.Item>
          <Select.Item value="resolved">✅ Resolved</Select.Item>
          <Select.Item value="snoozed">💤 Snoozed</Select.Item>
        </Select.Content>
      </Select.Root>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Priority Select">
    <div class="p-4 max-w-xs space-y-2">
      <Label>Priority</Label>
      <Select.Root bind:selected={{ value: priority }}>
        <Select.Trigger>
          <Select.Value placeholder="Set priority..." />
        </Select.Trigger>
        <Select.Content>
          <Select.Item value="urgent">🔴 Urgent</Select.Item>
          <Select.Item value="high">🟠 High</Select.Item>
          <Select.Item value="medium">🟡 Medium</Select.Item>
          <Select.Item value="low">🟢 Low</Select.Item>
          <Select.Item value="none">⚪ None</Select.Item>
        </Select.Content>
      </Select.Root>
    </div>
  </Hst.Variant>
</Hst.Story>
