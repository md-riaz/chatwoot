<script lang="ts">
  import { onMount } from 'svelte';
  import { toast } from 'svelte-sonner';
  import { automationStore } from '$lib/stores/automation.svelte';
  import type { CreateAutomationParams } from '$lib/api/automation';
  import AutomationList from '$lib/components/automation/AutomationList.svelte';
  import AutomationFormDialog from '$lib/components/automation/AutomationFormDialog.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus, Zap } from '@lucide/svelte';

  const automations = $derived(automationStore.sortedAutomations);
  const isLoading = $derived(automationStore.isLoading);
  const isSaving = $derived(automationStore.isSaving);
  const activeCount = $derived(automationStore.activeCount);
  const totalCount = $derived(automationStore.automationCount);

  let showEditor = $state(false);
  let editingAutomationId = $state<number | null>(null);

  const editingAutomation = $derived(
    editingAutomationId
      ? automationStore.getAutomationById(editingAutomationId)
      : null
  );

  $effect(() => {
    if (!showEditor) {
      editingAutomationId = null;
    }
  });

  onMount(() => {
    automationStore.fetchAutomations();
  });

  function handleAdd() {
    editingAutomationId = null;
    showEditor = true;
  }

  function handleEdit(id: number) {
    editingAutomationId = id;
    showEditor = true;
  }

  async function handleSubmit(payload: CreateAutomationParams) {
    const result = editingAutomationId
      ? await automationStore.updateAutomation(editingAutomationId, payload)
      : await automationStore.createAutomation(payload);

    if (result) {
      toast.success(
        editingAutomationId
          ? 'Automation updated successfully.'
          : 'Automation created successfully.'
      );
      showEditor = false;
      editingAutomationId = null;
      await automationStore.fetchAutomations();
      return;
    }

    toast.error(automationStore.error || 'Failed to save automation.');
  }
</script>

<div class="automation-settings">
  <div class="header mb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="mb-2 flex items-center gap-3">
          <Zap class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">Automation Rules</h1>
        </div>
        <p class="text-gray-600">
          Create automated workflows to save time and improve efficiency
        </p>
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div>
            <span class="text-2xl font-bold text-primary">{totalCount}</span>
            <span class="ml-2 text-gray-600">Total Rules</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-green-600">{activeCount}</span>
            <span class="ml-2 text-gray-600">Active</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-gray-400"
              >{totalCount - activeCount}</span
            >
            <span class="ml-2 text-gray-600">Inactive</span>
          </div>
        </div>
      </div>

      <Button onclick={handleAdd} size="lg">
        <Plus class="mr-2 h-5 w-5" />
        Add Automation
      </Button>
    </div>
  </div>

  <div class="content">
    <AutomationList
      {automations}
      {isLoading}
      onedit={handleEdit}
      onrefresh={() => automationStore.fetchAutomations()}
    />
  </div>
</div>

<AutomationFormDialog
  bind:open={showEditor}
  mode={editingAutomationId ? 'edit' : 'create'}
  automation={editingAutomation}
  isSubmitting={isSaving}
  onSubmit={handleSubmit}
  onClose={() => {
    showEditor = false;
    editingAutomationId = null;
  }}
/>

<style>
  .automation-settings {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }

  .header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1.5rem;
  }
</style>
