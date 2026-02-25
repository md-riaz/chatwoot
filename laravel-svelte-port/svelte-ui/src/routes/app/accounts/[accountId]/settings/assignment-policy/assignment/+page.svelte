<script lang="ts">
  /**
   * Agent Assignment Policy Index Page
   * Lists all assignment policies with create/edit/delete actions
   * Vue parity: pages/AgentAssignmentIndexPage.vue
   */

  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { goto } from '$app/navigation';
  import { assignmentPoliciesStore } from '$lib/stores/assignmentPolicies.svelte';
  import { Button } from '$lib/components/ui/button';
  import BaseSettingsHeader from '../../components/BaseSettingsHeader.svelte';
  import AssignmentPolicyCard from '$lib/components/assignment-policy/AssignmentPolicyCard.svelte';
  import ConfirmDeletePolicyDialog from '$lib/components/assignment-policy/ConfirmDeletePolicyDialog.svelte';
  import { Plus, ChevronLeft } from 'lucide-svelte';

  let accountId = $derived($page.params.accountId);
  let policies = $derived(assignmentPoliciesStore.records);
  let isLoading = $derived(assignmentPoliciesStore.isLoading);

  // Delete dialog state
  let showDeleteDialog = $state(false);
  let deletingPolicyId = $state<number | null>(null);

  onMount(() => {
    assignmentPoliciesStore.fetchAll();
  });

  function handleBack() {
    goto(`/app/accounts/${accountId}/settings/assignment-policy`);
  }

  function handleCreate() {
    goto(
      `/app/accounts/${accountId}/settings/assignment-policy/assignment/create`
    );
  }

  function handleEdit(id: number) {
    goto(
      `/app/accounts/${accountId}/settings/assignment-policy/assignment/edit/${id}`
    );
  }

  function handleDelete(id: number) {
    deletingPolicyId = id;
    showDeleteDialog = true;
  }

  async function confirmDelete(id: number) {
    try {
      await assignmentPoliciesStore.deletePolicy(id);
    } catch {
      // Error handled in store
    }
    showDeleteDialog = false;
    deletingPolicyId = null;
  }
</script>

<div class="flex flex-col max-w-4xl mx-auto w-full p-6">
  <!-- Breadcrumb header -->
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-2">
      <Button variant="ghost" size="sm" onclick={handleBack} class="gap-1">
        <ChevronLeft class="h-4 w-4" />
        Assignment Policy
      </Button>
      <span class="text-muted-foreground">/</span>
      <span class="font-medium">Agent Assignment Policy</span>
    </div>
    <Button onclick={handleCreate}>
      <Plus class="mr-2 h-4 w-4" />
      Create Policy
    </Button>
  </div>

  <!-- Content -->
  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
      ></div>
    </div>
  {:else if policies.length === 0}
    <div
      class="text-center py-16 border rounded-lg bg-card text-card-foreground"
    >
      <div class="mb-4">
        <svg
          class="mx-auto h-16 w-16 text-muted-foreground"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
      </div>
      <h2 class="text-xl font-semibold mb-2">No assignment policies found</h2>
      <p class="text-muted-foreground mb-4">
        Create your first assignment policy to start routing conversations
        automatically.
      </p>
      <Button onclick={handleCreate}>
        <Plus class="mr-2 h-4 w-4" />
        Create Your First Policy
      </Button>
    </div>
  {:else}
    <div class="flex flex-col gap-4">
      {#each policies as policy}
        <AssignmentPolicyCard
          id={policy.id}
          name={policy.name}
          description={policy.description}
          assignmentOrder={policy.assignmentOrder}
          conversationPriority={policy.conversationPriority}
          assignedInboxCount={policy.assignedInboxCount}
          enabled={policy.enabled}
          onedit={handleEdit}
          ondelete={handleDelete}
        />
      {/each}
    </div>
  {/if}
</div>

<ConfirmDeletePolicyDialog
  bind:open={showDeleteDialog}
  policyId={deletingPolicyId}
  onconfirm={confirmDelete}
/>
