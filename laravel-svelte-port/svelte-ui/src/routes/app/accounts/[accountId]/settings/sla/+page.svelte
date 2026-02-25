<script lang="ts">
  import { onMount } from 'svelte';
  import { toast } from 'svelte-sonner';
  import { slaStore } from '$lib/stores/sla.svelte';
  import type { CreateSLAPolicyParams } from '$lib/api/sla';
  import SLAList from '$lib/components/sla/SLAList.svelte';
  import SLAFormDialog from '$lib/components/sla/SLAFormDialog.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Plus, Shield } from '@lucide/svelte';

  const policies = $derived(slaStore.sortedPolicies);
  const isLoading = $derived(slaStore.isLoading);
  const isSaving = $derived(slaStore.isSaving);
  const totalCount = $derived(slaStore.policyCount);

  let showEditor = $state(false);
  let editingPolicyId = $state<number | null>(null);

  const editingPolicy = $derived(
    editingPolicyId ? slaStore.getPolicyById(editingPolicyId) : null
  );

  onMount(() => {
    slaStore.fetchPolicies();
  });

  function handleAdd() {
    editingPolicyId = null;
    showEditor = true;
  }

  function handleEdit(id: number) {
    editingPolicyId = id;
    showEditor = true;
  }

  async function handleSubmit(payload: CreateSLAPolicyParams) {
    const result = editingPolicyId
      ? await slaStore.updatePolicy(editingPolicyId, payload)
      : await slaStore.createPolicy(payload);

    if (result) {
      toast.success(
        editingPolicyId
          ? 'SLA policy updated successfully.'
          : 'SLA policy created successfully.'
      );
      showEditor = false;
      editingPolicyId = null;
      await slaStore.fetchPolicies();
      return;
    }

    toast.error(slaStore.error || 'Failed to save SLA policy.');
  }
</script>

<div class="space-y-6">
  <div class="mb-6 border-b border-border pb-6">
    <div class="flex items-start justify-between gap-4">
      <div class="flex-1">
        <div class="mb-2 flex items-center gap-3">
          <Shield class="h-8 w-8 text-primary" />
          <h1 class="text-3xl font-bold">SLA Management</h1>
        </div>
        <p class="text-gray-600">
          Define response and resolution time targets for conversations
        </p>
        <div class="mt-4 flex items-center gap-6 text-sm">
          <div>
            <span class="text-2xl font-bold text-primary">{totalCount}</span>
            <span class="ml-2 text-gray-600">Active Policies</span>
          </div>
        </div>
      </div>

      <Button onclick={handleAdd} size="lg">
        <Plus class="mr-2 h-5 w-5" />
        Add SLA Policy
      </Button>
    </div>
  </div>

  <div class="content">
    <SLAList
      {policies}
      {isLoading}
      onedit={handleEdit}
      onrefresh={() => slaStore.fetchPolicies()}
    />
  </div>
</div>

<SLAFormDialog
  bind:open={showEditor}
  mode={editingPolicyId ? 'edit' : 'create'}
  policy={editingPolicy}
  isSubmitting={isSaving}
  onSubmit={handleSubmit}
  onClose={() => {
    showEditor = false;
    editingPolicyId = null;
  }}
/>
