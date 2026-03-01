<script lang="ts">
  /**
   * Labels Management Page
   * Vue parity: app/javascript/dashboard/routes/dashboard/settings/labels/Index.vue
   */

  import { onMount } from 'svelte';
  import { Plus, Pen, Trash2 } from 'lucide-svelte';
  import { Button } from '$lib/components/ui/button';
  import { labelsStore } from '$lib/stores/labels.svelte';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';

  const labels = $derived(labelsStore.allLabels);
  const isLoading = $derived(labelsStore.isLoading);

  onMount(async () => {
    await labelsStore.fetchLabels();
  });
</script>

<div class="flex flex-col w-full h-full gap-8">
  <BaseSettingsHeader
    title="Labels"
    description="Labels help you categorize and prioritize conversations. You can assign a label to a conversation from the sidebar panel."
    linkText="Learn more about labels"
    linkUrl="https://www.chatwoot.com/hc/user-guide/articles/1677579743-how-to-create-and-manage-labels"
  >
    {#snippet actions()}
      <Button>
        <Plus class="mr-2 h-4 w-4" />
        Add Label
      </Button>
    {/snippet}
  </BaseSettingsHeader>

  <main>
    {#if isLoading}
      <div class="flex justify-center items-center py-20">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
        ></div>
      </div>
    {:else if labels.length === 0}
      <p
        class="flex-1 py-20 text-foreground flex items-center justify-center text-base"
      >
        No labels found. Create labels to categorize conversations.
      </p>
    {:else}
      <table class="min-w-full overflow-x-auto divide-y divide-border">
        <thead>
          <tr>
            <th class="py-4 pr-4 text-left font-semibold text-muted-foreground">
              Name
            </th>
            <th class="py-4 pr-4 text-left font-semibold text-muted-foreground">
              Description
            </th>
            <th class="py-4 pr-4 text-left font-semibold text-muted-foreground">
              Color
            </th>
            <th class="py-4 text-right font-semibold text-muted-foreground"
            ></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border text-foreground">
          {#each labels as label (label.id)}
            <tr>
              <td class="py-4 pr-4">
                <span class="mb-1 font-medium break-words">
                  {label.title}
                </span>
              </td>
              <td class="py-4 pr-4 text-muted-foreground">
                {label.description || ''}
              </td>
              <td class="py-4 pr-4 leading-6">
                <div class="flex items-center">
                  <span
                    class="w-4 h-4 mr-2 border border-border rounded"
                    style="background-color: {label.color}"
                  ></span>
                  {label.color}
                </div>
              </td>
              <td class="py-4 min-w-[80px]">
                <div class="flex gap-1 justify-end">
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-muted-foreground hover:text-foreground"
                    title="Edit"
                  >
                    <Pen class="h-4 w-4" />
                  </Button>
                  <Button
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                    title="Delete"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </td>
            </tr>
          {/each}
        </tbody>
      </table>
    {/if}
  </main>
</div>
