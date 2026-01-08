<script lang="ts">
  import { onMount } from 'svelte';
  import { slaStore } from '$lib/stores/sla.svelte';
  import SLAList from '$lib/components/sla/SLAList.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus, Shield } from '@lucide/svelte';
  
  const policies = $derived(slaStore.sortedPolicies);
  const isLoading = $derived(slaStore.isLoading);
  const totalCount = $derived(slaStore.policyCount);
  
  let showEditor = $state(false);
  let editingPolicyId = $state<number | null>(null);
  
  onMount(() => {
    slaStore.fetchPolicies();
  });
  
  function handleAdd() {
    editingPolicyId = null;
    showEditor = true;
    // TODO: Implement editor modal
    alert('SLA policy editor will be implemented in the next iteration');
  }
  
  function handleEdit(id: number) {
    editingPolicyId = id;
    showEditor = true;
    // TODO: Implement editor modal
    alert(`Editing SLA policy ${id} - editor will be implemented in the next iteration`);
  }
</script>

<div class="sla-settings">
  <div class="header mb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="flex items-center gap-3 mb-2">
          <Shield class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">SLA Management</h1>
        </div>
        <p class="text-gray-600">
          Define response and resolution time targets for conversations
        </p>
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div>
            <span class="text-2xl font-bold text-primary">{totalCount}</span>
            <span class="text-gray-600 ml-2">Active Policies</span>
          </div>
        </div>
      </div>
      
      <Button onclick={handleAdd} size="lg">
        <Plus class="h-5 w-5 mr-2" />
        Add SLA Policy
      </Button>
    </div>
  </div>
  
  <div class="content">
    <SLAList 
      {policies}
      {isLoading}
      onedit={handleEdit}
    />
  </div>
</div>

<style>
  .sla-settings {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
  }
  
  .header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1.5rem;
  }
</style>
