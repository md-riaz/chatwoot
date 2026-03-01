<script lang="ts">
  /**
   * Custom Attributes Management Page
   * Vue parity: app/javascript/dashboard/routes/dashboard/settings/attributes/Index.vue
   */

  import { onMount } from 'svelte';
  import { page } from '$app/stores';
  import { attributesStore } from '$lib/stores/attributes.svelte';
  import { Button } from '$lib/components/ui/button';
  import { Badge } from '$lib/components/ui/badge';
  import * as AlertDialog from '$lib/components/ui/alert-dialog';
  import type { CustomAttribute } from '$lib/api/attributes';
  import AttributeDialog from '$lib/components/attributes/AttributeDialog.svelte';
  import { Plus, Pen, Trash2 } from 'lucide-svelte';
  import BaseSettingsHeader from '../components/BaseSettingsHeader.svelte';

  let accountId = $derived($page.params.accountId);
  let attributes = $derived(attributesStore.sortedAttributes);
  let isLoading = $derived(attributesStore.isLoading);

  let showCreateDialog = $state(false);
  let showEditDialog = $state(false);
  let editingAttribute = $state<CustomAttribute | null>(null);

  // Delete dialog state
  let showDeleteDialog = $state(false);
  let deletingAttribute = $state<CustomAttribute | null>(null);

  // Tabs: conversation vs contact (matching Vue)
  let selectedTab = $state<'conversation_attribute' | 'contact_attribute'>(
    'conversation_attribute'
  );

  let filteredAttributes = $derived(
    attributes.filter(a => a.attributeModel === selectedTab)
  );

  onMount(() => {
    attributesStore.fetchAttributes();
  });

  function handleCreateAttribute() {
    showCreateDialog = true;
  }

  async function handleSubmitCreate(event: CustomEvent) {
    const data = event.detail;
    await attributesStore.createAttribute(data);
    attributesStore.fetchAttributes();
  }

  function handleEditAttribute(attribute: CustomAttribute) {
    editingAttribute = attribute;
    showEditDialog = true;
  }

  async function handleSubmitEdit(event: CustomEvent) {
    if (!editingAttribute) return;
    const data = event.detail;
    await attributesStore.updateAttribute(editingAttribute.id, data);
    attributesStore.fetchAttributes();
    editingAttribute = null;
  }

  function handleDeleteClick(attribute: CustomAttribute) {
    deletingAttribute = attribute;
    showDeleteDialog = true;
  }

  async function confirmDelete() {
    if (!deletingAttribute) return;
    await attributesStore.deleteAttribute(deletingAttribute.id);
    showDeleteDialog = false;
    deletingAttribute = null;
  }
</script>

<div class="flex flex-col w-full h-full gap-8">
  <BaseSettingsHeader
    title="Custom Attributes"
    description="A custom attribute tracks additional details about your contacts or conversations — such as the subscription plan or the date of their first purchase. You can add different types of custom attributes."
    linkText="Learn more about custom attributes"
    linkUrl="https://www.chatwoot.com/hc/user-guide/articles/1677579748-how-to-create-and-manage-custom-attributes"
  >
    {#snippet actions()}
      <Button onclick={handleCreateAttribute}>
        <Plus class="mr-2 h-4 w-4" />
        Add Attribute
      </Button>
    {/snippet}
  </BaseSettingsHeader>

  <main>
    <!-- Tabs matching Vue's TabBar -->
    <div class="flex gap-1 mb-6 max-w-xl">
      <button
        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {selectedTab ===
        'conversation_attribute'
          ? 'bg-primary text-primary-foreground'
          : 'bg-muted text-muted-foreground hover:text-foreground'}"
        onclick={() => (selectedTab = 'conversation_attribute')}
      >
        Conversation
      </button>
      <button
        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {selectedTab ===
        'contact_attribute'
          ? 'bg-primary text-primary-foreground'
          : 'bg-muted text-muted-foreground hover:text-foreground'}"
        onclick={() => (selectedTab = 'contact_attribute')}
      >
        Contact
      </button>
    </div>

    {#if isLoading}
      <div class="flex justify-center items-center py-20">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"
        ></div>
      </div>
    {:else if filteredAttributes.length === 0}
      <p
        class="flex-1 py-20 text-foreground flex items-center justify-center text-base"
      >
        No custom attributes found for this category.
      </p>
    {:else}
      <div class="grid gap-3">
        {#each filteredAttributes as attribute (attribute.id)}
          <div
            class="flex items-center justify-between p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors"
          >
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-3 mb-1">
                <span class="font-medium text-foreground"
                  >{attribute.attributeDisplayName}</span
                >
                <Badge variant="secondary" class="text-xs capitalize">
                  {attribute.attributeDisplayType}
                </Badge>
              </div>
              <span class="text-sm text-muted-foreground"
                >Key: {attribute.attributeKey}</span
              >
              {#if attribute.attributeValues && attribute.attributeValues.length > 0}
                <div class="mt-2 flex flex-wrap gap-1">
                  {#each attribute.attributeValues as value}
                    <Badge variant="outline" class="text-xs">{value}</Badge>
                  {/each}
                </div>
              {/if}
            </div>
            <div class="flex gap-1 ml-4">
              <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-muted-foreground hover:text-foreground"
                title="Edit"
                onclick={() => handleEditAttribute(attribute)}
              >
                <Pen class="h-4 w-4" />
              </Button>
              <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                title="Delete"
                onclick={() => handleDeleteClick(attribute)}
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        {/each}
      </div>
    {/if}
  </main>
</div>

<!-- Attribute Dialogs -->
<AttributeDialog
  bind:open={showCreateDialog}
  mode="create"
  on:submit={handleSubmitCreate}
/>

<AttributeDialog
  bind:open={showEditDialog}
  mode="edit"
  attribute={editingAttribute}
  on:submit={handleSubmitEdit}
/>

<!-- Delete Confirm Dialog -->
<AlertDialog.Root bind:open={showDeleteDialog}>
  <AlertDialog.Content>
    <AlertDialog.Header>
      <AlertDialog.Title>Delete Attribute</AlertDialog.Title>
      <AlertDialog.Description>
        Are you sure you want to delete <strong
          >{deletingAttribute?.attributeDisplayName}</strong
        >? This will remove the attribute from all {deletingAttribute?.attributeModel ===
        'contact_attribute'
          ? 'contacts'
          : 'conversations'}.
      </AlertDialog.Description>
    </AlertDialog.Header>
    <AlertDialog.Footer>
      <AlertDialog.Cancel
        onclick={() => {
          showDeleteDialog = false;
          deletingAttribute = null;
        }}
      >
        Cancel
      </AlertDialog.Cancel>
      <AlertDialog.Action
        class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
        onclick={confirmDelete}
      >
        Yes, delete {deletingAttribute?.attributeDisplayName}
      </AlertDialog.Action>
    </AlertDialog.Footer>
  </AlertDialog.Content>
</AlertDialog.Root>
