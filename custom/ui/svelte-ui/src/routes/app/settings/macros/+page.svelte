<script lang="ts">
  import { onMount } from 'svelte';
  import { macrosStore } from '$lib/stores/macros.svelte';
  import MacrosList from '$lib/components/macros/MacrosList.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus, Sparkles } from '@lucide/svelte';
  
  const macros = $derived(macrosStore.sortedMacros);
  const isLoading = $derived(macrosStore.isLoading);
  const totalCount = $derived(macrosStore.macroCount);
  const globalCount = $derived(macrosStore.globalMacros.length);
  const personalCount = $derived(macrosStore.personalMacros.length);
  
  let showEditor = $state(false);
  let editingMacroId = $state<number | null>(null);
  
  onMount(() => {
    macrosStore.fetchMacros();
  });
  
  function handleAdd() {
    editingMacroId = null;
    showEditor = true;
    // TODO: Implement editor modal
    alert('Macro editor will be implemented in the next iteration');
  }
  
  function handleEdit(id: number) {
    editingMacroId = id;
    showEditor = true;
    // TODO: Implement editor modal
    alert(`Editing macro ${id} - editor will be implemented in the next iteration`);
  }
  
  function handleExecute(id: number) {
    // TODO: Implement execution dialog
    alert(`Execute macro ${id} - execution dialog will be implemented in the next iteration`);
  }
</script>

<div class="macros-settings">
  <div class="header mb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <Sparkles class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">Macros</h1>
        </div>
        <p class="text-gray-600">
          Create quick action templates to automate repetitive tasks
        </p>
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div>
            <span class="text-2xl font-bold text-primary">{totalCount}</span>
            <span class="text-gray-600 ml-2">Total Macros</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-blue-600">{globalCount}</span>
            <span class="text-gray-600 ml-2">Global</span>
          </div>
          <div>
            <span class="text-2xl font-bold text-purple-600">{personalCount}</span>
            <span class="text-gray-600 ml-2">Personal</span>
          </div>
        </div>
      </div>
      
      <Button onclick={handleAdd} size="lg">
        <Plus class="h-5 w-5 mr-2" />
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
    />
  </div>
</div>

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
