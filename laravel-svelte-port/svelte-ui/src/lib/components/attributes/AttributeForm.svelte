<script lang="ts">
  /**
   * AttributeForm
   * Form for creating/editing custom attributes
   */
  import { onMount, createEventDispatcher } from 'svelte';
  import { Button } from '$lib/components/ui/button';
  import { Input } from '$lib/components/ui/input';
  import { Label } from '$lib/components/ui/label';
  import * as Select from '$lib/components/ui/select';
  import { Badge } from '$lib/components/ui/badge';
  import type { CustomAttribute, CreateAttributeParams } from '$lib/api/attributes';

  interface Props {
    mode: 'create' | 'edit';
    attribute?: CustomAttribute | null;
  }

  let { mode, attribute = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateAttributeParams;
    cancel: void;
  }>();

  let displayName = $state('');
  let key = $state('');
  let displayType = $state<'text' | 'number' | 'date' | 'list' | 'checkbox'>('text');
  let model = $state<'contact_attribute' | 'conversation_attribute'>('contact_attribute');
  let listValues = $state<string[]>([]);
  let newListValue = $state('');

  let errors = $state<Record<string, string>>({});
  let isSubmitting = $state(false);

  const typeOptions = [
    { value: 'text', label: 'Text' },
    { value: 'number', label: 'Number' },
    { value: 'date', label: 'Date' },
    { value: 'list', label: 'List' },
    { value: 'checkbox', label: 'Checkbox' },
  ];

  const modelOptions = [
    { value: 'contact_attribute', label: 'Contact Attribute' },
    { value: 'conversation_attribute', label: 'Conversation Attribute' },
  ];

  onMount(() => {
    // If editing, populate form with attribute data
    if (mode === 'edit' && attribute) {
      displayName = attribute.attributeDisplayName;
      key = attribute.attributeKey;
      displayType = attribute.attributeDisplayType;
      model = attribute.attributeModel;
      listValues = attribute.attributeValues || [];
    }
  });

  function generateKey(name: string): string {
    return name
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '_')
      .replace(/^_+|_+$/g, '');
  }

  function handleDisplayNameChange() {
    if (mode === 'create' && !key) {
      key = generateKey(displayName);
    }
  }

  function addListValue() {
    if (newListValue.trim()) {
      listValues = [...listValues, newListValue.trim()];
      newListValue = '';
    }
  }

  function removeListValue(index: number) {
    listValues = listValues.filter((_, i) => i !== index);
  }

  function validateForm(): boolean {
    errors = {};
    let isValid = true;

    if (!displayName || displayName.trim().length < 1) {
      errors.displayName = 'Display name is required';
      isValid = false;
    }

    if (!key || key.trim().length < 1) {
      errors.key = 'Attribute key is required';
      isValid = false;
    } else if (!/^[a-z0-9_]+$/.test(key)) {
      errors.key = 'Key must contain only lowercase letters, numbers, and underscores';
      isValid = false;
    }

    if (displayType === 'list' && listValues.length === 0) {
      errors.listValues = 'List attributes must have at least one value';
      isValid = false;
    }

    return isValid;
  }

  function handleSubmit() {
    if (!validateForm()) {
      return;
    }

    isSubmitting = true;

    const attributeData: CreateAttributeParams = {
      attributeDisplayName: displayName.trim(),
      attributeKey: key.trim(),
      attributeDisplayType: displayType,
      attributeModel: model,
    };

    if (displayType === 'list') {
      attributeData.attributeValues = listValues;
    }

    dispatch('submit', attributeData);
    isSubmitting = false;
  }

  function handleCancel() {
    dispatch('cancel');
  }
</script>

