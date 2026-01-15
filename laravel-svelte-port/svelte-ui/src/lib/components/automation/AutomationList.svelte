<script lang="ts">
  import { onMount } from 'svelte';
  import { automationStore } from '$lib/stores/automation.svelte';
  import type { Automation } from '$lib/api/automation';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import { Switch } from '$lib/components/ui/switch';
  import { 
    Play, 
    Pause, 
    Copy, 
    Edit, 
    Trash2, 
    AlertCircle 
  } from '@lucide/svelte';
  import { toast } from 'svelte-sonner';
  
  interface Props {
    automations: Automation[];
    isLoading: boolean;
    onedit?: (id: number) => void;
  }
  
  let { automations, isLoading, onedit }: Props = $props();
  
  const isSaving = $derived(automationStore.isSaving);
  const isDeleting = $derived(automationStore.isDeleting);
  
  async function handleToggleActive(automation: Automation) {
    const result = await automationStore.updateAutomation(automation.id, {
      active: !automation.active
    });
    
    if (result) {
      toast.success(`Automation ${automation.active ? 'activated' : 'deactivated'}`);
    } else {
      toast.error('Failed to update automation');
    }
  }
  
  async function handleClone(automation: Automation) {
    const result = await automationStore.cloneAutomation(automation.id);
    
    if (result) {
      toast.success('Automation cloned successfully');
    } else {
      toast.error('Failed to clone automation');
    }
  }
  
  async function handleDelete(automation: Automation) {
    if (!confirm(`Are you sure you want to delete "${automation.name}"?`)) {
      return;
    }
    
    const result = await automationStore.deleteAutomation(automation.id);
    
    if (result) {
      toast.success('Automation deleted successfully');
    } else {
      toast.error('Failed to delete automation');
    }
  }
  
  function handleEdit(automation: Automation) {
    onedit?.(automation.id);
  }
</script>

<div class="automation-list space-y-4">
  {#if isLoading}
    <div class="space-y-4">
      {#each Array(3) as _, i (i)}
        <Card.Root>
          <Card.Content class="p-6">
            <div class="animate-pulse">
              <div class="h-4 bg-gray-200 rounded w-1/3 mb-2"></div>
              <div class="h-3 bg-gray-200 rounded w-2/3"></div>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {:else if automations.length === 0}
    <Card.Root>
      <Card.Content class="p-12 text-center">
        <AlertCircle class="h-12 w-12 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold mb-2">No automation rules yet</h3>
        <p class="text-gray-600 mb-4">
          Create your first automation to save time and improve efficiency
        </p>
      </Card.Content>
    </Card.Root>
  {:else}
    {#each automations as automation (automation.id)}
      <Card.Root class="transition-shadow hover:shadow-md">
        <Card.Content class="p-6">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-semibold">{automation.name}</h3>
                <Badge variant={automation.active ? 'default' : 'secondary'}>
                  {automation.active ? 'Active' : 'Inactive'}
                </Badge>
              </div>
              
              {#if automation.description}
                <p class="text-sm text-gray-600 mb-3">
                  {automation.description}
                </p>
              {/if}
              
              <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>
                  {automation.conditions.length} condition{automation.conditions.length !== 1 ? 's' : ''}
                </span>
                <span>•</span>
                <span>
                  {automation.actions.length} action{automation.actions.length !== 1 ? 's' : ''}
                </span>
                <span>•</span>
                <span>
                  Created {new Date(automation.createdAt).toLocaleDateString()}
                </span>
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <Switch
                checked={automation.active}
                onclick={() => handleToggleActive(automation)}
                disabled={isSaving}
                aria-label={automation.active ? 'Deactivate automation' : 'Activate automation'}
              />
              
              <Button
                variant="ghost"
                size="icon"
                onclick={() => handleEdit(automation)}
                title="Edit automation"
              >
                <Edit class="h-4 w-4" />
              </Button>
              
              <Button
                variant="ghost"
                size="icon"
                onclick={() => handleClone(automation)}
                disabled={isSaving}
                title="Clone automation"
              >
                <Copy class="h-4 w-4" />
              </Button>
              
              <Button
                variant="ghost"
                size="icon"
                onclick={() => handleDelete(automation)}
                disabled={isDeleting}
                title="Delete automation"
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

<style>
  .automation-list {
    width: 100%;
  }
</style>
