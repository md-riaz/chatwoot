<script lang="ts">
  /**
   * Custom Attributes Management Page
   * Create and manage custom attributes for contacts and conversations
   */

  import { onMount } from 'svelte';
  import { goto } from '$app/navigation';
  import { page } from '$app/stores';
  import { attributesStore } from '$lib/stores/attributes.svelte';
  import { Button } from '$lib/components/ui/button';
  import * as Card from '$lib/components/ui/card';
  import { Badge } from '$lib/components/ui/badge';
  import type { CustomAttribute } from '$lib/api/attributes';
  import AttributeDialog from '$lib/components/attributes/AttributeDialog.svelte';

  let accountId = $derived($page.params.accountId);
  let attributes = $derived(attributesStore.sortedAttributes);
  let isLoading = $derived(attributesStore.isLoading);

  let showCreateDialog = $state(false);
  let showEditDialog = $state(false);
  let editingAttribute = $state<CustomAttribute | null>(null);

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

  function handleEditAttribute(event: Event, attribute: CustomAttribute) {
    event.stopPropagation();
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

  async function handleDeleteAttribute(
    event: Event,
    attributeId: number,
    attributeName: string
  ) {
    event.stopPropagation();
    const attributeData = attributes.find((a) => a.id === attributeId);
    if (
      confirm(
        `Are you sure you want to delete "${attributeName}"? This will remove the attribute from all ${attributeData?.attributeModel === 'contact_attribute' ? 'contacts' : 'conversations'}.`
      )
    ) {
      await attributesStore.deleteAttribute(attributeId);
    }
  }

  function getTypeBadgeClass(type: string) {
    const classes: Record<string, string> = {
      text: 'bg-blue-100 text-blue-800',
      number: 'bg-green-100 text-green-800',
      date: 'bg-purple-100 text-purple-800',
      list: 'bg-orange-100 text-orange-800',
      checkbox: 'bg-pink-100 text-pink-800',
    };
    return classes[type] || 'bg-gray-100 text-gray-800';
  }

  function getAppliesToBadgeClass(appliesTo: string) {
    return appliesTo === 'contact_attribute'
      ? 'bg-indigo-100 text-indigo-800'
      : 'bg-teal-100 text-teal-800';
  }

  function getAppliesToLabel(appliesTo: string) {
    return appliesTo === 'contact_attribute' ? 'contact' : 'conversation';
  }
</script>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-3xl font-bold">Custom Attributes</h1>
      <p class="text-muted-foreground mt-2">
        Define custom fields for contacts and conversations
      </p>
    </div>
    <Button onclick={handleCreateAttribute}>Create Attribute</Button>
  </div>

  {#if isLoading}
    <div class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>
  {:else if attributes.length === 0}
    <Card.Root class="text-center py-12">
      <Card.Content>
        <div class="mb-4">
          <svg
            class="mx-auto h-16 w-16 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"
            />
          </svg>
        </div>
        <h2 class="text-xl font-semibold mb-2">No custom attributes yet</h2>
        <p class="text-gray-600 mb-4">
          Create custom attributes to capture additional information
        </p>
        <Button onclick={handleCreateAttribute}>
          Create Your First Attribute
        </Button>
      </Card.Content>
    </Card.Root>
  {:else}
    <div class="space-y-3">
      {#each attributes as attribute}
        <Card.Root class="hover:shadow-md transition-shadow">
          <Card.Content class="p-6">
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="font-semibold text-lg">{attribute.attributeDisplayName}</h3>
                  <span
                    class="px-2 py-1 rounded text-xs font-medium {getTypeBadgeClass(
                      attribute.attributeDisplayType
                    )}"
                  >
                    {attribute.attributeDisplayType}
                  </span>
                  <span
                    class="px-2 py-1 rounded text-xs font-medium {getAppliesToBadgeClass(
                      attribute.attributeModel
                    )}"
                  >
                    {getAppliesToLabel(attribute.attributeModel)}
                  </span>
                </div>
                <p class="text-sm text-gray-600">Key: {attribute.attributeKey}</p>
                {#if attribute.attributeValues && attribute.attributeValues.length > 0}
                  <div class="mt-2 flex flex-wrap gap-1">
                    {#each attribute.attributeValues as value}
                      <Badge variant="outline" class="text-xs">{value}</Badge>
                    {/each}
                  </div>
                {/if}
              </div>
              <div class="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  onclick={(e) => handleEditAttribute(e, attribute)}
                >
                  Edit
                </Button>
                <Button
                  variant="destructive"
                  size="sm"
                  onclick={(e) =>
                    handleDeleteAttribute(e, attribute.id, attribute.attributeDisplayName)}
                >
                  Delete
                </Button>
              </div>
            </div>
          </Card.Content>
        </Card.Root>
      {/each}
    </div>
  {/if}
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
