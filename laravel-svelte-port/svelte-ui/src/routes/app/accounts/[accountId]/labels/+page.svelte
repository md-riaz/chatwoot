<script lang="ts">
  import { onMount } from 'svelte';
  import { Tag, Edit, Trash2, Plus } from '@lucide/svelte';
  import * as Card from '$lib/components/ui/card';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import { Skeleton } from '$lib/components/ui/skeleton';
  import { labelsStore } from '$lib/stores/labels.svelte';

  // Get labels from store
  const labels = $derived(labelsStore.allLabels);
  const isLoading = $derived(labelsStore.isLoading);

  onMount(async () => {
    // Fetch labels from store
    await labelsStore.fetchLabels();
  });
</script>

<div class="max-w-[60rem] mx-auto p-6">
  <!-- Header -->
  <div
    class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
  >
    <div>
      <h1 class="text-3xl font-bold">Label Management</h1>
      <p class="text-muted-foreground">Organize conversations with labels</p>
    </div>
    <Button>
      <Plus class="mr-2 h-4 w-4" />
      New Label
    </Button>
  </div>

  <!-- Label count -->
  <div class="mb-4">
    <p class="text-sm text-muted-foreground">
      {labels.length}
      {labels.length === 1 ? 'label' : 'labels'}
    </p>
  </div>

  <!-- Loading state -->
  {#if isLoading}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      {#each Array(8) as _}
        <Card.Root>
          <Card.Content class="p-4">
            <div class="space-y-3">
              <div class="flex items-center gap-2">
                <Skeleton class="h-4 w-4 rounded-full" />
                <Skeleton class="h-5 w-32" />
              </div>
              <Skeleton class="h-4 w-full" />
              <Skeleton class="h-6 w-20" />
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {:else if labels.length === 0}
    <!-- Empty state -->
    <Card.Root>
      <Card.Content class="flex flex-col items-center justify-center py-12">
        <Tag class="mb-4 h-12 w-12 text-muted-foreground" />
        <h3 class="mb-2 text-lg font-semibold">No labels yet</h3>
        <p class="mb-4 text-center text-sm text-muted-foreground">
          Create labels to organize and categorize your conversations
        </p>
        <Button>
          <Plus class="mr-2 h-4 w-4" />
          Create Label
        </Button>
      </Card.Content>
    </Card.Root>
  {:else}
    <!-- Labels grid -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      {#each labels as label (label.id)}
        <Card.Root
          class="transition-all hover:scale-105 hover:shadow-md"
          style="border-color: {label.color}; background: {label.color}10;"
        >
          <Card.Content class="p-4">
            <!-- Label header -->
            <div class="mb-3 flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div
                  class="h-4 w-4 rounded-full"
                  style="background-color: {label.color};"
                ></div>
                <h3 class="font-semibold">{label.title}</h3>
              </div>
            </div>

            <!-- Label description -->
            {#if label.description}
              <p class="mb-3 text-sm text-muted-foreground line-clamp-2">
                {label.description}
              </p>
            {/if}

            <!-- Usage count -->
            <div class="mb-3">
              <Badge variant="secondary" class="text-xs">
                {label.conversationsCount || 0} conversations
              </Badge>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <Button variant="outline" size="sm" class="flex-1">
                <Edit class="mr-1 h-3 w-3" />
                Edit
              </Button>
              <Button variant="outline" size="sm" class="flex-1">
                <Trash2 class="mr-1 h-3 w-3" />
                Delete
              </Button>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {/if}
</div>
