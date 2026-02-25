<script lang="ts">
  /**
   * Agent Assignment Policy Edit Page
   * Vue parity: pages/AgentAssignmentEditPage.vue
   */

  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { assignmentPoliciesStore } from '$lib/stores/assignmentPolicies.svelte';
  import { Button } from '$lib/components/ui/button';
  import AgentAssignmentPolicyForm from '$lib/components/assignment-policy/AgentAssignmentPolicyForm.svelte';
  import { ChevronLeft } from 'lucide-svelte';

  let accountId = $derived($page.params.accountId);
  let policyId = $derived(Number($page.params.id));
  let policy = $derived(assignmentPoliciesStore.getPolicyById(policyId));
  let isUpdating = $derived(assignmentPoliciesStore.isUpdating);
  let isFetchingItem = $derived(assignmentPoliciesStore.isFetchingItem);

  let formData = $derived(
    policy
      ? {
          name: policy.name || '',
          description: policy.description || '',
          enabled: policy.enabled || false,
          assignmentOrder: policy.assignmentOrder || 'round_robin',
          conversationPriority:
            policy.conversationPriority || 'earliest_created',
          fairDistributionLimit: policy.fairDistributionLimit || 100,
          fairDistributionWindow: policy.fairDistributionWindow || 3600,
        }
      : undefined
  );

  onMount(async () => {
    // Fetch policy if not already in store
    if (!policy) {
      await assignmentPoliciesStore.fetch(policyId);
    }
    // Fetch inboxes for this policy
    assignmentPoliciesStore.fetchInboxes(policyId);
  });

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/assignment-policy/assignment`);
  }

  async function handleSubmit(data: any) {
    try {
      await assignmentPoliciesStore.update(policyId, data);
    } catch {
      // Error handled in store
    }
  }
</script>

<div class="flex flex-col max-w-4xl mx-auto w-full p-6">
  <!-- Breadcrumb header -->
  <div class="flex items-center gap-2 mb-6">
    <Button variant="ghost" size="sm" onclick={handleBack} class="gap-1">
      <ChevronLeft class="h-4 w-4" />
      Agent Assignment Policy
    </Button>
    <span class="text-muted-foreground">/</span>
    <span class="font-medium">Edit Policy</span>
  </div>

  {#if isFetchingItem}
    <div class="flex justify-center items-center py-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
      ></div>
    </div>
  {:else}
    <div class="xl:px-20">
      <AgentAssignmentPolicyForm
        mode="EDIT"
        initialData={formData}
        isLoading={isUpdating}
        onsubmit={handleSubmit}
      />
    </div>
  {/if}
</div>
