<script lang="ts">
  /**
   * Agent Assignment Policy Create Page
   * Vue parity: pages/AgentAssignmentCreatePage.vue
   */

  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { assignmentPoliciesStore } from '$lib/stores/assignmentPolicies.svelte';
  import { Button } from '$lib/components/ui/button';
  import AgentAssignmentPolicyForm from '$lib/components/assignment-policy/AgentAssignmentPolicyForm.svelte';
  import { ChevronLeft } from 'lucide-svelte';

  let accountId = $derived($page.params.accountId);
  let isCreating = $derived(assignmentPoliciesStore.isCreating);

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/assignment-policy/assignment`);
  }

  async function handleSubmit(data: any) {
    try {
      const policy = await assignmentPoliciesStore.create(data);
      if (policy) {
        goto(
          `/app/accounts/${accountId}/settings/assignment-policy/assignment/edit/${policy.id}`
        );
      }
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
    <span class="font-medium">Create Policy</span>
  </div>

  <div class="xl:px-20">
    <AgentAssignmentPolicyForm
      mode="CREATE"
      isLoading={isCreating}
      onsubmit={handleSubmit}
    />
  </div>
</div>
