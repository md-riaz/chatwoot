<script lang="ts">
  /**
   * AgentDialog
   * Dialog for creating/editing agents
   */
  import { createEventDispatcher } from 'svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import AgentForm from './AgentForm.svelte';
  import type { Agent, CreateAgentParams } from '$lib/api/agents';

  interface Props {
    open: boolean;
    mode?: 'create' | 'edit';
    agent?: Agent | null;
  }

  let { open = $bindable(false), mode = 'create', agent = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateAgentParams;
    close: void;
  }>();

  function handleSubmit(data: CreateAgentParams) {
    dispatch('submit', data);
    open = false;
  }

  function handleCancel() {
    dispatch('close');
    open = false;
  }
</script>

<Dialog.Root {open} onOpenChange={(value) => (open = value)}>
  <Dialog.Content class="max-w-2xl max-h-[85vh] overflow-y-auto">
    <Dialog.Header>
      <Dialog.Title>
        {mode === 'create' ? 'Add Agent' : 'Edit Agent'}
      </Dialog.Title>
      <Dialog.Description>
        {mode === 'create' 
          ? 'Invite a new team member to your account.'
          : 'Update agent information.'}
      </Dialog.Description>
    </Dialog.Header>

    <AgentForm
      {mode}
      {agent}
      on:submit={(e) => handleSubmit(e.detail)}
      on:cancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
