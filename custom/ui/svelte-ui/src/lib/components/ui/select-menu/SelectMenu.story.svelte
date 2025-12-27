<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { SelectMenu } from './index';

  let selectedStatus = $state('');
  let selectedAgent = $state('');
</script>

<Hst.Story title="SelectMenu" icon="lucide:list">
  <Hst.Variant title="Default">
    <div class="p-4 max-w-xs">
      <SelectMenu
        placeholder="Select status"
        options={[
          { value: 'open', label: 'Open', icon: '🟢' },
          { value: 'pending', label: 'Pending', icon: '🟡' },
          { value: 'resolved', label: 'Resolved', icon: '✅' },
          { value: 'snoozed', label: 'Snoozed', icon: '💤' }
        ]}
        value={selectedStatus}
        onSelect={(v) => selectedStatus = v}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Searchable">
    <div class="p-4 max-w-xs">
      <SelectMenu
        placeholder="Select agent"
        searchable={true}
        options={[
          { value: '1', label: 'John Doe', description: 'john@example.com' },
          { value: '2', label: 'Jane Smith', description: 'jane@example.com' },
          { value: '3', label: 'Bob Wilson', description: 'bob@example.com' },
          { value: '4', label: 'Alice Brown', description: 'alice@example.com' },
          { value: '5', label: 'Charlie Davis', description: 'charlie@example.com' }
        ]}
        value={selectedAgent}
        onSelect={(v) => selectedAgent = v}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Disabled Options">
    <div class="p-4 max-w-xs">
      <SelectMenu
        placeholder="Select priority"
        options={[
          { value: 'urgent', label: 'Urgent', icon: '🔴' },
          { value: 'high', label: 'High', icon: '🟠' },
          { value: 'medium', label: 'Medium', icon: '🟡' },
          { value: 'low', label: 'Low', icon: '🟢' },
          { value: 'none', label: 'None', icon: '⚪', disabled: true }
        ]}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
