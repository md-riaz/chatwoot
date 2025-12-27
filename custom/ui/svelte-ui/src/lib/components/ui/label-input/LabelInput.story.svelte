<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { LabelInput } from './index';

  const availableLabels = [
    { id: 'bug', title: 'Bug', color: '#ef4444', description: 'Something is broken' },
    { id: 'feature', title: 'Feature', color: '#22c55e', description: 'New feature request' },
    { id: 'enhancement', title: 'Enhancement', color: '#3b82f6' },
    { id: 'documentation', title: 'Documentation', color: '#a855f7' },
    { id: 'question', title: 'Question', color: '#f59e0b' },
    { id: 'urgent', title: 'Urgent', color: '#dc2626', description: 'High priority' },
    { id: 'wontfix', title: "Won't Fix", color: '#6b7280' },
    { id: 'duplicate', title: 'Duplicate', color: '#9ca3af' },
  ];

  let selectedLabels = $state(['bug', 'urgent']);
  let emptyLabels = $state<string[]>([]);
</script>

<Hst.Story title="Forms/LabelInput" icon="lucide:tag">
  <Hst.Variant title="With Selected Labels">
    <div class="p-4 bg-background min-h-[300px]">
      <p class="text-sm font-medium mb-2">Conversation Labels</p>
      <LabelInput
        labels={availableLabels}
        bind:selectedLabels
        onAdd={(label) => console.log('Added:', label)}
        onRemove={(id) => console.log('Removed:', id)}
      />
      <p class="mt-4 text-sm text-muted-foreground">
        Selected: {selectedLabels.join(', ')}
      </p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <div class="p-4 bg-background min-h-[300px]">
      <p class="text-sm font-medium mb-2">Add Labels</p>
      <LabelInput
        labels={availableLabels}
        bind:selectedLabels={emptyLabels}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="All Labels Selected">
    <div class="p-4 bg-background min-h-[300px]">
      <LabelInput
        labels={availableLabels}
        selectedLabels={availableLabels.map(l => l.id)}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
