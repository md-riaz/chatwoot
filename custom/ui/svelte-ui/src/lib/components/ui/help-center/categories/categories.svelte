<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import { Button } from '../../../button/index.js';

  export let categories: Category[] = [];

  interface Category {
    id: string;
    title: string;
    children?: Category[];
  }

  const dispatch = createEventDispatcher();

  function addCategory() {
    dispatch('add');
  }

  function editCategory(cat: Category) {
    dispatch('edit', cat);
  }

  function removeCategory(cat: Category) {
    dispatch('remove', cat);
  }
</script>

<div class="space-y-4 p-4">
  <div class="flex items-center justify-between">
    <h3 class="text-lg font-medium">Categories</h3>
    <Button on:click={addCategory}>New Category</Button>
  </div>

  {#if categories.length === 0}
    <div class="text-sm text-muted-foreground">No categories yet.</div>
  {:else}
    <ul class="space-y-2">
      {#each categories as cat}
        <li class="border rounded p-2">
          <div class="flex items-center justify-between">
            <div>{cat.title}</div>
            <div class="flex gap-2">
              <Button variant="outline" size="sm" on:click={() => editCategory(cat)}>Edit</Button>
              <Button variant="ghost" size="sm" on:click={() => removeCategory(cat)}>Delete</Button>
            </div>
          </div>
          {#if cat.children && cat.children.length}
            <ul class="pl-4 mt-2 space-y-1">
              {#each cat.children as child}
                <li class="flex items-center justify-between text-sm">
                  <span>{child.title}</span>
                  <div class="flex gap-2">
                    <Button variant="outline" size="xs" on:click={() => editCategory(child)}>Edit</Button>
                    <Button variant="ghost" size="xs" on:click={() => removeCategory(child)}>Delete</Button>
                  </div>
                </li>
              {/each}
            </ul>
          {/if}
        </li>
      {/each}
    </ul>
  {/if}
</div>
<script lang="ts">
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import { Textarea } from '$lib/components/ui/textarea';
  import { Badge } from '$lib/components/ui/badge';
  import { Card } from '$lib/components/ui/card';

  export let categories: {
    id: string;
    name: string;
    description: string;
    color: string;
    articleCount: number;
    order: number;
  }[] = [];

  export let onCreate: (category: { name: string; description: string; color: string }) => void = () => {};
  export let onUpdate: (id: string, category: { name: string; description: string; color: string }) => void = () => {};
  export let onDelete: (id: string) => void = () => {};
  export let onReorder: (categories: typeof categories) => void = () => {};

  let isCreating = false;
  let editingId: string | null = null;
  let formData = {
    name: '',
    description: '',
    color: '#3B82F6'
  };

  const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16'];

  function startCreate() {
    isCreating = true;
    formData = { name: '', description: '', color: '#3B82F6' };
  }

  function startEdit(category: typeof categories[0]) {
    editingId = category.id;
    formData = {
      name: category.name,
      description: category.description,
      color: category.color
    };
  }

  function cancelEdit() {
    isCreating = false;
    editingId = null;
    formData = { name: '', description: '', color: '#3B82F6' };
  }

  function handleSubmit() {
    if (isCreating) {
      onCreate(formData);
    } else if (editingId) {
      onUpdate(editingId, formData);
    }
    cancelEdit();
  }

  function handleDelete(id: string) {
    if (confirm('Are you sure you want to delete this category?')) {
      onDelete(id);
    }
  }

  // Drag-and-drop (HTML5) for top-level reordering
  import { moveItem } from './dnd-utils';

  let dragId: string | null = null;
  let localCategories = categories ? [...categories] : [];

  $: if (categories) localCategories = [...categories];

  function onDragStart(e: DragEvent, id: string) {
    dragId = id;
    if (e.dataTransfer) {
      e.dataTransfer.setData('text/plain', id);
      e.dataTransfer.effectAllowed = 'move';
    }
  }

  function onDragOver(e: DragEvent) {
    e.preventDefault();
    e.dataTransfer!.dropEffect = 'move';
  }

  function onDrop(e: DragEvent, targetId: string) {
    e.preventDefault();
    const fromId = dragId || e.dataTransfer?.getData('text/plain');
    if (!fromId) return;
    const fromIndex = localCategories.findIndex(c => c.id === fromId);
    const toIndex = localCategories.findIndex(c => c.id === targetId);
    if (fromIndex === -1 || toIndex === -1) return;
    if (fromIndex === toIndex) return;
    localCategories = moveItem(localCategories, fromIndex, toIndex);
    onReorder(localCategories);
  }
</script>

<div class="w-full max-w-4xl mx-auto space-y-6 p-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-bold">Categories</h2>
    <Button on:click={startCreate} disabled={isCreating || editingId !== null}>
      New Category
    </Button>
  </div>

  {#if isCreating || editingId}
    <Card class="p-6">
      <form on:submit|preventDefault={handleSubmit} class="space-y-4">
        <div class="space-y-2">
          <Label for="name">Name*</Label>
          <Input
            id="name"
            bind:value={formData.name}
            placeholder="Category name"
            required
          />
        </div>

        <div class="space-y-2">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            bind:value={formData.description}
            placeholder="Category description"
            rows={3}
          />
        </div>

        <div class="space-y-2">
          <Label>Color</Label>
          <div class="flex gap-2">
            {#each colors as color}
              <button
                type="button"
                class="w-8 h-8 rounded-full border-2 transition-all {formData.color === color ? 'scale-110 border-primary' : 'border-transparent'}"
                style="background-color: {color}"
                on:click={() => formData.color = color}
              />
            {/each}
          </div>
        </div>

        <div class="flex gap-2">
          <Button type="submit">
            {isCreating ? 'Create' : 'Save'}
          </Button>
          <Button type="button" variant="outline" on:click={cancelEdit}>
            Cancel
          </Button>
        </div>
      </form>
    </Card>
  {/if}

  <div class="space-y-3">
    {#each localCategories as category (category.id)}
      <Card class="p-4" draggable="true" on:dragstart={(e) => onDragStart(e, category.id)} on:dragover={onDragOver} on:drop={(e) => onDrop(e, category.id)}>
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4 flex-1">
            <div
              class="w-4 h-4 rounded-full"
              style="background-color: {category.color}"
            />
            <div class="flex-1">
              <h3 class="font-semibold">{category.name}</h3>
              {#if category.description}
                <p class="text-sm text-muted-foreground">{category.description}</p>
              {/if}
            </div>
            <Badge variant="secondary">
              {category.articleCount} article{category.articleCount !== 1 ? 's' : ''}
            </Badge>
          </div>
          <div class="flex gap-2">
            <Button variant="ghost" size="sm" on:click={() => startEdit(category)}>
              Edit
            </Button>
            <Button variant="ghost" size="sm" on:click={() => handleDelete(category.id)}>
              Delete
            </Button>
          </div>
        </div>
      </Card>
    {/each}

    {#if categories.length === 0 && !isCreating}
      <Card class="p-8 text-center">
        <p class="text-muted-foreground">No categories yet. Create your first category to get started.</p>
      </Card>
    {/if}
  </div>
</div>
