<script lang="ts">
  import type { Hst } from '@histoire/plugin-svelte';
  export let Hst: Hst;

  import { CopilotLoader, CopilotThinkingGroup } from './index';
</script>

<Hst.Story title="Copilot/Loader" icon="lucide:loader">
  <Hst.Variant title="Simple Loader">
    <div class="p-4">
      <CopilotLoader message="Thinking..." />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Custom Message">
    <div class="p-4">
      <CopilotLoader message="Generating response..." />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Thinking Group - In Progress">
    <div class="p-4 max-w-sm bg-white dark:bg-slate-800 rounded-lg border">
      <CopilotThinkingGroup
        steps={[
          { id: '1', label: 'Analyzing context', status: 'complete' },
          { id: '2', label: 'Searching knowledge base', status: 'active' },
          { id: '3', label: 'Formulating response', status: 'pending' }
        ]}
      />
    </div>
  </Hst.Variant>

  <Hst.Variant title="Thinking Group - Complete">
    <div class="p-4 max-w-sm bg-white dark:bg-slate-800 rounded-lg border">
      <CopilotThinkingGroup
        steps={[
          { id: '1', label: 'Analyzed conversation history', status: 'complete' },
          { id: '2', label: 'Retrieved relevant documents', status: 'complete' },
          { id: '3', label: 'Generated response', status: 'complete' }
        ]}
      />
    </div>
  </Hst.Variant>
</Hst.Story>
