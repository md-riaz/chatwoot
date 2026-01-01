<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { TagInput } from './index';

  const labelOptions = [
    { id: 'bug', label: 'Bug', color: '#ef4444' },
    { id: 'feature', label: 'Feature', color: '#22c55e' },
    { id: 'enhancement', label: 'Enhancement', color: '#3b82f6' },
    { id: 'documentation', label: 'Documentation', color: '#a855f7' },
    { id: 'question', label: 'Question', color: '#f59e0b' },
    { id: 'urgent', label: 'Urgent', color: '#dc2626' },
  ];

  let selectedTags = $state([
    { id: 'bug', label: 'Bug', color: '#ef4444' },
  ]);

  let emptyTags = $state<typeof labelOptions>([]);
</script>

<Hst.Story title="Forms/TagInput" icon="lucide:tags">
  <Hst.Variant title="With Options">
    <div class="w-full p-4 bg-background min-h-[200px]">
      <TagInput
        bind:tags={selectedTags}
        options={labelOptions}
        placeholder="Add labels..."
      />
      <p class="mt-2 text-sm text-muted-foreground">
        Selected: {selectedTags.map(t => t.label).join(', ')}
      </p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Empty State">
    <div class="w-full p-4 bg-background min-h-[200px]">
      <TagInput
        bind:tags={emptyTags}
        options={labelOptions}
        placeholder="Search and add labels..."
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled">
    <div class="w-full p-4 bg-background min-h-[200px]">
      <TagInput
        tags={[
          { id: 'bug', label: 'Bug', color: '#ef4444' },
          { id: 'feature', label: 'Feature', color: '#22c55e' },
        ]}
        options={labelOptions}
        disabled
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Conversation Labels">
    <div class="w-full p-4 bg-background min-h-[200px]">
      <p class="mb-2 text-sm font-medium">Conversation Labels</p>
      <TagInput
        bind:tags={selectedTags}
        options={labelOptions}
        placeholder="Add conversation labels..."
      />
    </div>
  </Hst.Variant>
</Hst.Story>
