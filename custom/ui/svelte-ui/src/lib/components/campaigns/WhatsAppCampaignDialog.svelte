<script lang="ts">
  import { Dialog } from '$lib/components/ui/dialog';
  import WhatsAppCampaignForm from './WhatsAppCampaignForm.svelte';
  
  interface Props {
    open?: boolean;
    mode?: 'create' | 'edit';
    campaign?: any;
  }
  
  let {
    open = $bindable(false),
    mode = 'create',
    campaign = null
  }: Props = $props();
  
  let onsubmit: ((data: any) => void) | undefined = $state();
  
  function handleSubmit(data: any) {
    if (onsubmit) onsubmit(data);
    open = false;
  }
  
  function handleCancel() {
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
      onsubmit={handleSubmit}
      oncancel={handleCancel}
    />
  </Dialog.Content>
</Dialog.Root>
