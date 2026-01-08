<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { AssignmentCard, AssignmentPolicyCard, AgentCapacityCard, RadioCard, DataTable } from './index';

  let selectedPolicy = $state('round_robin');
</script>

<Hst.Story title="AssignmentPolicy" icon="lucide:users">
  <Hst.Variant title="Assignment Card">
    <div class="p-4 space-y-3 max-w-md">
      <AssignmentCard
        title="Auto-assign new conversations"
        description="Automatically assign incoming conversations to available agents"
        isEnabled={true}
        agentCount={5}
      />
      <AssignmentCard
        title="Limit concurrent chats"
        description="Restrict the number of active conversations per agent"
        isEnabled={false}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Assignment Policy Card">
    <div class="p-4 space-y-3 max-w-md">
      <AssignmentPolicyCard
        name="Default Policy"
        type="round_robin"
        inboxes={['Website Chat', 'Email Support']}
        isActive={true}
      />
      <AssignmentPolicyCard
        name="VIP Support"
        type="manual"
        inboxes={['Enterprise']}
        isActive={false}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Agent Capacity Cards">
    <div class="p-4 space-y-3 max-w-sm">
      <AgentCapacityCard
        agent={{ name: 'John Doe', email: 'john@example.com' }}
        capacity={10}
        current={3}
        status="online"
      />
      <AgentCapacityCard
        agent={{ name: 'Jane Smith', email: 'jane@example.com' }}
        capacity={10}
        current={8}
        status="busy"
      />
      <AgentCapacityCard
        agent={{ name: 'Bob Wilson' }}
        capacity={10}
        current={0}
        status="offline"
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Radio Cards">
    <div class="p-4 space-y-2 max-w-md">
      <RadioCard
        value="round_robin"
        title="Round Robin"
        description="Distribute conversations evenly among all agents"
        selected={selectedPolicy === 'round_robin'}
        onSelect={(v) => selectedPolicy = v}
      />
      <RadioCard
        value="load_balanced"
        title="Load Balanced"
        description="Assign to agents with the lowest current workload"
        selected={selectedPolicy === 'load_balanced'}
        onSelect={(v) => selectedPolicy = v}
      />
      <RadioCard
        value="manual"
        title="Manual"
        description="Let agents pick conversations from the queue"
        selected={selectedPolicy === 'manual'}
        onSelect={(v) => selectedPolicy = v}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Data Table">
    <div class="p-4">
      <DataTable
        selectable={true}
        columns={[
          { key: 'name', label: 'Agent Name' },
          { key: 'email', label: 'Email' },
          { key: 'capacity', label: 'Capacity', width: '100px' },
          { key: 'status', label: 'Status', width: '100px' }
        ]}
        data={[
          { name: 'John Doe', email: 'john@example.com', capacity: 10, status: 'Online' },
          { name: 'Jane Smith', email: 'jane@example.com', capacity: 8, status: 'Busy' },
          { name: 'Bob Wilson', email: 'bob@example.com', capacity: 10, status: 'Offline' }
        ]}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
