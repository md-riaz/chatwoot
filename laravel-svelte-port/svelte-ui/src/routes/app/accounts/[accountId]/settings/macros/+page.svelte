<script lang="ts">
  /**
   * Macros Management Page
   * Vue parity: app/javascript/dashboard/routes/dashboard/settings/macros/Index.vue
   */

  import { onMount } from 'svelte';
  import { toast } from 'svelte-sonner';
  import { macrosStore } from '$lib/stores/macros.svelte';
  import type { CreateMacroParams } from '$lib/api/macros';
  import MacrosList from '$lib/components/macros/MacrosList.svelte';
  import MacroFormDialog from '$lib/components/macros/MacroFormDialog.svelte';
  import MacroExecuteDialog from '$lib/components/macros/MacroExecuteDialog.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus } from '@lucide/svelte';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';

  const macros = $derived(macrosStore.sortedMacros);
  const isLoading = $derived(macrosStore.isLoading);
  const isSaving = $derived(macrosStore.isSaving);
  const isExecuting = $derived(macrosStore.isExecuting);

  let showEditor = $state(false);
  let showExecuteDialog = $state(false);
  let editingMacroId = $state<number | null>(null);
  let executeMacroId = $state<number | null>(null);

  const editingMacro = $derived(
    editingMacroId ? macrosStore.getMacroById(editingMacroId) : null
  );
  const macroToExecute = $derived(
    executeMacroId ? macrosStore.getMacroById(executeMacroId) : null
  );

  onMount(() => {
    macrosStore.fetchMacros();
  });

  function handleAdd() {
    editingMacroId = null;
    showEditor = true;
  }

  function handleEdit(id: number) {
    editingMacroId = id;
    showEditor = true;
  }

  function handleExecute(id: number) {
    executeMacroId = id;
    showExecuteDialog = true;
  }

  async function handleSubmit(payload: CreateMacroParams) {
    const result = editingMacroId
      ? await macrosStore.updateMacro(editingMacroId, payload)
      : await macrosStore.createMacro(payload);

    if (result) {
      toast.success(
        editingMacroId
          ? 'Macro updated successfully.'
          : 'Macro created successfully.'
      );
      showEditor = false;
      editingMacroId = null;
      await macrosStore.fetchMacros();
      return;
    }

    toast.error(macrosStore.error || 'Failed to save macro.');
  }

  async function handleExecuteSubmit(conversationIds: number[]) {
    if (!executeMacroId) return;

    const success = await macrosStore.executeMacro(
      executeMacroId,
      conversationIds
    );
    if (success) {
      toast.success('Macro executed successfully.');
      showExecuteDialog = false;
      executeMacroId = null;
      await macrosStore.fetchMacros();
      return;
    }

    toast.error(macrosStore.error || 'Failed to execute macro.');
  }
</script>

<div class="flex flex-col w-full h-full gap-8">
  <BaseSettingsHeader
    title="Macros"
    description="A macro is a set of saved actions that help customer service agents easily complete tasks. The agents can define a set of actions like tagging a conversation with a label, sending an email transcript, updating a custom attribute, etc., and they can run these actions in a single click."
    linkText="Learn more about macros"
    linkUrl="https://www.chatwoot.com/hc/user-guide/articles/1677579781-what-are-macros-and-how-to-use-them"
  >
    {#snippet actions()}
      <Button onclick={handleAdd}>
        <Plus class="mr-2 h-4 w-4" />
        Add Macro
      </Button>
    {/snippet}
  </BaseSettingsHeader>

  <main>
    <MacrosList
      {macros}
      {isLoading}
      onedit={handleEdit}
      onexecute={handleExecute}
      onrefresh={() => macrosStore.fetchMacros()}
    />
  </main>
</div>

<MacroFormDialog
  bind:open={showEditor}
  mode={editingMacroId ? 'edit' : 'create'}
  macro={editingMacro}
  isSubmitting={isSaving}
  onSubmit={handleSubmit}
  onClose={() => {
    showEditor = false;
    editingMacroId = null;
  }}
/>

<MacroExecuteDialog
  bind:open={showExecuteDialog}
  macro={macroToExecute}
  isSubmitting={isExecuting}
  onExecute={handleExecuteSubmit}
  onClose={() => {
    showExecuteDialog = false;
    executeMacroId = null;
  }}
/>
