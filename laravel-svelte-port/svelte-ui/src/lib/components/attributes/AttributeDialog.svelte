<script lang="ts">
  /**
   * AttributeDialog
   * Dialog for creating/editing custom attributes
   */
  import { createEventDispatcher } from 'svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import AttributeForm from './AttributeForm.svelte';
  import type { CustomAttribute, CreateAttributeParams } from '$lib/api/attributes';

  interface Props {
    open: boolean;
    mode?: 'create' | 'edit';
    attribute?: CustomAttribute | null;
  }

  let { open = $bindable(false), mode = 'create', attribute = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateAttributeParams;
    close: void;
  }>();

  function handleSubmit(data: CreateAttributeParams) {
    dispatch('submit', data);
    open = false;
  }

  function handleCancel() {
    dispatch('close');
    open = false;
  }
</script>

<Dialog.Root {open} onOpenChange={(value) => (open = value)}>
  <Dialog.Content class="max-w-2xl max-h-[85vh] overflow-y-auto">
    <Dialog.Header>
      <Dialog.Title>
        {mode === 'create' ? 'Create Custom Attribute' : 'Edit Custom Attribute'}
      </Dialog.Title>
      <Dialog.Description>
        {mode === 'create' 
          ? 'Add a new custom attribute for contacts or conversations.'
          : 'Update custom attribute configuration.'}
      </Dialog.Description>
    </Dialog.Header>

    <AttributeForm
      {mode}
      {attribute}
      on:submit={(e) => handleSubmit(e.detail)}
      on:cancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
