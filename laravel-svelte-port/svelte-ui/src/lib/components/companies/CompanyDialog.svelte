<script lang="ts">
  /**
   * CompanyDialog
   * Dialog for creating/editing companies
   */
  import { createEventDispatcher } from 'svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import CompanyForm from './CompanyForm.svelte';
  import type { Company, CreateCompanyParams } from '$lib/api/companies';

  interface Props {
    open: boolean;
    mode?: 'create' | 'edit';
    company?: Company | null;
  }

  let { open = $bindable(false), mode = 'create', company = null }: Props = $props();

  const dispatch = createEventDispatcher<{
    submit: CreateCompanyParams;
    close: void;
  }>();

  function handleSubmit(data: CreateCompanyParams) {
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
        {mode === 'create' ? 'Create Company' : 'Edit Company'}
      </Dialog.Title>
      <Dialog.Description>
        {mode === 'create' 
          ? 'Add a new company to your account.'
          : 'Update company information.'}
      </Dialog.Description>
    </Dialog.Header>

    <CompanyForm
      {mode}
      {company}
      on:submit={(e) => handleSubmit(e.detail)}
      on:cancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