<div class="space-y-6">
  <div class="space-y-4">
    <div class="space-y-2">
      <Label for="displayName">Display Name *</Label>
      <Input
        id="displayName"
        bind:value={displayName}
        oninput={handleDisplayNameChange}
        placeholder="e.g., Customer Type"
        class={errors.displayName ? 'border-red-500' : ''}
      />
      <p class="text-sm text-muted-foreground">
        The label shown in the UI
      </p>
      {#if errors.displayName}
        <p class="text-sm text-red-500">{errors.displayName}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="key">Attribute Key *</Label>
      <Input
        id="key"
        bind:value={key}
        placeholder="e.g., customer_type"
        disabled={mode === 'edit'}
        class={errors.key ? 'border-red-500' : ''}
      />
      <p class="text-sm text-muted-foreground">
        {mode === 'edit' 
          ? 'Key cannot be changed after creation'
          : 'Unique identifier (lowercase, numbers, underscores only)'}
      </p>
      {#if errors.key}
        <p class="text-sm text-red-500">{errors.key}</p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="displayType">Attribute Type *</Label>
      <Select.Root
        value={displayType}
        onValueChange={(value: any) => {
          if (value) displayType = value as typeof displayType;
        }}
        disabled={mode === 'edit'}
        type="single"
      >
        <Select.Trigger class={errors.displayType ? 'border-red-500' : ''}>
          <Select.Value placeholder="Select a type" />
        </Select.Trigger>
        <Select.Content>
          {#each typeOptions as option}
            <Select.Item value={option.value} label={option.label}>
              {option.label}
            </Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
      {#if mode === 'edit'}
        <p class="text-sm text-muted-foreground">
          Type cannot be changed after creation
        </p>
      {/if}
    </div>

    <div class="space-y-2">
      <Label for="model">Applies To *</Label>
      <Select.Root
        value={model}
        onValueChange={(value: any) => {
          if (value) model = value as typeof model;
        }}
        disabled={mode === 'edit'}
        type="single"
      >
        <Select.Trigger class={errors.model ? 'border-red-500' : ''}>
          <Select.Value placeholder="Select where to apply" />
        </Select.Trigger>
        <Select.Content>
          {#each modelOptions as option}
            <Select.Item value={option.value} label={option.label}>
              {option.label}
            </Select.Item>
          {/each}
        </Select.Content>
      </Select.Root>
      <p class="text-sm text-muted-foreground">
        {model === 'contact_attribute'
          ? 'This attribute will be available on contact records'
          : 'This attribute will be available on conversation records'}
      </p>
      {#if mode === 'edit'}
        <p class="text-sm text-muted-foreground">
          Applies to cannot be changed after creation
        </p>
      {/if}
    </div>

    {#if displayType === 'list'}
      <div class="space-y-2">
        <Label>List Values *</Label>
        <div class="flex gap-2">
          <Input
            bind:value={newListValue}
            placeholder="Enter a value"
            onkeypress={(e) => {
              if (e.key === 'Enter') {
                e.preventDefault();
                addListValue();
              }
            }}
          />
          <Button type="button" variant="outline" onclick={addListValue}>
            Add
          </Button>
        </div>
        {#if listValues.length > 0}
          <div class="flex flex-wrap gap-2 mt-2">
            {#each listValues as value, index}
              <Badge variant="secondary" class="flex items-center gap-1">
                {value}
                <button
                  type="button"
                  onclick={() => removeListValue(index)}
                  class="ml-1 hover:text-red-600"
                >
                  ×
                </button>
              </Badge>
            {/each}
          </div>
        {/if}
        {#if errors.listValues}
          <p class="text-sm text-red-500">{errors.listValues}</p>
        {/if}
      </div>
    {/if}
  </div>

  <div class="flex justify-end gap-2">
    <Button variant="outline" onclick={handleCancel} disabled={isSubmitting}>
      Cancel
    </Button>
    <Button onclick={handleSubmit} disabled={isSubmitting}>
      {isSubmitting ? 'Saving...' : mode === 'create' ? 'Create Attribute' : 'Update Attribute'}
    </Button>
  </div>
</div>
