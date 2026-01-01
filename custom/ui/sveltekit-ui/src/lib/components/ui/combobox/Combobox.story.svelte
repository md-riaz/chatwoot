<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { Combobox } from './index';

  const options = [
    { value: 1, label: 'Option 1' },
    { value: 2, label: 'Option 2' },
    { value: 3, label: 'Option 3' },
    { value: 4, label: 'Option 4' },
    { value: 5, label: 'Option 5' },
  ];

  const agentOptions = [
    { value: 'agent1', label: 'John Doe' },
    { value: 'agent2', label: 'Jane Smith' },
    { value: 'agent3', label: 'Mike Johnson' },
    { value: 'agent4', label: 'Sarah Williams' },
    { value: 'agent5', label: 'Tom Brown' },
  ];

  let selectedValue = $state<string | number>('');
  let agentValue = $state<string | number>('');
</script>

<Hst.Story title="Forms/Combobox" icon="lucide:search">
  <Hst.Variant title="Default">
    <div class="w-full p-4 bg-background h-80">
      <Combobox bind:value={selectedValue} {options} />
      <p class="mt-2 text-sm text-muted-foreground">Selected value: {selectedValue}</p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Agent Search">
    <div class="w-full p-4 bg-background h-80">
      <Combobox
        bind:value={agentValue}
        options={agentOptions}
        placeholder="Search agents..."
      />
      <p class="mt-2 text-sm text-muted-foreground">Selected agent: {agentValue}</p>
    </div>
  </Hst.Variant>

  <Hst.Variant title="Disabled">
    <div class="w-full p-4 bg-background h-80">
      <Combobox {options} disabled placeholder="Cannot select..." />
    </div>
  </Hst.Variant>
</Hst.Story>
