<script lang="ts">
  import { macrosStore } from '$lib/stores/macros.svelte';
  import type { Macro } from '$lib/api/macros';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import { 
    Edit, 
    Trash2, 
    Play,
    AlertCircle,
    Globe,
    User,
    Users
  } from '@lucide/svelte';
  import { toast } from 'svelte-sonner';
  
  interface Props {
    macros: Macro[];
    isLoading: boolean;
    onedit?: (id: number) => void;
    onexecute?: (id: number) => void;
  }
  
  let { macros, isLoading, onedit, onexecute }: Props = $props();
  
  const isDeleting = $derived(macrosStore.isDeleting);
  
  function getVisibilityIcon(visibility: string) {
    switch (visibility) {
      case 'global': return Globe;
      case 'personal': return User;
      case 'team': return Users;
      default: return Globe;
    }
  }
  
  function getVisibilityLabel(visibility: string) {
    switch (visibility) {
      case 'global': return 'Global';
      case 'personal': return 'Personal';
      case 'team': return 'Team';
      default: return visibility;
    }
  }
  
  function getVisibilityColor(visibility: string) {
    switch (visibility) {
      case 'global': return 'default';
      case 'personal': return 'secondary';
      case 'team': return 'outline';
      default: return 'default';
    }
  }
  
  async function handleDelete(macro: Macro) {
    if (!confirm(`Are you sure you want to delete "${macro.name}"?`)) {
      return;
    }
    
    const result = await macrosStore.deleteMacro(macro.id);
    
    if (result) {
      toast.success('Macro deleted successfully');
    } else {
      toast.error('Failed to delete macro');
    }
  }
  
  function handleEdit(macro: Macro) {
    onedit?.(macro.id);
  }
  
  function handleExecute(macro: Macro) {
    onexecute?.(macro.id);
  }
</script>

<div class="macros-list space-y-4">
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
  {:else if macros.length === 0}
    <Card.Root>
      <Card.Content class="p-12 text-center">
        <AlertCircle class="h-12 w-12 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold mb-2">No macros yet</h3>
        <p class="text-gray-600 mb-4">
          Create your first macro to automate repetitive tasks
        </p>
      </Card.Content>
    </Card.Root>
  {:else}
    {#each macros as macro (macro.id)}
      <Card.Root class="transition-shadow hover:shadow-md">
        <Card.Content class="p-6">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-semibold">{macro.name}</h3>
                <Badge variant={getVisibilityColor(macro.visibility)}>
                  {@const VisibilityIcon = getVisibilityIcon(macro.visibility)}
                  <VisibilityIcon class="h-3 w-3 mr-1" />
                  {getVisibilityLabel(macro.visibility)}
                </Badge>
              </div>
              
              <div class="flex items-center gap-4 text-sm text-gray-500">
                <span>
                  {macro.actions.length} action{macro.actions.length !== 1 ? 's' : ''}
                </span>
                <span>•</span>
                <span>
                  Created {new Date(macro.createdAt).toLocaleDateString()}
                </span>
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                onclick={() => handleExecute(macro)}
                title="Execute macro"
              >
                <Play class="h-4 w-4 mr-1" />
                Run
              </Button>
              
              <Button
                variant="ghost"
                size="icon"
                onclick={() => handleEdit(macro)}
                title="Edit macro"
              >
                <Edit class="h-4 w-4" />
              </Button>
              
              <Button
                variant="ghost"
                size="icon"
                onclick={() => handleDelete(macro)}
                disabled={isDeleting}
                title="Delete macro"
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
  .macros-list {
    width: 100%;
  }
</style>
