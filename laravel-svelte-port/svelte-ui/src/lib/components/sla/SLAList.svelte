<script lang="ts">
  import { slaStore } from '$lib/stores/sla.svelte';
  import type { SLAPolicy } from '$lib/api/sla';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import ConfirmDialog from '$lib/components/ConfirmDialog.svelte';
  import { Edit, Trash2, AlertCircle, Clock } from '@lucide/svelte';
  import { toast } from 'svelte-sonner';

  interface Props {
    policies: SLAPolicy[];
    isLoading: boolean;
    onedit?: (id: number) => void;
    onrefresh?: () => void | Promise<void>;
  }

  let { policies, isLoading, onedit, onrefresh }: Props = $props();

  const isDeleting = $derived(slaStore.isDeleting);

  let showDeleteDialog = $state(false);
  let selectedPolicy = $state<SLAPolicy | null>(null);

  function formatTime(seconds: number): string {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);

    if (hours > 0) {
      return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
  }

  function requestDelete(policy: SLAPolicy) {
    selectedPolicy = policy;
    showDeleteDialog = true;
  }

  async function handleDelete() {
    if (!selectedPolicy) return;

    const result = await slaStore.deletePolicy(selectedPolicy.id);

    if (result) {
      toast.success('SLA policy deleted successfully');
      await onrefresh?.();
    } else {
      toast.error(slaStore.error || 'Failed to delete SLA policy');
    }

    showDeleteDialog = false;
    selectedPolicy = null;
  }

  function handleEdit(policy: SLAPolicy) {
    onedit?.(policy.id);
  }
</script>

<div class="sla-list space-y-4">
  {#if isLoading}
    <div class="space-y-4">
      {#each Array(3) as _, i (i)}
        <Card.Root>
          <Card.Content class="p-6">
            <div class="animate-pulse">
              <div class="mb-2 h-4 w-1/3 rounded bg-gray-200"></div>
              <div class="h-3 w-2/3 rounded bg-gray-200"></div>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {:else if policies.length === 0}
    <Card.Root>
      <Card.Content class="p-12 text-center">
        <AlertCircle class="mx-auto mb-4 h-12 w-12 text-gray-400" />
        <h3 class="mb-2 text-lg font-semibold">No SLA policies yet</h3>
        <p class="mb-4 text-gray-600">
          Create your first SLA policy to track response and resolution times
        </p>
      </Card.Content>
    </Card.Root>
  {:else}
    {#each policies as policy (policy.id)}
      <Card.Root class="transition-shadow hover:shadow-md">
        <Card.Content class="p-6">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="mb-2 flex items-center gap-3">
                <h3 class="text-lg font-semibold">{policy.name}</h3>
                {#if policy.onlyDuringBusinessHours}
                  <span
                    class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-700"
                    >Business Hours</span
                  >
                {/if}
              </div>

              {#if policy.description}
                <p class="mb-3 text-sm text-gray-600">{policy.description}</p>
              {/if}

              <div class="grid grid-cols-3 gap-4 text-sm">
                <div class="flex items-center gap-2">
                  <Clock class="h-4 w-4 text-blue-600" />
                  <div>
                    <p class="text-gray-500">First Response</p>
                    <p class="font-medium">
                      {formatTime(policy.firstResponseTime)}
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <Clock class="h-4 w-4 text-purple-600" />
                  <div>
                    <p class="text-gray-500">Next Response</p>
                    <p class="font-medium">
                      {formatTime(policy.nextResponseTime)}
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <Clock class="h-4 w-4 text-green-600" />
                  <div>
                    <p class="text-gray-500">Resolution</p>
                    <p class="font-medium">
                      {formatTime(policy.resolutionTime)}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Button
                variant="ghost"
                size="icon"
                onclick={() => handleEdit(policy)}
                title="Edit SLA policy"
              >
                <Edit class="h-4 w-4" />
              </Button>

              <Button
                variant="ghost"
                size="icon"
                onclick={() => requestDelete(policy)}
                disabled={isDeleting}
                title="Delete SLA policy"
              >
                <Trash2 class="h-4 w-4 text-red-600" />
              </Button>
            </div>
          </div>
        </Card.Content>
      </Card.Root>
    {/each}
  {/if}
</div>

<ConfirmDialog
  open={showDeleteDialog}
  title="Delete SLA Policy"
  description={selectedPolicy
    ? `Are you sure you want to delete \"${selectedPolicy.name}\"?`
    : 'Are you sure?'}
  confirmText="Delete"
  variant="destructive"
  onConfirm={handleDelete}
  onCancel={() => {
    showDeleteDialog = false;
    selectedPolicy = null;
  }}
/>

<style>
  .sla-list {
    width: 100%;
  }
</style>
