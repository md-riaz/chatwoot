<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import SMSCampaignForm from './SMSCampaignForm.svelte';
  import type { Campaign, CreateCampaignParams } from '$lib/api/campaigns';
  
  interface Props {
    open?: boolean;
    mode?: 'create' | 'edit';
    campaign?: Campaign | null;
  }
  
  let {
    open = $bindable(false),
    mode = 'create',
    campaign = null
  }: Props = $props();
  
  const dispatch = createEventDispatcher<{
    submit: CreateCampaignParams;
    close: void;
  }>();
  
  function handleSubmit(data: CreateCampaignParams) {
    dispatch('submit', data);
    open = false;
  }
  
  function handleCancel() {
    dispatch('close');
    open = false;
  }
</script>

<Dialog.Root {open} onOpenChange={(value) => (open = value)}>
  <Dialog.Content class="max-w-2xl">
    <Dialog.Header>
      <Dialog.Title>
        {mode === 'create' ? 'Create SMS Campaign' : 'Edit SMS Campaign'}
      </Dialog.Title>
      <Dialog.Description>
        {mode === 'create' 
          ? 'Create a new SMS campaign to send messages to your contacts.' 
          : 'Update your SMS campaign details.'}
      </Dialog.Description>
    </Dialog.Header>
    
    <SMSCampaignForm 
      {mode}
      {campaign}
      on:submit={(e) => handleSubmit(e.detail)}
      on:cancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
