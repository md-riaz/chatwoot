<script lang="ts">
  import { onMount } from 'svelte';
  import { automationStore } from '$lib/stores/automation.svelte';
  import AutomationList from '$lib/components/automation/AutomationList.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus, Zap } from '@lucide/svelte';
  
  const automations = $derived(automationStore.sortedAutomations);
  const isLoading = $derived(automationStore.isLoading);
  const activeCount = $derived(automationStore.activeCount);
  const totalCount = $derived(automationStore.automationCount);
  
  let showEditor = $state(false);
  let editingAutomationId = $state<number | null>(null);
  
  onMount(() => {
    automationStore.fetchAutomations();
  });
  
  function handleAdd() {
    editingAutomationId = null;
    showEditor = true;
    // TODO: Implement editor modal
    alert('Automation editor will be implemented in the next iteration');
  }
  
  function handleEdit(id: number) {
    editingAutomationId = id;
    showEditor = true;
    // TODO: Implement editor modal
    alert(`Editing automation ${id} - editor will be implemented in the next iteration`);
  }
</script>

<div class="automation-settings">
  <div class="header mb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <Zap class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">Automation Rules</h1>
        </div>
        <p class="text-gray-600">
          Create automated workflows to save time and improve efficiency
        </p>
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div>
            <span class="text-2xl font-bold text-primary">{totalCount}</span>
            <span class="text-gray-600 ml-2">Total Rules</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-green-600">{activeCount}</span>
            <span class="text-gray-600 ml-2">Active</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-gray-400">{totalCount - activeCount}</span>
            <span class="text-gray-600 ml-2">Inactive</span>
          </div>
        </div>
      </div>
      
      <Button onclick={handleAdd} size="lg">
        <Plus class="h-5 w-5 mr-2" />
        Add Automation
      </Button>
    </div>
  </div>
  
  <div class="content">
    <AutomationList 
      {automations}
      {isLoading}
      onedit={handleEdit}
    />
  </div>
</div>

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
