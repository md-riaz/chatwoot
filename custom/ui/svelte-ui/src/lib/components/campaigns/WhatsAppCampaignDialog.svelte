<script lang="ts">
  import { createEventDispatcher } from 'svelte';
  import * as Dialog from '$lib/components/ui/dialog';
  import WhatsAppCampaignForm from './WhatsAppCampaignForm.svelte';
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

<Dialog.Root bind:open>
  <Dialog.Content class="max-w-2xl max-h-[90vh] overflow-y-auto">
    <Dialog.Header>
      <Dialog.Title>
        {mode === 'create' ? 'Create WhatsApp Campaign' : 'Edit WhatsApp Campaign'}
      </Dialog.Title>
      <Dialog.Description>
        {mode === 'create' 
          ? 'Create a new WhatsApp campaign using approved templates.' 
          : 'Update your WhatsApp campaign details.'}
      </Dialog.Description>
    </Dialog.Header>
    
    <WhatsAppCampaignForm 
      {mode}
      {campaign}
      on:submit={(e) => handleSubmit(e.detail)}
      on:cancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
