<script lang="ts">
  import { onMount } from 'svelte';
  import { toast } from 'svelte-sonner';
  import { macrosStore } from '$lib/stores/macros.svelte';
  import type { CreateMacroParams } from '$lib/api/macros';
  import MacrosList from '$lib/components/macros/MacrosList.svelte';
  import MacroFormDialog from '$lib/components/macros/MacroFormDialog.svelte';
  import MacroExecuteDialog from '$lib/components/macros/MacroExecuteDialog.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus, Sparkles } from '@lucide/svelte';

  const macros = $derived(macrosStore.sortedMacros);
  const isLoading = $derived(macrosStore.isLoading);
  const isSaving = $derived(macrosStore.isSaving);
  const isExecuting = $derived(macrosStore.isExecuting);
  const totalCount = $derived(macrosStore.macroCount);
  const globalCount = $derived(macrosStore.globalMacros.length);
  const personalCount = $derived(macrosStore.personalMacros.length);

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

  async function handleSubmit(event: CustomEvent<CreateMacroParams>) {
    const payload = event.detail;

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

  async function handleExecuteSubmit(event: CustomEvent<number[]>) {
    if (!executeMacroId) return;

    const success = await macrosStore.executeMacro(
      executeMacroId,
      event.detail
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

<div class="macros-settings">
  <div class="header mb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="mb-2 flex items-center gap-3">
          <Sparkles class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">Macros</h1>
        </div>
        <p class="text-gray-600">
          Create quick action templates to automate repetitive tasks
        </p>
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div>
            <span class="text-2xl font-bold text-primary">{totalCount}</span>
            <span class="ml-2 text-gray-600">Total Macros</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-blue-600">{globalCount}</span>
            <span class="ml-2 text-gray-600">Global</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-purple-600"
              >{personalCount}</span
            >
            <span class="ml-2 text-gray-600">Personal</span>
          </div>
        </div>
      </div>

      <Button onclick={handleAdd} size="lg">
        <Plus class="mr-2 h-5 w-5" />
        Add Macro
      </Button>
    </div>
  </div>

  <div class="content">
    <MacrosList
      {macros}
      {isLoading}
      onedit={handleEdit}
      onexecute={handleExecute}
      onrefresh={() => macrosStore.fetchMacros()}
    />
  </div>
</div>

<MacroFormDialog
  open={showEditor}
  mode={editingMacroId ? 'edit' : 'create'}
  macro={editingMacro}
  isSubmitting={isSaving}
  on:submit={handleSubmit}
  on:close={() => {
    showEditor = false;
    editingMacroId = null;
  }}
/>

<MacroExecuteDialog
  open={showExecuteDialog}
  macro={macroToExecute}
  isSubmitting={isExecuting}
  on:execute={handleExecuteSubmit}
  on:close={() => {
    showExecuteDialog = false;
    executeMacroId = null;
  }}
/>

<style>
  .macros-settings {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }

  .header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1.5rem;
  }
</style>
