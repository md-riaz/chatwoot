<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { RadioGroup } from './index';

  const notificationOptions = [
    { value: 'all', label: 'All notifications', description: 'Receive all push and email notifications' },
    { value: 'important', label: 'Important only', description: 'Only receive notifications for mentions and assignments' },
    { value: 'none', label: 'None', description: 'Turn off all notifications' },
  ];

  const priorityOptions = [
    { value: 'urgent', label: 'Urgent' },
    { value: 'high', label: 'High' },
    { value: 'medium', label: 'Medium' },
    { value: 'low', label: 'Low' },
  ];

  const planOptions = [
    { value: 'starter', label: 'Starter', description: '$9/month - Up to 5 agents' },
    { value: 'pro', label: 'Pro', description: '$29/month - Up to 25 agents' },
    { value: 'enterprise', label: 'Enterprise', description: 'Custom pricing - Unlimited agents', disabled: true },
  ];

  let selectedNotification = $state('important');
  let selectedPriority = $state('medium');
  let selectedPlan = $state('starter');
</script>

<Hst.Story title="Forms/RadioGroup" icon="lucide:circle-dot">
  <Hst.Variant title="Vertical with Descriptions">
    <div class="p-4 bg-background max-w-md">
      <p class="text-sm font-medium mb-3">Notification Preferences</p>
      <RadioGroup
        options={notificationOptions}
        bind:value={selectedNotification}
        name="notifications"
      />
      <p class="mt-4 text-sm text-muted-foreground">
        Selected: {selectedNotification}
      </p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Horizontal">
    <div class="p-4 bg-background">
      <p class="text-sm font-medium mb-3">Priority</p>
      <RadioGroup
        options={priorityOptions}
        bind:value={selectedPriority}
        name="priority"
        orientation="horizontal"
      />
      <p class="mt-4 text-sm text-muted-foreground">
        Selected: {selectedPriority}
      </p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="With Disabled Option">
    <div class="p-4 bg-background max-w-md">
      <p class="text-sm font-medium mb-3">Select Plan</p>
      <RadioGroup
        options={planOptions}
        bind:value={selectedPlan}
        name="plan"
      />
      <p class="mt-4 text-sm text-muted-foreground">
        Selected: {selectedPlan}
      </p>
    </div>
  </Hst.Variant>
</Hst.Story>
